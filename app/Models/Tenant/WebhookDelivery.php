<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasUuids;

    protected $fillable = [
        'webhook_id', 'event', 'payload',
        'response_status', 'response_body',
        'attempt_count', 'attempted_at', 'next_retry_at',
    ];

    protected $casts = [
        'payload'       => 'array',
        'attempt_count' => 'integer',
        'attempted_at'  => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function isSuccess(): bool
    {
        return $this->response_status !== null
            && $this->response_status >= 200
            && $this->response_status < 300;
    }

    public function canRetry(): bool
    {
        return ! $this->isSuccess() && $this->attempt_count < 3;
    }
}
