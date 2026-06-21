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
    ];
}
