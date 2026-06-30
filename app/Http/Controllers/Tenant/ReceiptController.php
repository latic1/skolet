<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\Student;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
    ) {}

    /**
     * Download a receipt PDF by receipt_number (or legacy payment UUID).
     *
     * Access:
     *   - fees.view (admin/accountant) → any receipt
     *   - Student/parent → only their own student's receipts
     */
    public function download(Request $request, string $receiptNumber): BinaryFileResponse
    {
        $user = Auth::user();

        // Resolve payments — by receipt_number first, UUID fallback for old receipts
        $payments = FeePayment::where('receipt_number', $receiptNumber)
            ->with('student')
            ->get();

        if ($payments->isEmpty()) {
            // Backward compatibility: single payment accessed by its UUID
            $single = FeePayment::with('student')->find($receiptNumber);
            if ($single) {
                $payments = collect([$single]);
                // Use the stored receipt_number if present, otherwise keep using UUID as key
                $receiptNumber = $single->receipt_number ?? $receiptNumber;
            }
        }

        if ($payments->isEmpty()) {
            abort(404, 'Receipt not found.');
        }

        if (! $user->can('fees.view')) {
            // Student / parent: verify ownership
            $studentIds  = $payments->pluck('student_id')->unique();
            $ownedStudent = Student::where('user_id', $user->id)
                ->whereIn('id', $studentIds)
                ->exists();

            if (! $ownedStudent) {
                abort(403);
            }
        }

        try {
            $absolutePath = $this->receiptService->generatePdf($receiptNumber);
        } catch (\Throwable) {
            abort(500, 'Receipt could not be generated. Please try again.');
        }

        $student   = $payments->first()->student;
        $safeName  = preg_replace('/[^a-z0-9]+/i', '-', $student?->full_name ?? 'student');
        $safeNum   = preg_replace('/[^a-z0-9]+/i', '-', strtolower($receiptNumber));
        $filename  = "receipt-{$safeName}-{$safeNum}.pdf";

        return response()->file($absolutePath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
