<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'     => fake()->name(),
            'email'    => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role'     => 'teacher',
        ];
    }

    public function schoolAdmin(): static
    {
        return $this->state(['role' => 'school_admin']);
    }

    public function teacher(): static
    {
        return $this->state(['role' => 'teacher']);
    }

    public function student(): static
    {
        return $this->state(['role' => 'student']);
    }
}
