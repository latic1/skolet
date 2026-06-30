<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\SendWebhookPayload;
use App\Models\Tenant\FeeBundle;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\Student;
use App\Services\PaystackService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class PaystackWebhookController extends Controller
{
    public function __construct(
        private readonly PaystackService $paystackService,
        private readonly ReceiptService $receiptService,
    ) {}

    public function handle(Request $request): Response
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-Paystack-Signature', '');

        if (! $this->paystackService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('[PaystackWebhook] Invalid signature — request rejected.');

            return response('Unauthorized', 401);
        }

        $event     = json_decode($rawPayload, true);
        $eventType = $event['event'] ?? '';

        if ($eventType !== 'charge.success') {
            return response('OK', 200);
        }

        $data      = $event['data'] ?? [];
        $reference = $data['reference'] ?? '';

        if (! $reference) {
            return response('Bad Request', 400);
        }

        // Idempotency: skip if already recorded
        if (FeePayment::where('paystack_ref', $reference)->exists()) {
            return response('OK', 200);
        }

        $verification = $this->paystackService->verifyTransaction($reference);

        if (! $verification['success']) {
            Log::error('[PaystackWebhook] Verification failed for ref: ' . $reference . ' — ' . $verification['error']);

            return response('Payment verification failed', 422);
        }

        $metadata       = $verification['metadata'];
        $studentId      = $metadata['student_id'] ?? null;
        $feeBundleId    = $metadata['fee_bundle_id'] ?? null;
        $feeStructureId = $metadata['fee_structure_id'] ?? null;

        if (! $studentId || (! $feeBundleId && ! $feeStructureId)) {
            Log::error('[PaystackWebhook] Missing metadata for ref: ' . $reference);

            return response('Bad Request', 400);
        }

        $student = Student::find($studentId);

        if (! $student) {
            Log::error('[PaystackWebhook] Student not found for ref: ' . $reference);

            return response('Not Found', 404);
        }

        try {
            if ($feeBundleId) {
                $bundle = FeeBundle::with('items')->find($feeBundleId);
                if (! $bundle) {
                    Log::error('[PaystackWebhook] Bundle not found for ref: ' . $reference);

                    return response('Not Found', 404);
                }
                $result = $this->receiptService->recordBundlePayment(
                    $student, $bundle, $verification['amount'], 'paystack', $reference
                );
            } else {
                $feeStructure = FeeStructure::find($feeStructureId);
                if (! $feeStructure) {
                    Log::error('[PaystackWebhook] FeeStructure not found for ref: ' . $reference);

                    return response('Not Found', 404);
                }
                $result = $this->receiptService->recordStandalonePayment(
                    $student, $feeStructure, $verification['amount'], 'paystack', $reference
                );
            }

            if (! $result['success']) {
                Log::error('[PaystackWebhook] Failed to record payment for ref: ' . $reference . ' — ' . $result['error']);

                return response('Internal Server Error', 500);
            }

            SendWebhookPayload::dispatch(tenant('id'), 'payment_received', [
                'event'     => 'payment_received',
                'tenant'    => tenant('id'),
                'timestamp' => now()->toIso8601String(),
                'data'      => [
                    'student_id'     => $student->id,
                    'student_name'   => $student->full_name,
                    'amount'         => $result['total_allocated'],
                    'receipt_number' => $result['receipt_number'],
                    'payment_method' => 'paystack',
                    'reference'      => $reference,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[PaystackWebhook] Failed for ref: ' . $reference . ' — ' . $e->getMessage());

            return response('Internal Server Error', 500);
        }

        return response('OK', 200);
    }
}
