<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\FeePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeePayment>
 */
final class FeePaymentFactory extends Factory
{
    protected $model = FeePayment::class;

    public function definition(): array
    {
        return [
            'student_id'       => StudentFactory::new()->create()->id,
            'fee_structure_id' => FeeStructureFactory::new()->create()->id,
            'amount'           => fake()->randomFloat(2, 10, 500),
            'payment_method'   => 'cash',
            'paystack_ref'     => null,
            'recorded_by'      => null,
            'paid_at'          => now(),
        ];
    }

    public function paystack(): static
    {
        return $this->state([
            'payment_method' => 'paystack',
            'paystack_ref'   => 'PSK_' . fake()->unique()->uuid(),
        ]);
    }
}
