<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\DataExportReadyMail;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use ZipArchive;

final class ExportAllSchoolDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
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
                $token = (string) Str::uuid();
                $dir   = storage_path("app/{$this->tenantId}/exports");

                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $zipPath = "{$dir}/{$token}.zip";
                $zip     = new ZipArchive();
                $zip->open($zipPath, ZipArchive::CREATE);

                $tables = [
                    'students', 'staff', 'users', 'school_classes', 'sections',
                    'subjects', 'academic_years', 'terms', 'exams', 'exam_results',
                    'attendances', 'staff_attendances', 'fee_structures', 'fee_payments',
                    'announcements', 'expenses',
                ];

                foreach ($tables as $table) {
                    try {
                        $rows = DB::table($table)->get();
                        if ($rows->isEmpty()) {
                            continue;
                        }

                        $headers = array_keys((array) $rows->first());
                        $zip->addFromString(
                            "{$table}.csv",
                            $this->toCsv($headers, $rows->map(fn ($r) => (array) $r)->toArray())
                        );
                    } catch (\Throwable) {
                        // Table may not exist in older tenant DBs — skip
                    }
                }

                $zip->close();

                $downloadUrl = $this->tenantHost . '/export/download/' . $token;
                $expiresAt   = now()->addHours(24)->format('d M Y H:i') . ' UTC';

                Mail::to($this->adminEmail)->send(new DataExportReadyMail(
                    recipientName: $this->adminName,
                    exportType: 'Full School Data',
                    downloadUrl: $downloadUrl,
                    expiresAt: $expiresAt,
                ));
            } catch (\Throwable $e) {
                Log::error('[ExportAllSchoolDataJob] ' . $e->getMessage(), [
                    'tenant_id' => $this->tenantId,
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
