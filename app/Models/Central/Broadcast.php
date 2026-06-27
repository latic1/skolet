<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broadcast extends Model
{
    protected $connection = 'central';

    protected $table = 'broadcasts';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'subject',
        'message',
        'severity',
        'send_at',
        'sent_at',
        'sent_by',
    ];

    protected $casts = [
        'send_at'    => 'datetime',
        'sent_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'sent_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(BroadcastNotification::class, 'broadcast_id');
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function isScheduled(): bool
    {
        return $this->send_at !== null && $this->sent_at === null;
    }
}
