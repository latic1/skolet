<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Expense;
use App\Models\Tenant\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
final class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $category = ExpenseCategory::firstOrCreate(
            ['name' => fake()->randomElement(['Utilities', 'Supplies', 'Maintenance', 'Events', 'Other'])],
        );

        return [
            'category_id' => $category->id,
            'amount'      => fake()->randomFloat(2, 50, 5000),
            'date'        => fake()->dateThisYear()->format('Y-m-d'),
            'description' => fake()->sentence(),
            'recorded_by' => UserFactory::new()->create()->id,
        ];
    }
}
