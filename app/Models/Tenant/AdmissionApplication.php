<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdmissionApplication extends Model
{
    use HasUuids;

    protected $fillable = [
        'applicant_name',
        'date_of_birth',
        'gender',
        'class_applying_for',
        'guardian_name',
        'guardian_contact',
        'guardian_email',
        'previous_school',
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at'   => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
