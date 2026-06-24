<?php

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\FeeStatusService;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'accountant',   'guard_name' => 'web']);

    $class = SchoolClass::create(['name' => 'Class C', 'order' => 1]);

    $this->student = Student::create([
        'admission_no'     => '2026/0300',
        'full_name'        => 'Fee Tester',
        'gender'           => 'female',
        'guardian_name'    => 'Parent',
        'guardian_contact' => '0200000003',
        'guardian_email'   => 'parent@test.com',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'      => 'Tuition Fee',
        'amount'        => 500.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addDays(30)->toDateString(),
    ]);

    $this->service = app(FeeStatusService::class);
});

test('fee structure is created with correct attributes', function (): void {
    expect($this->feeStructure)->toBeInstanceOf(FeeStructure::class)
        ->and($this->feeStructure->fee_item)->toBe('Tuition Fee')
        ->and((float) $this->feeStructure->amount)->toBe(500.0);
});

test('computeStatus returns unpaid when no payment exists', function (): void {
    $status = $this->service->computeStatus(500.0, 0.0, Carbon::tomorrow());
    expect($status)->toBe('unpaid');
});

test('computeStatus returns partial when some payment exists', function (): void {
    $status = $this->service->computeStatus(500.0, 200.0, Carbon::tomorrow());
    expect($status)->toBe('partial');
});

test('computeStatus returns paid when full amount is paid', function (): void {
    $status = $this->service->computeStatus(500.0, 500.0, Carbon::tomorrow());
    expect($status)->toBe('paid');
});

test('computeStatus returns overdue when due date is past and unpaid', function (): void {
    $status = $this->service->computeStatus(500.0, 0.0, Carbon::yesterday());
    expect($status)->toBe('overdue');
});

test('computeStatus returns overdue when due date is past and partially paid', function (): void {
    $status = $this->service->computeStatus(500.0, 200.0, Carbon::yesterday());
    expect($status)->toBe('overdue');
});

test('recordCashPayment creates a fee payment record', function (): void {
    $admin = User::create([
        'name'     => 'Admin',
        'email'    => 'admin@test.test',
        'password' => bcrypt('password'),
        'role'     => 'school_admin',
    ]);

    $this->actingAs($admin);

    $result = $this->service->recordCashPayment($this->student, $this->feeStructure, 250.0);

    expect($result['success'])->toBeTrue()
        ->and($result['error'])->toBeNull();

    expect(
        FeePayment::where('student_id', $this->student->id)
            ->where('fee_structure_id', $this->feeStructure->id)
            ->exists()
    )->toBeTrue();
});

test('getStudentFeeItems returns items for students class', function (): void {
    $items = $this->service->getStudentFeeItems($this->student);

    expect($items)->toBeArray()
        ->and(count($items))->toBeGreaterThanOrEqual(1);

    $item = collect($items)->firstWhere(fn ($i) => $i['fee_structure']->id === $this->feeStructure->id);
    expect($item)->not->toBeNull()
        ->and($item['paid_amount'])->toBe(0.0)
        ->and($item['status'])->toBe('unpaid');
});
