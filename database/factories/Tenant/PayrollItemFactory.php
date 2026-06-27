<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\PayrollItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollItem>
 */
final class PayrollItemFactory extends Factory
{
    protected $model = PayrollItem::class;

    public function definition(): array
    {
        $gross          = fake()->randomFloat(2, 800, 5000);
        $ssnitEmployee  = round($gross * 0.055, 2);
        $tier2Employee  = round($gross * 0.05, 2);
        $paye           = round(max(0, $gross - $ssnitEmployee - $tier2Employee - 365) * 0.05, 2);
        $net            = max(0, $gross - $ssnitEmployee - $tier2Employee - $paye);

        return [
            'payroll_run_id'   => PayrollRunFactory::new()->create()->id,
            'staff_id'         => StaffFactory::new()->create()->id,
            'gross'            => $gross,
            'allowances_total' => 0,
            'deductions_total' => 0,
            'ssnit_employee'   => $ssnitEmployee,
            'tier2_employee'   => $tier2Employee,
            'paye'             => $paye,
            'ssnit_employer'   => round($gross * 0.13, 2),
            'tier2_employer'   => round($gross * 0.05, 2),
            'net'              => $net,
            'payment_status'   => 'pending',
        ];
    }
}
