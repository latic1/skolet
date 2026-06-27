<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Central\Tenant;
use App\Models\Tenant\Webhook;
use App\Services\WebhookDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendWebhookPayload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $tenantId,
        private readonly string $event,
        private readonly array $payload,
    ) {}

    public function handle(WebhookDeliveryService $service): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (! $tenant) {
            return;
        }

        $event   = $this->event;
        $payload = $this->payload;

        $tenant->run(function () use ($event, $payload, $service): void {
            try {
                $webhooks = Webhook::where('active', true)
                    ->whereJsonContains('events', $event)
                    ->get();

                foreach ($webhooks as $webhook) {
                    $service->send($webhook, $event, $payload);
                }
            } catch (\Throwable $e) {
                Log::error('[SendWebhookPayload] event=' . $event . ' tenant=' . $this->tenantId . ' — ' . $e->getMessage());
            }
        });
    }
}
