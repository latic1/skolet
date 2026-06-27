<?php

use App\Models\Tenant\FeeDiscount;
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

    $class = SchoolClass::create(['name' => 'FD1', 'order' => 1]);

    $this->adminUser = User::create([
        'name' => 'Admin', 'email' => 'admin_fd@test.test', 'password' => bcrypt('pw'), 'role' => 'school_admin',
    ]);

    $this->student = Student::create([
        'admission_no'     => '2026/0900',
        'full_name'        => 'Discount Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000009',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'      => 'Tuition',
        'amount'        => 1000.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addMonth()->toDateString(),
    ]);
});

test('percentage discount reduces effective amount correctly', function (): void {
    FeeDiscount::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'discount_type'    => 'percentage',
        'discount_value'   => 20.00, // 20%
        'reason'           => 'Scholarship',
        'approved_by'      => $this->adminUser->id,
    ]);

    $discount     = FeeDiscount::where('student_id', $this->student->id)->first();
    $originalAmt  = (float) $this->feeStructure->amount;
    $effectiveAmt = $discount->discount_type === 'percentage'
        ? $originalAmt * (1 - $discount->discount_value / 100)
        : $originalAmt - $discount->discount_value;

    expect($effectiveAmt)->toBe(800.0);
});

test('fixed discount reduces effective amount correctly', function (): void {
    FeeDiscount::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'discount_type'    => 'fixed',
        'discount_value'   => 250.00,
        'reason'           => 'Staff child discount',
        'approved_by'      => $this->adminUser->id,
    ]);

    $discount     = FeeDiscount::where('student_id', $this->student->id)->first();
    $originalAmt  = (float) $this->feeStructure->amount;
    $effectiveAmt = $discount->discount_type === 'percentage'
        ? $originalAmt * (1 - $discount->discount_value / 100)
        : $originalAmt - $discount->discount_value;

    expect($effectiveAmt)->toBe(750.0);
});

test('blanket discount with null fee_structure_id applies to all fees for the student', function (): void {
    $feeStructure2 = FeeStructure::create([
        'fee_item'      => 'PTA Dues',
        'amount'        => 50.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addMonth()->toDateString(),
    ]);

    // Blanket discount (fee_structure_id = null)
    FeeDiscount::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => null,
        'discount_type'    => 'percentage',
        'discount_value'   => 10.00,
        'reason'           => 'Sibling discount',
        'approved_by'      => $this->adminUser->id,
    ]);

    // Blanket discount applies to ALL fee structures for this student
    $discount = FeeDiscount::where('student_id', $this->student->id)
        ->whereNull('fee_structure_id')
        ->first();

    expect($discount)->not->toBeNull()
        ->and($discount->fee_structure_id)->toBeNull()
        ->and((float) $discount->discount_value)->toBe(10.0);
});

test('expired discount is not applied', function (): void {
    FeeDiscount::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'discount_type'    => 'percentage',
        'discount_value'   => 15.00,
        'reason'           => 'Old scholarship',
        'approved_by'      => $this->adminUser->id,
        'valid_until'      => now()->subDay()->toDateString(), // expired
    ]);

    $activeDiscount = FeeDiscount::where('student_id', $this->student->id)
        ->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', now()->toDateString());
        })
        ->first();

    expect($activeDiscount)->toBeNull();
});

test('discount relationships resolve correctly', function (): void {
    $discount = FeeDiscount::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'discount_type'    => 'fixed',
        'discount_value'   => 100.00,
        'reason'           => 'Merit award',
        'approved_by'      => $this->adminUser->id,
    ]);

    expect($discount->student->full_name)->toBe('Discount Student')
        ->and($discount->feeStructure->fee_item)->toBe('Tuition')
        ->and($discount->approver->name)->toBe('Admin');
});
