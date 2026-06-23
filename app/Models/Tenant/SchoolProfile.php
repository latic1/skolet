<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

final class SchoolProfile extends Model
{
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
    ];

    protected $casts = [
        'grading_scale'         => 'array',
        'onboarding_completed'  => 'boolean',
        'onboarding_step'       => 'integer',
        'notification_settings' => 'array',
    ];

    public function isNotificationEnabled(string $key): bool
    {
        $settings = $this->notification_settings;

        if (! $settings) {
            return true;
        }

        return (bool) ($settings[$key]['email'] ?? true);
    }
}
