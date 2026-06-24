<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
final class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'admission_no'    => date('Y') . '/' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'full_name'       => fake()->name(),
            'gender'          => fake()->randomElement(['male', 'female']),
            'date_of_birth'   => fake()->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'guardian_name'   => fake()->name(),
            'guardian_contact'=> fake()->phoneNumber(),
            'guardian_email'  => fake()->safeEmail(),
            'status'          => 'active',
            'class_id'        => null,
            'section_id'      => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }
}
