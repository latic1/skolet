<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\PayrollRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRun>
 */
final class PayrollRunFactory extends Factory
{
    protected $model = PayrollRun::class;

    public function definition(): array
    {
        return [
            'month'        => fake()->numberBetween(1, 12),
            'year'         => now()->year,
            'status'       => 'processed',
            'processed_by' => UserFactory::new()->create()->id,
            'processed_at' => now(),
        ];
    }
}
