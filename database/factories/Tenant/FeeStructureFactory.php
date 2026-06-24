<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeeStructure>
 */
final class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        return [
            'fee_item'     => fake()->randomElement(['Tuition Fee', 'Sports Fee', 'Library Fee', 'Lab Fee']),
            'amount'       => fake()->randomFloat(2, 50, 1000),
            'billing_cycle'=> 'term',
            'target_class' => 'all',
            'is_mandatory' => true,
            'due_date'     => now()->addDays(30)->toDateString(),
            'term_id'      => null,
        ];
    }

    public function overdue(): static
    {
        return $this->state(['due_date' => now()->subDay()->toDateString()]);
    }

    public function annual(): static
    {
        return $this->state(['billing_cycle' => 'annual', 'term_id' => null]);
    }
}
