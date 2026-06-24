<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Announcement extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'title',
        'body',
        'posted_by',
        'is_public',
        'audience_type',
        'audience_ids',
    ];

    protected $casts = [
        'is_public'    => 'boolean',
        'audience_ids' => 'array',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->useLogName('announcement');
    }
}
