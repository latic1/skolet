<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    use HasUuids;

    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'rate_per_student',
        'student_count',
        'student_count_synced_at',
        'amount_due',
        'payment_status',
        'cycle_start',
        'cycle_end',
        'status',
    ];

    protected $casts = [
        'rate_per_student'       => 'decimal:2',
        'amount_due'             => 'decimal:2',
        'student_count'          => 'integer',
        'student_count_synced_at' => 'datetime',
        'cycle_start'            => 'date',
        'cycle_end'              => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
