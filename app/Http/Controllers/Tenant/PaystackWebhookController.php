<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\Student;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class PaystackWebhookController extends Controller
{
    public function __construct(private readonly PaystackService $paystackService) {}

    public function handle(Request $request): Response
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-Paystack-Signature', '');

        // Step 1: verify the webhook signature before processing anything
        if (! $this->paystackService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('[PaystackWebhook] Invalid signature — request rejected.');

            return response('Unauthorized', 401);
        }

        $event     = json_decode($rawPayload, true);
        $eventType = $event['event'] ?? '';

        // Only process successful charge events
        if ($eventType !== 'charge.success') {
            return response('OK', 200);
        }

        $data      = $event['data'] ?? [];
        $reference = $data['reference'] ?? '';

        if (! $reference) {
            return response('Bad Request', 400);
        }

        // Step 2: idempotency — skip if already recorded by a prior webhook or callback
        if (FeePayment::where('paystack_ref', $reference)->exists()) {
            return response('OK', 200);
        }

        // Step 3: always verify with the Paystack API before recording payment
        $verification = $this->paystackService->verifyTransaction($reference);

        if (! $verification['success']) {
            Log::error('[PaystackWebhook] Verification failed for ref: ' . $reference . ' — ' . $verification['error']);

            return response('Payment verification failed', 422);
        }

        // Step 4: extract tenant-scoped IDs from metadata
        $metadata       = $verification['metadata'];
        $studentId      = $metadata['student_id'] ?? null;
        $feeStructureId = $metadata['fee_structure_id'] ?? null;

        if (! $studentId || ! $feeStructureId) {
            Log::error('[PaystackWebhook] Missing metadata for ref: ' . $reference);

            return response('Bad Request', 400);
        }

        $student      = Student::find($studentId);
        $feeStructure = FeeStructure::find($feeStructureId);

        if (! $student || ! $feeStructure) {
            Log::error('[PaystackWebhook] Student or FeeStructure not found for ref: ' . $reference);

            return response('Not Found', 404);
        }

        // Step 5: record the payment
        try {
            FeePayment::create([
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'amount'           => $verification['amount'],
                'payment_method'   => 'paystack',
                'paystack_ref'     => $reference,
                'recorded_by'      => null,
                'paid_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[PaystackWebhook] Failed to record payment for ref: ' . $reference . ' — ' . $e->getMessage());

            return response('Internal Server Error', 500);
        }

        return response('OK', 200);
    }
}
