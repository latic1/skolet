<?php

use App\Models\Tenant\PayrollItem;
use App\Models\Tenant\PayrollRun;
use App\Models\Tenant\SalaryStructure;
use App\Models\Tenant\Staff;
use App\Models\Tenant\User;
use App\Services\PayrollService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $this->adminUser = User::create([
        'name' => 'Payroll Admin', 'email' => 'payroll_admin@test.test', 'password' => bcrypt('pw'), 'role' => 'school_admin',
    ]);
    $this->actingAs($this->adminUser);

    $this->service = app(PayrollService::class);

    // Create active staff with salary structures
    $user1        = User::create(['name' => 'S1', 'email' => 's1@p.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff1 = Staff::create(['user_id' => $user1->id, 'full_name' => 'Staff One', 'role_title' => 'Teacher', 'status' => 'active']);
    SalaryStructure::create(['staff_id' => $this->staff1->id, 'gross' => 2000.00, 'allowances' => [], 'deductions' => [], 'effective_from' => now()->startOfMonth()->toDateString()]);

    $user2        = User::create(['name' => 'S2', 'email' => 's2@p.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff2 = Staff::create(['user_id' => $user2->id, 'full_name' => 'Staff Two', 'role_title' => 'Teacher', 'status' => 'active']);
    SalaryStructure::create(['staff_id' => $this->staff2->id, 'gross' => 3000.00, 'allowances' => [], 'deductions' => [], 'effective_from' => now()->startOfMonth()->toDateString()]);

    // Staff without salary structure (should be excluded)
    $user3        = User::create(['name' => 'S3', 'email' => 's3@p.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff3 = Staff::create(['user_id' => $user3->id, 'full_name' => 'Staff No Salary', 'role_title' => 'Teacher', 'status' => 'active']);

    // Inactive staff (should be excluded)
    $user4        = User::create(['name' => 'S4', 'email' => 's4@p.test', 'password' => bcrypt('pw'), 'role' => 'teacher']);
    $this->staff4 = Staff::create(['user_id' => $user4->id, 'full_name' => 'Inactive Staff', 'role_title' => 'Teacher', 'status' => 'inactive']);
    SalaryStructure::create(['staff_id' => $this->staff4->id, 'gross' => 1500.00, 'allowances' => [], 'deductions' => [], 'effective_from' => now()->startOfMonth()->toDateString()]);
});

test('running payroll creates one payroll_item per active staff with a salary structure', function (): void {
    $run = $this->service->runPayroll(1, 2026);

    expect($run)->toBeInstanceOf(PayrollRun::class);

    $itemCount = PayrollItem::where('payroll_run_id', $run->id)->count();
    // Only staff1 and staff2 have salary structures AND are active
    expect($itemCount)->toBe(2);
});

test('staff without a salary structure are excluded from the payroll run', function (): void {
    $run = $this->service->runPayroll(2, 2026);

    $staffIds = PayrollItem::where('payroll_run_id', $run->id)->pluck('staff_id');

    expect($staffIds)->not->toContain($this->staff3->id);
});

test('inactive staff are excluded from the payroll run', function (): void {
    $run = $this->service->runPayroll(3, 2026);

    $staffIds = PayrollItem::where('payroll_run_id', $run->id)->pluck('staff_id');

    expect($staffIds)->not->toContain($this->staff4->id);
});

test('running payroll for the same month and year twice throws a unique constraint error', function (): void {
    $this->service->runPayroll(6, 2026);

    expect(fn () => $this->service->runPayroll(6, 2026))->toThrow(QueryException::class);
});

test('payroll item SSNIT employee is 5.5 percent of gross', function (): void {
    $run  = $this->service->runPayroll(7, 2026);
    $item = PayrollItem::where('payroll_run_id', $run->id)
        ->where('staff_id', $this->staff1->id)
        ->first();

    $expected = round(2000.00 * 0.055, 2);
    expect((float) $item->ssnit_employee)->toBe($expected);
});

test('payroll item Tier2 employee is 5 percent of gross', function (): void {
    $run  = $this->service->runPayroll(8, 2026);
    $item = PayrollItem::where('payroll_run_id', $run->id)
        ->where('staff_id', $this->staff1->id)
        ->first();

    $expected = round(2000.00 * 0.05, 2);
    expect((float) $item->tier2_employee)->toBe($expected);
});

test('payroll item net pay equals gross minus statutory employee deductions', function (): void {
    $run  = $this->service->runPayroll(9, 2026);
    $item = PayrollItem::where('payroll_run_id', $run->id)
        ->where('staff_id', $this->staff1->id)
        ->first();

    $gross         = 2000.00;
    $ssnitEmployee = round($gross * 0.055, 2);
    $tier2Employee = round($gross * 0.05, 2);
    $taxable       = max(0, $gross - $ssnitEmployee - $tier2Employee);
    $paye          = $this->computeExpectedPaye($taxable);
    $expectedNet   = max(0.0, $gross - $ssnitEmployee - $tier2Employee - $paye);

    expect(abs((float) $item->net - $expectedNet))->toBeLessThan(0.02);
});

test('mark-as-paid updates payment_status and saves payment_method and paid_at', function (): void {
    $run  = $this->service->runPayroll(10, 2026);
    $item = PayrollItem::where('payroll_run_id', $run->id)->first();

    $item->update([
        'payment_status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at'        => now(),
    ]);

    $fresh = PayrollItem::find($item->id);
    expect($fresh->payment_status)->toBe('paid')
        ->and($fresh->payment_method)->toBe('bank_transfer')
        ->and($fresh->paid_at)->not->toBeNull();
});

function computeExpectedPaye(float $income): float
{
    $tax   = 0.0;
    $bands = config('payroll.paye_bands');
    foreach ($bands as $band) {
        if ($income <= 0) {
            break;
        }
        $slice   = $band['limit'] === null ? $income : min($income, (float) $band['limit']);
        $tax    += $slice * $band['rate'];
        $income -= $slice;
    }
    return round($tax, 2);
}
