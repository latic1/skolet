<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class Subject extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'code'];
}
