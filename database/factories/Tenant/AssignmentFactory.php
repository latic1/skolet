<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Assignment;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
final class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'teacher_id'  => StaffFactory::new()->create()->id,
            'subject_id'  => Subject::factory()->create()->id,
            'class_id'    => SchoolClass::factory()->create()->id,
            'section_id'  => null,
            'title'       => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'due_date'    => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d H:i:s'),
            'total_marks' => fake()->randomElement([10, 20, 50, 100]),
        ];
    }

    public function overdue(): static
    {
        return $this->state(['due_date' => now()->subDay()->format('Y-m-d H:i:s')]);
    }
}
