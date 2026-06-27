<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\SalaryStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryStructure>
 */
final class SalaryStructureFactory extends Factory
{
    protected $model = SalaryStructure::class;

    public function definition(): array
    {
        return [
            'staff_id'       => StaffFactory::new()->create()->id,
            'gross'          => fake()->randomFloat(2, 800, 5000),
            'allowances'     => [],
            'deductions'     => [],
            'effective_from' => now()->startOfMonth()->toDateString(),
        ];
    }
}
