<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Central\Tenant;
use App\Models\Tenant\Announcement;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class SendAnnouncementNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $announcementId,
        private readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) {
            return;
        }

        $tenant->run(function (): void {
            try {
                $announcement = Announcement::find($this->announcementId);
                if (!$announcement) {
                    return;
                }

                $userIds = $this->resolveTargetUserIds($announcement);
                if (empty($userIds)) {
                    return;
                }

                $now  = now();
                $rows = [];
                foreach ($userIds as $userId) {
                    $rows[] = [
                        'id'              => (string) Str::uuid(),
                        'user_id'         => $userId,
                        'announcement_id' => $announcement->id,
                        'type'            => 'announcement',
                        'message'         => $announcement->title,
                        'data'            => json_encode(['announcement_id' => $announcement->id]),
                        'read_at'         => null,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }

                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table('notifications')->insert($chunk);
                }
            } catch (\Throwable $e) {
                Log::error('[SendAnnouncementNotifications] ' . $e->getMessage(), [
                    'announcement_id' => $this->announcementId,
                    'tenant_id'       => $this->tenantId,
                ]);
            }
        });
    }

    /** @return array<int, string> */
    private function resolveTargetUserIds(Announcement $announcement): array
    {
        $audienceIds = is_array($announcement->audience_ids) ? $announcement->audience_ids : [];

        return match ($announcement->audience_type) {
            'all_students' => User::role('student')->pluck('id')->toArray(),
            'all_parents'  => User::role('parent')->pluck('id')->toArray(),
            'class'        => $this->getUsersInClasses($audienceIds),
            'role'         => empty($audienceIds)
                ? []
                : User::role($audienceIds)->pluck('id')->toArray(),
            default        => User::pluck('id')->toArray(),
        };
    }

    /** @param array<int, string> $classIds */
    private function getUsersInClasses(array $classIds): array
    {
        if (empty($classIds)) {
            return [];
        }

        return Student::whereIn('class_id', $classIds)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();
    }
}
