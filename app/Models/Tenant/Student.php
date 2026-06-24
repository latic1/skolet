<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Student extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'admission_no',
        'user_id',
        'class_id',
        'section_id',
        'full_name',
        'date_of_birth',
        'gender',
        'photo_path',
        'guardian_name',
        'guardian_contact',
        'guardian_email',
        'address',
        'medical_notes',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->useLogName('student');
    }
}
