<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class SchoolProfile extends Model
{
    use LogsActivity;
    protected $table = 'school_profile';

    protected $fillable = [
        'school_name',
        'logo_path',
        'short_description',
        'address',
        'phone',
        'email',
        'website',
        'period_system',
        'admission_pattern',
        'admission_counter',
        'grading_scale',
        'onboarding_completed',
        'onboarding_step',
        'notification_settings',
        'admissions_open',
        'currency_code',
        'currency_symbol',
    ];

    protected $casts = [
        'grading_scale'         => 'array',
        'onboarding_completed'  => 'boolean',
        'onboarding_step'       => 'integer',
        'notification_settings' => 'array',
        'admissions_open'       => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->useLogName('school_profile');
    }

    public function isNotificationEnabled(string $key): bool
    {
        $settings = $this->notification_settings;

        if (! $settings) {
            return true;
        }

        return (bool) ($settings[$key]['email'] ?? true);
    }
}
