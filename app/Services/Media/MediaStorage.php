<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * انتخاب دیسک رسانه + ساخت آدرس عمومی. دیسک «public_uploads» در زمان اجرا ثبت می‌شود
 * (config/filesystems.php دست نخورده می‌ماند).
 */
class MediaStorage
{
    public const DISK_UPLOADS = 'public_uploads';

    public static function publicRoot(): string
    {
        $root = config('media.public_root');

        return rtrim($root ? (string) $root : public_path(config('media.public_url_prefix', 'uploads')), '/\\');
    }

    public static function ensureDisks(): void
    {
        if (! config('filesystems.disks.'.self::DISK_UPLOADS)) {
            config(['filesystems.disks.'.self::DISK_UPLOADS => [
                'driver' => 'local',
                'root' => self::publicRoot(),
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ]]);
        }
    }

    /** نام دیسک برای آپلودهای جدید */
    public static function diskName(): string
    {
        $d = (string) config('media.disk', 'auto');
        if ($d === 'auto' || $d === '') {
            return (is_link(public_path('storage')) || is_dir(public_path('storage'))) ? 'public' : self::DISK_UPLOADS;
        }

        return $d;
    }

    public static function disk(?string $name = null): Filesystem
    {
        self::ensureDisks();

        return Storage::disk($name ?: self::diskName());
    }

    /** آدرس نسبیِ از ریشه‌ی سایت، مثل /storage/uploads/2026/10/a.jpg */
    public static function rel(string $disk, string $path): string
    {
        $path = ltrim($path, '/');

        return match ($disk) {
            'public' => '/storage/'.$path,
            self::DISK_UPLOADS => '/'.trim((string) config('media.public_url_prefix', 'uploads'), '/').'/'.$path,
            default => (string) Storage::disk($disk)->url($path),
        };
    }

    public static function url(string $disk, string $path): string
    {
        $rel = self::rel($disk, $path);

        return preg_match('#^https?://#', $rel) ? $rel : url($rel);
    }

    /** حذف فایل اصلی و تمام نسخه‌های یک رسانه */
    public static function deleteFiles(Media $media): void
    {
        $disk = self::disk($media->disk ?: 'public');
        $paths = array_filter(array_merge([$media->path], array_values((array) $media->variants)));
        foreach ($paths as $p) {
            try {
                $disk->delete($p);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
