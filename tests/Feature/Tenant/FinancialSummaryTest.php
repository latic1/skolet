<?php

use App\Models\Tenant\AcademicYear;
use App\Models\Tenant\Expense;
use App\Models\Tenant\ExpenseCategory;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Term;
use App\Models\Tenant\User;
use App\Services\FinancialSummaryService;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $this->adminUser = User::create([
        'name' => 'Admin', 'email' => 'admin_fs@test.test', 'password' => bcrypt('pw'), 'role' => 'school_admin',
    ]);

    $this->year = AcademicYear::create([
        'name'       => '2026/2027',
        'start_date' => '2026-01-01',
        'end_date'   => '2026-12-31',
        'is_current' => true,
    ]);

    $this->term = Term::create([
        'academic_year_id' => $this->year->id,
        'name'             => 'Term 1',
        'start_date'       => '2026-01-01',
        'end_date'         => '2026-04-30',
        'is_current'       => true,
    ]);

    $class = SchoolClass::create(['name' => 'FS1', 'order' => 1]);

    $this->student = Student::create([
        'admission_no'     => '2026/1400',
        'full_name'        => 'Financial Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000014',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'         => 'School Fees',
        'amount'           => 800.00,
        'billing_cycle'    => 'term',
        'target_class'     => 'all',
        'is_mandatory'     => true,
        'due_date'         => '2026-02-28',
        'academic_year_id' => $this->year->id,
        'term_id'          => $this->term->id,
    ]);

    $this->expenseCategory = ExpenseCategory::create(['name' => 'Salaries']);

    $this->service = app(FinancialSummaryService::class);
});

test('FinancialSummaryService returns correct income total from fee_payments', function (): void {
    FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 400.00,
        'payment_method'   => 'cash',
        'paid_at'          => '2026-02-15',
    ]);

    FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 400.00,
        'payment_method'   => 'cash',
        'paid_at'          => '2026-03-01',
    ]);

    $data = $this->service->build($this->year->id, $this->term->id);

    expect($data['income_total'])->toBe(800.0);
});

test('expense total matches sum of expenses in the date range', function (): void {
    Expense::create(['category_id' => $this->expenseCategory->id, 'amount' => 300.00, 'date' => '2026-01-15', 'description' => 'January salaries', 'recorded_by' => $this->adminUser->id]);
    Expense::create(['category_id' => $this->expenseCategory->id, 'amount' => 200.00, 'date' => '2026-02-15', 'description' => 'February utilities', 'recorded_by' => $this->adminUser->id]);
    // Outside date range
    Expense::create(['category_id' => $this->expenseCategory->id, 'amount' => 999.00, 'date' => '2025-12-15', 'description' => 'Old expense', 'recorded_by' => $this->adminUser->id]);

    $data = $this->service->build($this->year->id, $this->term->id);

    expect($data['expense_total'])->toBe(500.0);
});

test('net balance equals income minus expenses', function (): void {
    FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 600.00,
        'payment_method'   => 'cash',
        'paid_at'          => '2026-02-01',
    ]);

    Expense::create(['category_id' => $this->expenseCategory->id, 'amount' => 250.00, 'date' => '2026-01-20', 'description' => 'Expense', 'recorded_by' => $this->adminUser->id]);

    $data = $this->service->build($this->year->id, $this->term->id);

    expect($data['net'])->toBe(350.0);
});

test('negative net balance is returned when expenses exceed income', function (): void {
    Expense::create(['category_id' => $this->expenseCategory->id, 'amount' => 1000.00, 'date' => '2026-01-05', 'description' => 'Overspend', 'recorded_by' => $this->adminUser->id]);

    $data = $this->service->build($this->year->id, $this->term->id);

    expect($data['net'])->toBeLessThan(0);
});

test('monthly trend array contains one entry per month in the range', function (): void {
    $data = $this->service->build($this->year->id, $this->term->id);

    // Term is Jan–Apr 2026 → 4 months
    expect(count($data['monthly_trend']))->toBe(4);
    expect($data['monthly_trend'][0]['month'])->toBe('Jan 2026');
    expect($data['monthly_trend'][3]['month'])->toBe('Apr 2026');
});

test('income breakdown by category is returned', function (): void {
    FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 500.00,
        'payment_method'   => 'cash',
        'paid_at'          => '2026-02-01',
    ]);

    $data = $this->service->build($this->year->id, $this->term->id);

    expect($data['income_by_category'])->toBeArray()
        ->and(count($data['income_by_category']))->toBeGreaterThanOrEqual(1);
});
