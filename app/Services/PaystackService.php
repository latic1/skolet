<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class PaystackService
{
    private readonly Client $client;
    private readonly string $secretKey;

    public function __construct(?Client $client = null)
    {
        $this->secretKey = (string) config('paystack.secret_key', '');

        $this->client = $client ?? new Client([
            'base_uri' => 'https://api.paystack.co/',
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    /**
     * Initialize a Paystack transaction.
     *
     * Amount should be in the major currency unit (e.g. GHS 5.00, not pesewas).
     * This method converts to the smallest unit internally.
     *
     * @param  array<string, mixed>  $metadata
     * @return array{success: bool, authorization_url: ?string, reference: ?string, error: ?string}
     */
    public function initializeTransaction(
        string $email,
        float $amount,
        string $callbackUrl,
        array $metadata = [],
        ?string $reference = null
    ): array {
        $reference ??= 'SF-' . strtoupper(Str::random(12));

        // Paystack expects amount in smallest currency unit (100 pesewas = 1 GHS)
        $amountInSmallestUnit = (int) round($amount * 100);

        try {
            $response = $this->client->post('transaction/initialize', [
                'json' => [
                    'email'        => $email,
                    'amount'       => $amountInSmallestUnit,
                    'reference'    => $reference,
                    'callback_url' => $callbackUrl,
                    'currency'     => \App\Models\Tenant\SchoolProfile::first()?->currency_code ?? config('paystack.currency', 'GHS'),
                    'metadata'     => $metadata,
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (! ($body['status'] ?? false)) {
                Log::error('[PaystackService::initializeTransaction] ' . ($body['message'] ?? 'Unknown error'));

                return ['success' => false, 'authorization_url' => null, 'reference' => null, 'error' => $body['message'] ?? 'Payment initialization failed.'];
            }

            return [
                'success'           => true,
                'authorization_url' => $body['data']['authorization_url'],
                'reference'         => $body['data']['reference'],
                'error'             => null,
            ];
        } catch (GuzzleException $e) {
            Log::error('[PaystackService::initializeTransaction] ' . $e->getMessage());

            return ['success' => false, 'authorization_url' => null, 'reference' => null, 'error' => 'Could not reach payment provider. Please try again.'];
        }
    }

    /**
     * Verify a Paystack transaction with the Paystack API.
     * Always call this before recording any payment — never trust the webhook payload alone.
     *
     * @return array{success: bool, status: string, amount: float, metadata: array<string, mixed>, error: ?string}
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = $this->client->get('transaction/verify/' . urlencode($reference));
            $body     = json_decode((string) $response->getBody(), true);

            if (! ($body['status'] ?? false)) {
                return ['success' => false, 'status' => 'failed', 'amount' => 0.0, 'metadata' => [], 'error' => $body['message'] ?? 'Verification failed.'];
            }

            $data     = $body['data'];
            $txStatus = $data['status'] ?? 'failed';
            // Convert from smallest unit back to major unit
            $amountPaid = ($data['amount'] ?? 0) / 100;

            return [
                'success'  => $txStatus === 'success',
                'status'   => $txStatus,
                'amount'   => (float) $amountPaid,
                'metadata' => $data['metadata'] ?? [],
                'error'    => $txStatus !== 'success' ? 'Transaction status: ' . $txStatus . '.' : null,
            ];
        } catch (GuzzleException $e) {
            Log::error('[PaystackService::verifyTransaction] ' . $e->getMessage());

            return ['success' => false, 'status' => 'failed', 'amount' => 0.0, 'metadata' => [], 'error' => 'Could not verify payment. Please contact support.'];
        }
    }

    /**
     * Verify a Paystack webhook HMAC-SHA512 signature.
     * Must be called before trusting any webhook payload.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature): bool
    {
        if (! $this->secretKey || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha512', $rawPayload, $this->secretKey);

        return hash_equals($expected, $signature);
    }
}
