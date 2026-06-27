<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Central\Broadcast;
use App\Models\Central\BroadcastNotification;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private readonly string $broadcastId) {}

    public function handle(): void
    {
        $broadcast = Broadcast::find($this->broadcastId);

        if ($broadcast === null) {
            return;
        }

        if ($broadcast->sent_at !== null) {
            return;
        }

        $tenants = Tenant::where('status', 'active')->pluck('id');

        $rows = $tenants->map(fn ($tenantId) => [
            'id'           => (string) \Illuminate\Support\Str::uuid(),
            'broadcast_id' => $this->broadcastId,
            'tenant_id'    => $tenantId,
            'dismissed_at' => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ])->all();

        foreach (array_chunk($rows, 100) as $chunk) {
            BroadcastNotification::insertOrIgnore($chunk);
        }

        $broadcast->update(['sent_at' => now()]);

        Log::info("[SendBroadcastJob] Broadcast {$this->broadcastId} delivered to {$tenants->count()} tenants.");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[SendBroadcastJob] Failed for broadcast {$this->broadcastId}: {$e->getMessage()}");
    }
}
