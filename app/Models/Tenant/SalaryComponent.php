<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

final class SalaryComponent extends Model
{
    protected $fillable = ['type', 'name', 'default_amount'];

    protected $casts = ['default_amount' => 'decimal:2'];
}
