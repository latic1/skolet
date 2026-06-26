<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PurgeDeletedRecords extends Command
{
    protected $signature   = 'schoolflow:purge-deleted';
    protected $description = 'Permanently delete soft-deleted students, staff, and users older than 90 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(90);

        foreach (Tenant::all() as $tenant) {
            $tenant->run(function () use ($cutoff, $tenant): void {
                try {
                    $students = DB::table('students')
                        ->whereNotNull('deleted_at')
                        ->where('deleted_at', '<', $cutoff)
                        ->pluck('id');

                    if ($students->isNotEmpty()) {
                        DB::table('students')->whereIn('id', $students)->delete();
                    }

                    $staff = DB::table('staff')
                        ->whereNotNull('deleted_at')
                        ->where('deleted_at', '<', $cutoff)
                        ->pluck('id');

                    if ($staff->isNotEmpty()) {
                        DB::table('staff')->whereIn('id', $staff)->delete();
                    }

                    $users = DB::table('users')
                        ->whereNotNull('deleted_at')
                        ->where('deleted_at', '<', $cutoff)
                        ->pluck('id');

                    if ($users->isNotEmpty()) {
                        DB::table('users')->whereIn('id', $users)->delete();
                    }

                    $this->info("Tenant {$tenant->id}: purged {$students->count()} students, {$staff->count()} staff, {$users->count()} users.");
                } catch (\Throwable $e) {
                    Log::error('[PurgeDeletedRecords] tenant=' . $tenant->id . ' ' . $e->getMessage());
                    $this->error("Tenant {$tenant->id}: failed — " . $e->getMessage());
                }
            });
        }

        return self::SUCCESS;
    }
}
