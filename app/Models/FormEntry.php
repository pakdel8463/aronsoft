<?php

namespace App\Models;

use App\Forms\FormSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormEntry extends Model
{
    protected $guarded = ['id'];

    public const STATUSES = [
        'new' => 'جدید',
        'in_progress' => 'در حال پیگیری',
        'done' => 'انجام‌شده',
        'archived' => 'بایگانی',
        'spam' => 'هرزنامه',
    ];

    public const STATUS_COLORS = ['new' => 'brand', 'in_progress' => 'amber', 'done' => 'mint', 'archived' => 'slate', 'spam' => 'coral'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_starred' => 'boolean'];
    }

    /** فارسی خوانا در ستون JSON تا جستجوی LIKE در پنل کار کند. */
    protected function asJson($value, $flags = 0)
    {
        return json_encode($value, $flags | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function display(array $field): string
    {
        return FormSchema::display($field, $this->data[$field['key']] ?? null);
    }

    /** اولین مقدار نام/ایمیل/موبایل؛ به‌عنوان عنوان ورودی در پنل. */
    public function title(): string
    {
        foreach ($this->form?->inputFields() ?? [] as $f) {
            if (in_array($f['type'], ['text', 'email', 'mobile', 'tel'], true) && filled($this->data[$f['key']] ?? null)) {
                return (string) $this->data[$f['key']];
            }
        }

        return 'ورودی #'.$this->id;
    }
}
