<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FeePayment;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ReceiptController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
    ) {}

    /**
     * Download a fee payment receipt PDF.
     *
     * Access:
     *   - Users with fees.view (admin / accountant) can download any receipt.
     *   - Students / parents can only download receipts for their own student record.
     */
    public function download(Request $request, FeePayment $feePayment): BinaryFileResponse
    {
        $user = Auth::user();

        if (! $user->can('fees.view')) {
            // Student / parent — may only download their own receipt
            $student = $feePayment->student;
            if (! $student || $student->user_id !== $user->id) {
                abort(403);
            }
        }

        try {
            $absolutePath = $this->receiptService->generatePdf($feePayment);
        } catch (\Throwable) {
            abort(500, 'Receipt could not be generated. Please try again.');
        }

        $student    = $feePayment->student;
        $feeItem    = $feePayment->feeStructure?->fee_item ?? 'receipt';
        $safeName   = preg_replace('/[^a-z0-9]+/i', '-', $student?->full_name ?? 'student');
        $safeFee    = preg_replace('/[^a-z0-9]+/i', '-', $feeItem);
        $filename   = strtolower("receipt-{$safeName}-{$safeFee}.pdf");

        return response()->file($absolutePath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
