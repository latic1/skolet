<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
final class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-30 days', '+30 days');
        $end   = fake()->dateTimeBetween($start, (clone $start)->modify('+7 days'));

        return [
            'leave_type' => fake()->randomElement(['sick', 'annual', 'maternity', 'paternity', 'personal', 'other']),
            'start_date' => $start->format('Y-m-d'),
            'end_date'   => $end->format('Y-m-d'),
            'reason'     => fake()->sentence(),
            'status'     => 'pending',
            'staff_id'   => StaffFactory::new()->create()->id,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected', 'rejection_reason' => fake()->sentence()]);
    }
}
