<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $table = 'email_logs';

    protected $fillable = [
        'user_id',
        'recipient_email',
        'subject',
        'type',
        'status',
        'body',
        'reference_type',
        'reference_id',
        'retry_count',
        'sent_at',
        'failed_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent(): bool
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }
}

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'key',
        'name',
        'description',
        'subject',
        'html_body',
        'text_body',
        'placeholders',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getActive($key)
    {
        return static::where('key', $key)->where('is_active', true)->first();
    }

    public function renderSubject($data = []): string
    {
        return $this->replacePlaceholders($this->subject, $data);
    }

    public function renderHtmlBody($data = []): string
    {
        return $this->replacePlaceholders($this->html_body, $data);
    }

    public function renderTextBody($data = []): string
    {
        return $this->replacePlaceholders($this->text_body, $data);
    }

    private function replacePlaceholders($text, $data = []): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
}
