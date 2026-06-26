<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\DataExportReadyMail;
use App\Models\Central\Tenant;
use App\Models\Tenant\Attendance;
use App\Models\Tenant\ExamResult;
use App\Models\Tenant\FeePayment;
use App\Models\Tenant\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

final class ExportStudentDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $studentId,
        private readonly string $tenantId,
        private readonly string $tenantHost,
        private readonly string $adminEmail,
        private readonly string $adminName,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            return;
        }

        $tenant->run(function (): void {
            try {
                $student = Student::withTrashed()->findOrFail($this->studentId);
                $token   = (string) Str::uuid();
                $dir     = storage_path("app/{$this->tenantId}/exports");

                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $zipPath = "{$dir}/{$token}.zip";
                $zip     = new ZipArchive();
                $zip->open($zipPath, ZipArchive::CREATE);

                // student.json
                $zip->addFromString('student.json', json_encode($student->toArray(), JSON_PRETTY_PRINT));

                // attendance.csv
                $attendance = Attendance::where('student_id', $student->id)
                    ->orderBy('date')
                    ->get(['date', 'status', 'note']);
                $zip->addFromString('attendance.csv', $this->toCsv(['date', 'status', 'note'], $attendance->toArray()));

                // exam_results.csv
                $results = ExamResult::where('student_id', $student->id)
                    ->with(['exam:id,name,start_date', 'subject:id,name'])
                    ->orderBy('created_at')
                    ->get();
                $resultRows = $results->map(fn ($r) => [
                    'exam'    => $r->exam?->name ?? '',
                    'date'    => $r->exam?->start_date ?? '',
                    'subject' => $r->subject?->name ?? '',
                    'marks'   => $r->marks,
                    'grade'   => $r->grade ?? '',
                    'remarks' => $r->remarks ?? '',
                ])->toArray();
                $zip->addFromString('exam_results.csv', $this->toCsv(['exam', 'date', 'subject', 'marks', 'grade', 'remarks'], $resultRows));

                // fee_payments.csv
                $payments = FeePayment::where('student_id', $student->id)
                    ->with('feeStructure:id,fee_item')
                    ->orderBy('paid_at')
                    ->get();
                $paymentRows = $payments->map(fn ($p) => [
                    'fee_item'       => $p->feeStructure?->fee_item ?? '',
                    'amount'         => $p->amount,
                    'payment_method' => $p->payment_method,
                    'paystack_ref'   => $p->paystack_ref ?? '',
                    'paid_at'        => $p->paid_at,
                ])->toArray();
                $zip->addFromString('fee_payments.csv', $this->toCsv(['fee_item', 'amount', 'payment_method', 'paystack_ref', 'paid_at'], $paymentRows));

                $zip->close();

                $downloadUrl = $this->tenantHost . '/export/download/' . $token;
                $expiresAt   = now()->addHours(24)->format('d M Y H:i') . ' UTC';

                Mail::to($this->adminEmail)->send(new DataExportReadyMail(
                    recipientName: $this->adminName,
                    exportType: 'Student: ' . $student->full_name,
                    downloadUrl: $downloadUrl,
                    expiresAt: $expiresAt,
                ));
            } catch (\Throwable $e) {
                Log::error('[ExportStudentDataJob] ' . $e->getMessage(), [
                    'student_id' => $this->studentId,
                    'tenant_id'  => $this->tenantId,
                ]);
            }
        });
    }

    /** @param array<int, string> $headers @param array<int, array<string, mixed>> $rows */
    private function toCsv(array $headers, array $rows): string
    {
        $out = implode(',', array_map(fn ($h) => '"' . $h . '"', $headers)) . "\n";
        foreach ($rows as $row) {
            $out .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) ($v ?? '')) . '"', array_values($row))) . "\n";
        }

        return $out;
    }
}
