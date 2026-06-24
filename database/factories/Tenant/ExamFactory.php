<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
final class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end   = fake()->dateTimeBetween($start, '+2 months');

        return [
            'name'         => fake()->randomElement(['Mid-Term', 'End of Term', 'Mock', 'Yearly']) . ' Exam',
            'term_id'      => null,
            'start_date'   => $start->format('Y-m-d'),
            'end_date'     => $end->format('Y-m-d'),
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }
}
