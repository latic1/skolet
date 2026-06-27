<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PayrollRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'month',
        'year',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'month'        => 'integer',
        'year'         => 'integer',
        'processed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F');
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getTotalNetAttribute(): float
    {
        return (float) $this->items->sum('net');
    }

    public function getTotalSsnitEmployeeAttribute(): float
    {
        return (float) $this->items->sum('ssnit_employee');
    }

    public function getTotalTier2EmployeeAttribute(): float
    {
        return (float) $this->items->sum('tier2_employee');
    }

    public function getTotalPayeAttribute(): float
    {
        return (float) $this->items->sum('paye');
    }

    public function getTotalSsnitEmployerAttribute(): float
    {
        return (float) $this->items->sum('ssnit_employer');
    }

    public function getTotalTier2EmployerAttribute(): float
    {
        return (float) $this->items->sum('tier2_employer');
    }
}
