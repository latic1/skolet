<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ExpenseCategory extends Model
{
    use HasUuids;

    protected $fillable = ['name'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
