<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SmsService
{
    private string $apiKey;
    private string $senderId;
    private string $endpoint = 'https://api.smsonlinegh.com/v5/message/sms/send';

    public function __construct()
    {
        $this->apiKey   = config('services.smsonlinegh.key', '');
        $this->senderId = config('services.smsonlinegh.sender_id', 'Skolet');
    }

    public function send(string $to, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('[SmsService] No API key configured — SMS not sent.');
            return false;
        }

        $number = $this->normalizeGhanaNumber($to);

        if ($number === null) {
            Log::warning('[SmsService] Invalid Ghana phone number: ' . $to);
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'key ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post($this->endpoint, [
                    'text'         => $message,
                    'type'         => 0,
                    'sender'       => $this->senderId,
                    'destinations' => [['number' => $number]],
                ]);

            $body = $response->json();
            $handshakeId = data_get($body, 'handshake.id');

            if ($handshakeId === 0) {
                return true;
            }

            Log::warning('[SmsService] API returned non-OK handshake', [
                'handshake' => data_get($body, 'handshake'),
                'to'        => $number,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('[SmsService] Request failed: ' . $e->getMessage());
            return false;
        }
    }

    // Normalises 0XXXXXXXXX or +233XXXXXXXXX → 233XXXXXXXXX
    private function normalizeGhanaNumber(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '233') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '233' . substr($digits, 1);
        }

        return null;
    }
}
