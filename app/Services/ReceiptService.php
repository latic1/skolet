<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\NumberToWords;
use App\Models\Tenant\FeeBundle;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\FeeStructure;
use App\Models\Tenant\SchoolProfile;
use App\Models\Tenant\Student;
use App\Notifications\PaymentConfirmation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ReceiptService
{
    public function __construct(private readonly FeeStatusService $feeStatusService) {}

    // -------------------------------------------------------------------------
    // Receipt number
    // -------------------------------------------------------------------------

    public function generateReceiptNumber(): string
    {
        $profile = SchoolProfile::first();

        if ($profile?->receipt_prefix) {
            $prefix = strtoupper((string) $profile->receipt_prefix);
        } else {
            $name   = preg_replace('/[^A-Za-z]/', '', $profile?->school_name ?? 'SCH');
            $prefix = strtoupper(substr($name, 0, 4));
        }

        $sequence = FeePayment::whereDate('paid_at', today())
            ->whereNotNull('receipt_number')
            ->distinct()
            ->count('receipt_number') + 1;

        return sprintf('%s%s.%s.%02d',
            $prefix,
            now()->format('y'),
            now()->format('m'),
            $sequence
        );
    }

    // -------------------------------------------------------------------------
    // Recording payments
    // -------------------------------------------------------------------------

    /**
     * Record a bundle payment: allocates the paid amount across outstanding
     * bundle items greedily and creates one FeePayment row per allocated item,
     * all sharing the same receipt_number.
     *
     * @return array{success: bool, receipt_number: ?string, total_allocated: float, error: ?string}
     */
    public function recordBundlePayment(
        Student $student,
        FeeBundle $bundle,
        float $amount,
        string $method,
        ?string $paystackRef = null
    ): array {
        $feeItems = $this->feeStatusService->getStudentFeeItems($student, $bundle->term_id);

        $bundleItems = collect($feeItems)->filter(
            fn ($item) => $item['fee_structure']->fee_bundle_id === $bundle->id
        )->values();

        if ($bundleItems->isEmpty()) {
            return ['success' => false, 'receipt_number' => null, 'total_allocated' => 0, 'error' => 'No outstanding fee items found in this bundle.'];
        }

        $receiptNumber  = $this->generateReceiptNumber();
        $remaining      = $amount;
        $totalAllocated = 0.0;
        $firstPayment   = null;

        DB::beginTransaction();
        try {
            foreach ($bundleItems as $item) {
                if ($remaining <= 0) {
                    break;
                }

                $outstanding = $item['outstanding'];
                if ($outstanding <= 0) {
                    continue;
                }

                $allocation  = min($remaining, $outstanding);
                $remaining  -= $allocation;
                $totalAllocated += $allocation;

                $payment = FeePayment::create([
                    'receipt_number'   => $receiptNumber,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $item['fee_structure']->id,
                    'amount'           => $allocation,
                    'payment_method'   => $method,
                    'paystack_ref'     => $paystackRef,
                    'recorded_by'      => Auth::id(),
                    'paid_at'          => now(),
                ]);

                $firstPayment ??= $payment;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ReceiptService::recordBundlePayment] ' . $e->getMessage());

            return ['success' => false, 'receipt_number' => null, 'total_allocated' => 0, 'error' => 'Could not record payment. Please try again.'];
        }

        $this->sendNotification($student, $firstPayment);

        return ['success' => true, 'receipt_number' => $receiptNumber, 'total_allocated' => $totalAllocated, 'error' => null];
    }

    /**
     * Record a standalone (single item) payment.
     *
     * @return array{success: bool, receipt_number: ?string, total_allocated: float, error: ?string}
     */
    public function recordStandalonePayment(
        Student $student,
        FeeStructure $feeStructure,
        float $amount,
        string $method,
        ?string $paystackRef = null
    ): array {
        $receiptNumber = $this->generateReceiptNumber();

        try {
            $payment = FeePayment::create([
                'receipt_number'   => $receiptNumber,
                'student_id'       => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'amount'           => $amount,
                'payment_method'   => $method,
                'paystack_ref'     => $paystackRef,
                'recorded_by'      => Auth::id(),
                'paid_at'          => now(),
            ]);

            $this->sendNotification($student, $payment);

            return ['success' => true, 'receipt_number' => $receiptNumber, 'total_allocated' => $amount, 'error' => null];
        } catch (\Throwable $e) {
            Log::error('[ReceiptService::recordStandalonePayment] ' . $e->getMessage());

            return ['success' => false, 'receipt_number' => null, 'total_allocated' => 0, 'error' => 'Could not record payment. Please try again.'];
        }
    }

    // -------------------------------------------------------------------------
    // PDF generation
    // -------------------------------------------------------------------------

    /**
     * Build the view-data array for a receipt.
     * Accepts either a receipt_number string or a legacy payment UUID.
     */
    public function buildReceipt(string $receiptNumber): array
    {
        $payments = FeePayment::with([
            'student.schoolClass',
            'student.section',
            'feeStructure.term.academicYear',
            'feeStructure.bundle',
            'recordedBy',
        ])->where('receipt_number', $receiptNumber)->orderBy('paid_at')->get();

        // Backward compatibility: fall back to searching by payment UUID
        if ($payments->isEmpty() && Str::isUuid($receiptNumber)) {
            $single = FeePayment::with([
                'student.schoolClass',
                'student.section',
                'feeStructure.term.academicYear',
                'feeStructure.bundle',
                'recordedBy',
            ])->find($receiptNumber);

            if ($single) {
                $payments = collect([$single]);
            }
        }

        if ($payments->isEmpty()) {
            throw new \RuntimeException("No payment found for receipt: {$receiptNumber}");
        }

        $firstPayment  = $payments->first();
        $student       = $firstPayment->student;
        $schoolProfile = SchoolProfile::first();
        $logoBase64    = $this->loadLogoBase64($schoolProfile);
        $totalAmount   = (float) $payments->sum('amount');
        $method        = $firstPayment->payment_method ?? 'cash';
        $paidAt        = $firstPayment->paid_at ?? now();
        $accountOfficer = $firstPayment->recordedBy?->name;

        // Items list for the receipt table
        $feeLines = $payments->map(fn ($pmt) => [
            'fee_item' => $pmt->feeStructure?->fee_item ?? 'Fee Payment',
            'amount'   => (float) $pmt->amount,
        ]);

        // Description: bundle name or fee item, plus term/year
        $firstFs      = $firstPayment->feeStructure;
        $bundle       = $firstFs?->bundle;
        $term         = $firstFs?->term;
        $academicYear = $term?->academicYear ?? $firstFs?->academicYear;

        $description  = $bundle?->name ?? $firstFs?->fee_item ?? 'School Fees';
        if ($term) {
            $description .= ' — ' . $term->name;
        }
        if ($academicYear) {
            $description .= ' ' . $academicYear->name;
        }

        // Current balance: remaining outstanding after this payment
        $currentBalance = 0.0;
        if ($student) {
            $allItems       = $this->feeStatusService->getStudentFeeItems($student, $firstFs?->term_id);
            $currentBalance = max(0.0, (float) collect($allItems)->sum('outstanding'));
        }

        $paymentLabel  = $currentBalance <= 0.0 ? 'Full Payment' : 'Partial Payment';
        $amountInWords = NumberToWords::convert($totalAmount);

        $className = $student?->schoolClass?->name ?? '';
        if ($student?->section) {
            $className .= ' — ' . $student->section->name;
        }

        return [
            'receiptNumber'   => $receiptNumber,
            'payments'        => $payments,
            'student'         => $student,
            'schoolProfile'   => $schoolProfile,
            'logoBase64'      => $logoBase64,
            'totalAmount'     => $totalAmount,
            'method'          => $method,
            'paidAt'          => $paidAt,
            'accountOfficer'  => $accountOfficer,
            'feeLines'        => $feeLines,
            'description'     => $description,
            'currentBalance'  => $currentBalance,
            'paymentLabel'    => $paymentLabel,
            'amountInWords'   => $amountInWords,
            'className'       => $className,
            'academicYearName' => $academicYear?->name ?? '',
        ];
    }

    /**
     * Generate a receipt PDF for a receipt number (or legacy payment UUID).
     * Saves to local storage and returns the absolute path.
     */
    public function generatePdf(string $receiptNumber): string
    {
        $data      = $this->buildReceipt($receiptNumber);
        $student   = $data['student'];
        $tenantId  = tenant('id');
        $studentId = $student?->id ?? 'unknown';
        $disk      = Storage::disk('local');
        $safe      = preg_replace('/[^A-Za-z0-9_\-]/', '-', $receiptNumber);
        $directory = "{$tenantId}/receipts/{$studentId}";
        $filename  = "{$safe}.pdf";
        $path      = "{$directory}/{$filename}";

        try {
            $pdf = Pdf::loadView('tenant.fees.receipt-pdf', $data)
                ->setPaper('a4', 'portrait');

            $disk->makeDirectory($directory);
            $disk->put($path, $pdf->output());

            return storage_path("app/{$path}");
        } catch (\Throwable $e) {
            Log::error('[ReceiptService.generatePdf] ' . $e->getMessage(), [
                'receipt_number' => $receiptNumber,
            ]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function loadLogoBase64(?SchoolProfile $profile): ?string
    {
        if (! $profile?->logo_path) {
            return null;
        }

        try {
            $content = Storage::disk('public')->get($profile->logo_path);
            if (! $content) {
                return null;
            }

            $ext  = strtolower(pathinfo($profile->logo_path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'gif'         => 'image/gif',
                'webp'        => 'image/webp',
                'svg'         => 'image/svg+xml',
                default       => 'image/png',
            };

            return 'data:' . $mime . ';base64,' . base64_encode($content);
        } catch (\Throwable) {
            return null;
        }
    }

    private function sendNotification(Student $student, ?FeePayment $payment): void
    {
        if (! $payment) {
            return;
        }

        $profile = SchoolProfile::first();
        if ($profile?->isNotificationEnabled('payment_confirmation') && $student->guardian_email) {
            Notification::route('mail', $student->guardian_email)
                ->notify(new PaymentConfirmation($payment));
        }
    }
}
