<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class PlatformAnalytics extends Model
{
    protected $connection = 'central';
    protected $table = 'platform_analytics';
    public $timestamps = false;

    protected $fillable = ['metric', 'value', 'computed_at'];

    protected $casts = [
        'value'       => 'array',
        'computed_at' => 'datetime',
    ];
}
