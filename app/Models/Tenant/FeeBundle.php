<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FeeBundle extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'target_class',
        'billing_cycle',
        'term_id',
        'academic_year_id',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'fee_bundle_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** Bundle total is always computed, never stored. */
    public function getComputedTotalAttribute(): float
    {
        return (float) $this->items->sum('amount');
    }
}
