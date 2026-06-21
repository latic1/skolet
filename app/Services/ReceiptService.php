<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\FeePayment;
use App\Models\Tenant\SchoolProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ReceiptService
{
    /**
     * Build the data array for a receipt PDF.
     */
    public function build(FeePayment $payment): array
    {
        $payment->loadMissing([
            'student.schoolClass',
            'student.section',
            'feeStructure.term.academicYear',
            'recordedBy',
        ]);

        $schoolProfile = SchoolProfile::first();
        $logoBase64    = null;

        if ($schoolProfile?->logo_path) {
            try {
                $content = Storage::disk('public')->get($schoolProfile->logo_path);
                if ($content) {
                    $ext        = strtolower(pathinfo($schoolProfile->logo_path, PATHINFO_EXTENSION));
                    $mime       = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png'         => 'image/png',
                        'gif'         => 'image/gif',
                        'webp'        => 'image/webp',
                        'svg'         => 'image/svg+xml',
                        default       => 'image/png',
                    };
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            } catch (\Throwable) {
                // Logo not critical — receipt still generates without it
            }
        }

        return [
            'payment'       => $payment,
            'schoolProfile' => $schoolProfile,
            'logoBase64'    => $logoBase64,
            'receiptNo'     => strtoupper(substr(str_replace('-', '', $payment->id), 0, 10)),
        ];
    }

    /**
     * Generate (or regenerate) a receipt PDF for a fee payment.
     * Saves to storage/{tenant}/receipts/{student_id}/{payment_id}.pdf
     * Returns the absolute local path, or throws on failure.
     */
    public function generatePdf(FeePayment $payment): string
    {
        $tenantId  = tenant('id');
        $studentId = $payment->student_id;
        $disk      = Storage::disk('local');
        $directory = "{$tenantId}/receipts/{$studentId}";
        $filename  = "{$payment->id}.pdf";
        $path      = "{$directory}/{$filename}";

        try {
            $data = $this->build($payment);

            $pdf = Pdf::loadView('tenant.fees.receipt-pdf', $data)
                ->setPaper('a4', 'portrait');

            $disk->makeDirectory($directory);
            $disk->put($path, $pdf->output());

            return storage_path("app/{$path}");
        } catch (\Throwable $e) {
            Log::error('[ReceiptService.generatePdf] ' . $e->getMessage(), [
                'payment_id' => $payment->id,
            ]);
            throw $e;
        }
    }
}
