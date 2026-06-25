<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class DisciplinaryRecord extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'student_id',
        'reported_by',
        'incident_type',
        'description',
        'action_taken',
        'date',
        'parent_notified',
    ];

    protected $casts = [
        'date'             => 'date',
        'parent_notified'  => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->useLogName('disciplinary_record');
    }
}
