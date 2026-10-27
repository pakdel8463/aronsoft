<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * اعتبارسنجی + ذخیره + پردازش تصویر.
 *  - نوع فایل از روی «محتوا» (finfo) تشخیص داده می‌شود، نه هدر کلاینت؛ پسوند باید با mime واقعی بخواند
 *  - هر بخش از نام فایل که پسوند خطرناک باشد (shell.php.jpg) رد می‌شود
 *  - پسوند فایل ذخیره‌شده را خودِ سرور از روی mime مشخص می‌کند و نام تصادفی/امن دارد
 *  - تصاویر دوباره encode می‌شوند (متادیتا/payload تعبیه‌شده پاک می‌شود) و webp + thumb/medium/large ساخته می‌شود
 */
class MediaUploader
{
    public function store(UploadedFile $file, ?User $user = null, array $meta = []): Media
    {
        $info = $this->inspect($file);
        $disk = MediaStorage::diskName();
        $fs = MediaStorage::disk($disk);

        $dir = trim((string) config('media.directory', 'uploads'), '/').'/'.now()->format('Y/m');
        $stem = $this->stem($file->getClientOriginalName());
        $written = [];

        try {
            $res = $this->write($fs, $file, $info, $dir, $stem, $written);
        } catch (MediaException $e) {
            $this->cleanup($fs, $written);
            throw $e;
        } catch (\Throwable $e) {
            $this->cleanup($fs, $written);
            report($e);
            throw new MediaException('پردازش فایل با خطا مواجه شد. فایل را بررسی کنید و دوباره تلاش کنید.');
        }

        return Media::create([
            'file_name' => basename($res['path']),
            'original_name' => mb_substr($this->cleanName($file->getClientOriginalName()), 0, 250),
            'mime_type' => $res['mime'],
            'size' => $res['size'],
            'type' => $info['type'],
            'width' => $res['width'],
            'height' => $res['height'],
            'alt' => $meta['alt'] ?? null,
            'title' => $meta['title'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'disk' => $disk,
            'path' => $res['path'],
            'variants' => $res['variants'] ?: null,
            'extension' => $info['ext_out'],
            'uploaded_by' => $user?->id,
            'hash' => $res['hash'],
        ]);
    }

    /** جایگزینی فایل یک رسانه‌ی موجود (id و استفاده‌ها ثابت می‌مانند) */
    public function replace(Media $media, UploadedFile $file, ?User $user = null): Media
    {
        $info = $this->inspect($file);
        if ($info['type'] !== $media->type) {
            throw new MediaException('نوع فایل جدید باید با فایل قبلی یکسان باشد.');
        }

        $disk = MediaStorage::diskName();
        $fs = MediaStorage::disk($disk);
        $dir = trim((string) config('media.directory', 'uploads'), '/').'/'.now()->format('Y/m');
        $stem = $this->stem($file->getClientOriginalName());
        $written = [];

        try {
            $res = $this->write($fs, $file, $info, $dir, $stem, $written);
        } catch (MediaException $e) {
            $this->cleanup($fs, $written);
            throw $e;
        } catch (\Throwable $e) {
            $this->cleanup($fs, $written);
            report($e);
            throw new MediaException('پردازش فایل با خطا مواجه شد.');
        }

        MediaStorage::deleteFiles($media);

        $media->update([
            'file_name' => basename($res['path']),
            'original_name' => mb_substr($this->cleanName($file->getClientOriginalName()), 0, 250),
            'mime_type' => $res['mime'],
            'size' => $res['size'],
            'width' => $res['width'],
            'height' => $res['height'],
            'disk' => $disk,
            'path' => $res['path'],
            'variants' => $res['variants'] ?: null,
            'extension' => $info['ext_out'],
            'hash' => $res['hash'],
        ]);

        return $media->refresh();
    }

    /** @return array{type:string,mime:string,ext_out:string,max_kb:int} */
    public function inspect(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            $msg = match ($file->getError()) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم فایل از محدودیت سرور (upload_max_filesize) بیشتر است.',
                UPLOAD_ERR_PARTIAL => 'آپلود فایل ناقص انجام شد؛ دوباره تلاش کنید.',
                UPLOAD_ERR_NO_FILE => 'فایلی انتخاب نشده است.',
                default => 'آپلود فایل با خطا مواجه شد.',
            };
            throw new MediaException($msg);
        }

        $real = $file->getRealPath();
        if (! $real || ! is_file($real)) {
            throw new MediaException('فایل آپلودشده در دسترس نیست.');
        }

        $orig = $this->cleanName($file->getClientOriginalName());
        $segments = array_values(array_filter(explode('.', mb_strtolower($orig)), fn ($s) => $s !== ''));
        $ext = count($segments) > 1 ? end($segments) : '';
        // بخش اول نام (قبل از اولین نقطه) فقط وقتی «پسوند» حساب می‌شود که نقطه‌ای در نام باشد
        $blocked = (array) config('media.blocked_extensions', []);
        foreach (array_slice($segments, 1) as $seg) {
            if (in_array($seg, $blocked, true)) {
                throw new MediaException('این نوع فایل به دلایل امنیتی مجاز نیست.');
            }
        }
        if (count($segments) === 1 && in_array($segments[0], $blocked, true)) {
            throw new MediaException('این نوع فایل به دلایل امنیتی مجاز نیست.');
        }
        if ($ext === '') {
            throw new MediaException('فایل باید پسوند داشته باشد.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($real) ?: 'application/octet-stream';
        $allowed = (array) config('media.allowed', []);
        if (! isset($allowed[$mime])) {
            throw new MediaException('نوع فایل ('.$mime.') پشتیبانی نمی‌شود.');
        }
        if (! in_array($ext, $allowed[$mime], true)) {
            throw new MediaException('پسوند فایل با محتوای آن (' . $mime . ') همخوانی ندارد.');
        }

        $type = match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            default => 'file',
        };

        $maxKb = (int) config("media.max_kb.$type", 20480);
        if ($file->getSize() > $maxKb * 1024) {
            throw new MediaException('حجم فایل بیشتر از حد مجاز ('.number_format($maxKb / 1024, 0).' مگابایت) است.');
        }

        // فایل‌های متنی/gif نباید کد اجرایی یا اسکریپت داشته باشند
        if (in_array($mime, ['text/plain', 'text/csv', 'image/gif'], true)) {
            $head = (string) file_get_contents($real, false, null, 0, 200000);
            if (preg_match('/<\?(php|=)|<script\b|<\?xml/i', $head)) {
                throw new MediaException('محتوای فایل مشکوک به کد اجرایی است و رد شد.');
            }
        }

        return [
            'type' => $type,
            'mime' => $mime,
            'ext_in' => $ext,
            'ext_out' => $this->outExt($mime, $ext),
            'max_kb' => $maxKb,
        ];
    }

    private function outExt(string $mime, string $ext): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => $ext,
        };
    }

    /**
     * @param  array<int,string>  $written  مسیرهای نوشته‌شده (برای پاک‌سازی در صورت خطا)
     * @return array{path:string,mime:string,size:int,width:?int,height:?int,variants:array,hash:string}
     */
    private function write($fs, UploadedFile $file, array $info, string $dir, string $stem, array &$written): array
    {
        $real = $file->getRealPath();
        $ext = $info['ext_out'];
        $path = "$dir/$stem.$ext";
        $variants = [];
        $width = $height = null;

        if ($info['type'] !== 'image') {
            $stream = fopen($real, 'rb');
            $fs->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $written[] = $path;

            return ['path' => $path, 'mime' => $info['mime'], 'size' => (int) $file->getSize(), 'width' => null, 'height' => null, 'variants' => [], 'hash' => (string) sha1_file($real)];
        }

        // ---- تصویر ----
        try {
            $img = Image::read($real);
        } catch (\Throwable $e) {
            throw new MediaException('فایل تصویر معتبر نیست یا آسیب دیده است.');
        }

        $img->orient();
        $max = (int) config('media.max_dimension', 2560);
        if ($img->width() > $max || $img->height() > $max) {
            $img->scaleDown($max, $max);
        }
        $width = $img->width();
        $height = $img->height();
        $q = (int) config('media.quality', 85);

        $main = match ($info['mime']) {
            'image/png' => $img->toPng()->toString(),
            'image/webp' => $img->toWebp($q)->toString(),
            'image/gif' => (string) file_get_contents($real),
            default => $img->toJpeg($q)->toString(),
        };
        $fs->put($path, $main);
        $written[] = $path;

        $sizes = (array) config('media.sizes', []);

        // نسخه webp هم‌اندازه (برای تصاویر غیر webp)
        if (config('media.webp', true) && $info['mime'] !== 'image/webp' && $info['mime'] !== 'image/gif') {
            $webp = Image::read($main)->toWebp($q)->toString();
            if (strlen($webp) < strlen($main) * 1.15) {
                $variants['webp'] = "$dir/$stem.webp";
                $fs->put($variants['webp'], $webp);
                $written[] = $variants['webp'];
            }
        }

        foreach ($sizes as $name => $cfg) {
            $w = (int) ($cfg['width'] ?? 0);
            $h = (int) ($cfg['height'] ?? 0);
            $crop = ! empty($cfg['crop']);
            if (! $crop && $width <= $w) {
                continue; // تصویر از این اندازه کوچک‌تر است
            }
            $v = Image::read($main);
            $crop ? $v->cover($w, $h ?: $w) : $v->scaleDown($w);
            $variantPath = "$dir/$stem-$name.webp";
            $fs->put($variantPath, $v->toWebp($q)->toString());
            $variants[$name] = $variantPath;
            $written[] = $variantPath;
        }

        return ['path' => $path, 'mime' => $info['mime'], 'size' => strlen($main), 'width' => $width, 'height' => $height, 'variants' => $variants, 'hash' => sha1($main)];
    }

    private function cleanup($fs, array $paths): void
    {
        foreach ($paths as $p) {
            try {
                $fs->delete($p);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    private function cleanName(string $name): string
    {
        $name = str_replace(["\0", '/', '\\'], '', $name);

        return trim($name);
    }

    private function stem(string $original): string
    {
        $base = pathinfo($this->cleanName($original), PATHINFO_FILENAME);
        $slug = Str::slug(Str::limit($base, 50, ''));
        $slug = $slug !== '' ? $slug : 'media';

        return $slug.'-'.Str::lower(Str::random(6));
    }
}
