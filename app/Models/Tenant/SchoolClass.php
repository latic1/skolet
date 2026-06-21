<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SchoolClass extends Model
{
    use HasUuids;

    protected $table = 'school_classes';

    protected $fillable = ['name', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id')->orderBy('name');
    }
}
