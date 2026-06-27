<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasUuids;

    protected $connection = 'central';

    protected $table = 'subscription_payments';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'amount',
        'cycle_start',
        'cycle_end',
        'payment_reference',
        'notes',
        'recorded_by',
        'created_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'cycle_start' => 'date',
        'cycle_end'   => 'date',
        'created_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'recorded_by');
    }
}
