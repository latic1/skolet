<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
final class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => StudentFactory::new()->create()->id,
            'date'       => now()->toDateString(),
            'status'     => 'present',
            'marked_by'  => null,
            'note'       => null,
        ];
    }

    public function absent(): static
    {
        return $this->state(['status' => 'absent']);
    }

    public function late(): static
    {
        return $this->state(['status' => 'late']);
    }
}
