<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant\Webhook;
use App\Models\Tenant\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class WebhookDeliveryService
{
    public function send(Webhook $webhook, string $event, array $payload): void
    {
        $body      = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $webhook->secret);

        $delivery = WebhookDelivery::create([
            'webhook_id'   => $webhook->id,
            'event'        => $event,
            'payload'      => $payload,
            'attempt_count'=> 0,
            'attempted_at' => now(),
        ]);

        $this->attempt($delivery, $webhook, $body, $signature);
    }

    public function retry(WebhookDelivery $delivery): void
    {
        $webhook = $delivery->webhook;
        if (! $webhook || ! $webhook->active) {
            $delivery->update(['next_retry_at' => null]);
            return;
        }

        $body      = json_encode($delivery->payload);
        $signature = hash_hmac('sha256', $body, $webhook->secret);

        $delivery->update(['attempted_at' => now(), 'next_retry_at' => null]);

        $this->attempt($delivery, $webhook, $body, $signature);
    }

    private function attempt(WebhookDelivery $delivery, Webhook $webhook, string $body, string $signature): void
    {
        try {
            $response = Http::timeout(10)
                ->withBody($body, 'application/json')
                ->withHeaders([
                    'X-Skolet-Signature' => 'sha256=' . $signature,
                    'X-Skolet-Event'     => $delivery->event,
                ])
                ->post($webhook->url);

            $newCount = $delivery->attempt_count + 1;
            $success  = $response->successful();

            $delivery->update([
                'response_status' => $response->status(),
                'response_body'   => substr($response->body(), 0, 1000),
                'attempt_count'   => $newCount,
                'next_retry_at'   => $success ? null : $this->nextRetryAt($newCount),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[WebhookDelivery] ' . $webhook->url . ' — ' . $e->getMessage());

            $newCount = $delivery->attempt_count + 1;

            $delivery->update([
                'response_body' => substr($e->getMessage(), 0, 1000),
                'attempt_count' => $newCount,
                'next_retry_at' => $this->nextRetryAt($newCount),
            ]);
        }
    }

    private function nextRetryAt(int $attemptCount): ?\DateTimeInterface
    {
        return match ($attemptCount) {
            1       => now()->addMinute(),
            2       => now()->addMinutes(5),
            3       => now()->addMinutes(30),
            default => null,
        };
    }
}
