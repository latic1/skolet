<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PayrollItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'payroll_run_id',
        'staff_id',
        'gross',
        'allowances_total',
        'deductions_total',
        'ssnit_employee',
        'tier2_employee',
        'paye',
        'ssnit_employer',
        'tier2_employer',
        'net',
        'payment_status',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'gross'            => 'decimal:2',
        'allowances_total' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'ssnit_employee'   => 'decimal:2',
        'tier2_employee'   => 'decimal:2',
        'paye'             => 'decimal:2',
        'ssnit_employer'   => 'decimal:2',
        'tier2_employer'   => 'decimal:2',
        'net'              => 'decimal:2',
        'paid_at'          => 'datetime',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
