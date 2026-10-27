<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketAttachment extends Model
{
    protected $fillable = ['ticket_id', 'ticket_message_id', 'original_name', 'path', 'mime', 'size'];

    protected static function booted(): void
    {
        static::deleting(function (TicketAttachment $a) {
            Storage::disk(config('tickets.attachments.disk'))->delete($a->path);
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function isImage(): bool
    {
        return in_array($this->mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    public function getSizeLabelAttribute(): string
    {
        $kb = $this->size / 1024;

        return \App\Helpers\Fa::toFa($kb >= 1024 ? round($kb / 1024, 1).' مگابایت' : max(1, (int) round($kb)).' کیلوبایت');
    }

    /** نام فایل امن برای نمایش/دانلود: بدون مسیر، کاراکتر کنترلی و کاراکترهای جهت‌دهندهٔ یونیکد. */
    public static function sanitizeName(string $name, string $ext): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]+/u', '', $name) ?? '';
        $name = preg_replace('/[\/\\\\:*?"<>|]+/u', '_', $name) ?? '';
        $base = trim(pathinfo($name, PATHINFO_FILENAME), " ._\t");
        $base = Str::limit($base, 90, '');
        if ($base === '') {
            $base = 'file';
        }

        return $base.'.'.$ext;
    }

    /**
     * ذخیره روی دیسک خصوصی. نوع فایل از روی «محتوا» تشخیص داده می‌شود و پسوند ذخیره‌شده
     * از همان نوع می‌آید (نه از نام ارسالی کاربر). null = نوع غیرمجاز.
     */
    public static function storeUpload(TicketMessage $message, UploadedFile $file): ?self
    {
        $mime = (string) $file->getMimeType();
        $ext = null;
        foreach (config('tickets.attachments.types') as $e => $mimes) {
            if (in_array($mime, $mimes, true)) {
                $ext = $e === 'jpeg' ? 'jpg' : $e;
                break;
            }
        }
        if (! $ext) {
            return null;
        }

        $disk = config('tickets.attachments.disk');
        $path = Storage::disk($disk)->putFileAs(
            'tickets/'.$message->ticket_id,
            $file,
            Str::random(40).'.'.$ext
        );
        if (! $path) {
            return null;
        }

        return self::create([
            'ticket_id' => $message->ticket_id,
            'ticket_message_id' => $message->id,
            'original_name' => self::sanitizeName((string) $file->getClientOriginalName(), $ext),
            'path' => $path,
            'mime' => $mime === 'application/x-zip-compressed' ? 'application/zip' : $mime,
            'size' => (int) $file->getSize(),
        ]);
    }
}
