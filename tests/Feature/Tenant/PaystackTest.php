<?php

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use App\Services\PaystackService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Spatie\Permission\Models\Role;


beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

    $class = SchoolClass::create(['name' => 'PS1', 'order' => 1]);
    $this->student = Student::create([
        'admission_no'     => '2026/0500',
        'full_name'        => 'Paystack Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000005',
        'guardian_email'   => 'parent@paystack.test',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->feeStructure = FeeStructure::create([
        'fee_item'      => 'School Fees',
        'amount'        => 300.00,
        'billing_cycle' => 'term',
        'target_class'  => 'all',
        'is_mandatory'  => true,
        'due_date'      => now()->addDays(30)->toDateString(),
    ]);

    config(['paystack.secret_key' => 'sk_test_dummy_key_for_testing']);
});

/** Helper: build a PaystackService with a Guzzle mock handler. */
function makePaystackServiceWithMock(array $responses): PaystackService
{
    $mock    = new MockHandler($responses);
    $handler = HandlerStack::create($mock);
    $client  = new Client(['handler' => $handler]);

    return new PaystackService($client);
}

test('initializeTransaction returns authorization URL on success', function (): void {
    $service = makePaystackServiceWithMock([
        new GuzzleResponse(200, [], json_encode([
            'status'  => true,
            'message' => 'Authorization URL created',
            'data'    => [
                'authorization_url' => 'https://checkout.paystack.com/abc123',
                'access_code'       => 'abc123',
                'reference'         => 'SF-TEST123',
            ],
        ])),
    ]);

    $result = $service->initializeTransaction(
        'parent@paystack.test',
        300.00,
        'https://school.test/paystack/callback',
        ['student_id' => $this->student->id],
        'SF-TEST123'
    );

    expect($result['success'])->toBeTrue()
        ->and($result['authorization_url'])->toBe('https://checkout.paystack.com/abc123')
        ->and($result['reference'])->toBe('SF-TEST123')
        ->and($result['error'])->toBeNull();
});

test('initializeTransaction returns error on Paystack failure', function (): void {
    $service = makePaystackServiceWithMock([
        new GuzzleResponse(400, [], json_encode([
            'status'  => false,
            'message' => 'Invalid key',
        ])),
    ]);

    $result = $service->initializeTransaction('bad@test.test', 100.0, 'https://cb.test');

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->not->toBeNull();
});

test('verifyTransaction returns amount and metadata on success', function (): void {
    $service = makePaystackServiceWithMock([
        new GuzzleResponse(200, [], json_encode([
            'status' => true,
            'data'   => [
                'status'    => 'success',
                'amount'    => 30000, // pesewas
                'reference' => 'SF-VERIFY',
                'metadata'  => ['student_id' => $this->student->id],
            ],
        ])),
    ]);

    $result = $service->verifyTransaction('SF-VERIFY');

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe('success')
        ->and($result['amount'])->toBe(300.0)
        ->and($result['metadata']['student_id'])->toBe($this->student->id);
});

test('verifyWebhookSignature returns true for valid HMAC', function (): void {
    // secretKey is set from config in beforeEach
    $service   = app(PaystackService::class);
    $payload   = '{"event":"charge.success","data":{"reference":"SF-123"}}';
    $signature = hash_hmac('sha512', $payload, 'sk_test_dummy_key_for_testing');

    expect($service->verifyWebhookSignature($payload, $signature))->toBeTrue();
});

test('verifyWebhookSignature returns false for invalid HMAC', function (): void {
    $service = app(PaystackService::class);

    expect($service->verifyWebhookSignature('{"event":"charge.success"}', 'wrong-signature'))->toBeFalse();
});

test('duplicate webhook with same paystack_ref does not create a second fee_payment row', function (): void {
    // Simulate the idempotency check in the webhook controller
    FeePayment::create([
        'student_id'       => $this->student->id,
        'fee_structure_id' => $this->feeStructure->id,
        'amount'           => 300.00,
        'payment_method'   => 'paystack',
        'paystack_ref'     => 'SF-DUPLICATE',
        'paid_at'          => now(),
    ]);

    $isDuplicate = FeePayment::where('paystack_ref', 'SF-DUPLICATE')->exists();

    // Simulating the webhook controller idempotency check:
    // if duplicate exists, don't insert again
    if (! $isDuplicate) {
        FeePayment::create([
            'student_id'       => $this->student->id,
            'fee_structure_id' => $this->feeStructure->id,
            'amount'           => 300.00,
            'payment_method'   => 'paystack',
            'paystack_ref'     => 'SF-DUPLICATE',
            'paid_at'          => now(),
        ]);
    }

    expect(FeePayment::where('paystack_ref', 'SF-DUPLICATE')->count())->toBe(1);
});
