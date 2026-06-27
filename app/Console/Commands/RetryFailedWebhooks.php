<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\WebhookDelivery;
use App\Services\WebhookDeliveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class RetryFailedWebhooks extends Command
{
    protected $signature = 'schoolflow:retry-failed-webhooks';
    protected $description = 'Retry failed webhook deliveries that are scheduled for re-attempt';

    public function handle(WebhookDeliveryService $service): int
    {
        $tenants = Tenant::all();
        $retried = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenant->run(function () use ($service, &$retried): void {
                    $deliveries = WebhookDelivery::where('next_retry_at', '<=', now())
                        ->where('attempt_count', '<', 3)
                        ->with('webhook')
                        ->get();

                    foreach ($deliveries as $delivery) {
                        $service->retry($delivery);
                        $retried++;
                    }
                });
            } catch (\Throwable $e) {
                Log::error('[RetryFailedWebhooks] tenant=' . $tenant->id . ' — ' . $e->getMessage());
            }
        }

        $this->info("Retried {$retried} webhook delivery(ies).");
        return self::SUCCESS;
    }
}
