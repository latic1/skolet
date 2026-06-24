<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
final class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'full_name'  => fake()->name(),
            'role_title' => fake()->randomElement(['Class Teacher', 'Subject Teacher', 'HOD', 'Deputy Head']),
            'phone'      => fake()->phoneNumber(),
            'status'     => 'active',
            'user_id'    => UserFactory::new()->create()->id,
        ];
    }
}
