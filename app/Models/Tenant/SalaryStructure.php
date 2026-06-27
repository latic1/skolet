<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SalaryStructure extends Model
{
    protected $fillable = [
        'staff_id',
        'gross',
        'allowances',
        'deductions',
        'effective_from',
    ];

    protected $casts = [
        'gross'          => 'decimal:2',
        'allowances'     => 'array',
        'deductions'     => 'array',
        'effective_from' => 'date',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function getAllowancesTotalAttribute(): float
    {
        return (float) array_sum($this->allowances ?? []);
    }

    public function getDeductionsTotalAttribute(): float
    {
        return (float) array_sum($this->deductions ?? []);
    }

    public function getNetAttribute(): float
    {
        return max(0, (float) $this->gross + $this->allowances_total - $this->deductions_total);
    }
}
