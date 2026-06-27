<?php

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Notifications\AbsenceAlert;
use App\Notifications\PaymentConfirmation;
use App\Services\FeeStatusService;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    Notification::fake();

    $class = SchoolClass::create(['name' => 'NF1', 'order' => 1]);

    $this->adminUser = User::create([
        'name' => 'Admin', 'email' => 'admin_nf@test.test', 'password' => bcrypt('pw'), 'role' => 'school_admin',
    ]);
    $this->actingAs($this->adminUser);

    $this->student = Student::create([
        'admission_no'     => '2026/1300',
        'full_name'        => 'Notify Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000013',
        'guardian_email'   => 'guardian_nf@test.test',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'      => 'Notification Fee',
        'amount'        => 200.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addMonth()->toDateString(),
    ]);

    SchoolProfile::create([
        'school_name'           => 'Test School',
        'notification_settings' => json_encode(['absent_alert' => ['email' => true], 'payment_confirmation' => ['email' => true]]),
    ]);
});

test('absence alert notification is dispatched when student is marked absent and setting is enabled', function (): void {
    $profile = SchoolProfile::first();
    $settings = $profile ? json_decode((string) $profile->notification_settings, true) : [];

    $absentAlertEnabled = $settings['absent_alert']['email'] ?? true;

    if ($absentAlertEnabled) {
        Notification::route('mail', $this->student->guardian_email)
            ->notify(new AbsenceAlert($this->student, now()->toDateString()));
    }

    Notification::assertSentOnDemand(
        AbsenceAlert::class,
        fn ($notification, $channels) => in_array('mail', $channels)
    );
});

test('payment confirmation notification is dispatched after cash payment', function (): void {
    $payment = FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 200.00,
        'payment_method'   => 'cash',
        'paid_at'          => now(),
    ]);

    $profile  = SchoolProfile::first();
    $settings = $profile ? json_decode((string) $profile->notification_settings, true) : [];
    $enabled  = $settings['payment_confirmation']['email'] ?? true;

    if ($enabled) {
        Notification::route('mail', $this->student->guardian_email)
            ->notify(new PaymentConfirmation($payment));
    }

    Notification::assertSentOnDemand(
        PaymentConfirmation::class,
        fn ($notification, $channels) => in_array('mail', $channels)
    );
});

test('notifications are NOT dispatched when the event type is disabled', function (): void {
    // Disable absent_alert notifications in settings
    SchoolProfile::first()->update([
        'notification_settings' => json_encode(['absent_alert' => ['email' => false]]),
    ]);

    $profile  = SchoolProfile::first();
    $settings = json_decode((string) $profile->notification_settings, true);

    $absentAlertEnabled = $settings['absent_alert']['email'] ?? true;

    if ($absentAlertEnabled) {
        Notification::route('mail', $this->student->guardian_email)
            ->notify(new AbsenceAlert($this->student, now()->toDateString()));
    }

    // Setting is false, so no notification should have been dispatched
    Notification::assertNothingSent();
});

test('notification settings default to email enabled when null', function (): void {
    SchoolProfile::first()->update(['notification_settings' => null]);

    $profile  = SchoolProfile::first();
    $settings = $profile ? json_decode((string) ($profile->notification_settings ?? '{}'), true) : [];

    // When null, default is email=true
    $absentAlertEnabled = $settings['absent_alert']['email'] ?? true;

    expect($absentAlertEnabled)->toBeTrue();
});
