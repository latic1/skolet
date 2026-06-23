<?php

declare(strict_types=1);

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    protected $fillable = [
        'domain',
        'tenant_id',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isPrimary(): bool
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com';
        $baseHost = (string) preg_replace('/^www\./i', '', $appHost);

        return str_ends_with($this->domain, '.' . $baseHost);
    }
}
