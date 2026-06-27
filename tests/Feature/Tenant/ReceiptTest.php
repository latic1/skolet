<?php

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\ReceiptService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'accountant',   'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student',      'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'fees.view', 'guard_name' => 'web']);

    $class = SchoolClass::create(['name' => 'RC1', 'order' => 1]);

    $studentUser = User::create([
        'name'     => 'Receipt Student',
        'email'    => 'rcstudent@test.test',
        'password' => bcrypt('pw'),
        'role'     => 'student',
    ]);

    $this->student = Student::create([
        'admission_no'     => '2026/0600',
        'full_name'        => 'Receipt Student',
        'gender'           => 'female',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000006',
        'guardian_email'   => 'guardian@receipt.test',
        'status'           => 'active',
        'class_id'         => $class->id,
        'user_id'          => $studentUser->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'      => 'Term Fees',
        'amount'        => 450.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addMonth()->toDateString(),
    ]);

    $this->payment = FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 450.00,
        'payment_method'   => 'cash',
        'paid_at'          => now(),
    ]);

    $this->studentUser = $studentUser;
});

test('receipt service builds correct data for a fee payment', function (): void {
    $service = app(ReceiptService::class);
    $data    = $service->build($this->payment);

    expect($data)->toBeArray()
        ->and($data['payment']->id)->toBe($this->payment->id)
        ->and($data['payment']->student->id)->toBe($this->student->id)
        ->and($data['receiptNo'])->not->toBeEmpty();
});

test('receipt number is derived from payment UUID', function (): void {
    $service = app(ReceiptService::class);
    $data    = $service->build($this->payment);

    // Receipt number is first 10 hex chars of UUID
    expect(strlen($data['receiptNo']))->toBe(10)
        ->and(ctype_xdigit($data['receiptNo']))->toBeTrue();
});

test('student can access their own fee payment receipt data', function (): void {
    // Verify student's user_id matches the student record linked to the payment
    $paymentStudent = $this->payment->fresh()->student;

    expect($paymentStudent->user_id)->toBe($this->studentUser->id);
});

test('fee payment is linked to student and fee structure', function (): void {
    $payment = FeePayment::with(['student', 'feeStructure'])->find($this->payment->id);

    expect($payment->student->full_name)->toBe('Receipt Student')
        ->and($payment->feeStructure->fee_item)->toBe('Term Fees')
        ->and((float) $payment->amount)->toBe(450.0);
});

test('fee payment record has correct payment method and amount', function (): void {
    $payment = FeePayment::find($this->payment->id);

    expect($payment->payment_method)->toBe('cash')
        ->and((float) $payment->amount)->toBe(450.0)
        ->and($payment->paid_at)->not->toBeNull();
});
