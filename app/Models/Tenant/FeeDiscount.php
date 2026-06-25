<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FeeDiscount extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'discount_type',
        'discount_value',
        'reason',
        'approved_by',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from'     => 'date',
        'valid_until'    => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
