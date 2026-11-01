<?php

namespace App\Services\Mpesa;

use App\Exceptions\MpesaException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DarajaClient
{
    public function stkPush(
        string $phone,
        int $amount,
        string $accountReference,
        string $description,
        string $callbackUrl,
    ): array {
        $timestamp = now()->format('YmdHis');
        $shortcode = (string) config('mpesa.shortcode');

        try {
            $response = $this->client()
                ->withToken($this->accessToken())
                ->post('/mpesa/stkpush/v1/processrequest', [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $this->password($timestamp),
                    'Timestamp' => $timestamp,
                    'TransactionType' => config('mpesa.transaction_type'),
                    'Amount' => $amount,
                    'PartyA' => $phone,
                    'PartyB' => (string) config('mpesa.party_b'),
                    'PhoneNumber' => $phone,
                    'CallBackURL' => $callbackUrl,
                    'AccountReference' => $accountReference,
                    'TransactionDesc' => $description,
                ]);
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('M-Pesa STK push connection error', [
                'error' => $exception->getMessage(),
                'status' => $exception instanceof RequestException ? $exception->response?->status() : null,
            ]);

            throw new MpesaException(
                'Unable to initiate M-Pesa payment. Please try again.',
                $exception instanceof RequestException ? $exception->response?->status() : null,
            );
        }

        if ($response->failed()) {
            Log::warning('M-Pesa STK push HTTP error', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new MpesaException(status: $response->status());
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];

        if ((string) ($payload['ResponseCode'] ?? '') !== '0') {
            throw new MpesaException(
                (string) ($payload['CustomerMessage'] ?? $payload['ResponseDescription'] ?? 'M-Pesa rejected the payment request.'),
                status: $response->status(),
            );
        }

        if (empty($payload['CheckoutRequestID'])) {
            throw new MpesaException('M-Pesa did not return a checkout request.');
        }

        Log::info('M-Pesa STK push accepted', [
            'checkout_request_id' => $payload['CheckoutRequestID'],
            'amount_sent' => $amount,
            'phone' => $phone,
            'environment' => config('mpesa.environment'),
            'shortcode' => config('mpesa.shortcode'),
        ]);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function stkQuery(string $checkoutRequestId): array
    {
        $timestamp = now()->format('YmdHis');
        $shortcode = (string) config('mpesa.shortcode');

        try {
            $response = $this->client()
                ->withToken($this->accessToken())
                ->retry([200, 500], 0, $this->retryWhen(...))
                ->throw()
                ->post('/mpesa/stkpushquery/v1/query', [
                    'BusinessShortCode' => $shortcode,
                    'Password' => $this->password($timestamp),
                    'Timestamp' => $timestamp,
                    'CheckoutRequestID' => $checkoutRequestId,
                ]);
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('M-Pesa STK query connection error', [
                'error' => $exception->getMessage(),
                'status' => $exception instanceof RequestException ? $exception->response?->status() : null,
            ]);

            throw new MpesaException(
                'Unable to check M-Pesa payment status.',
                $exception instanceof RequestException ? $exception->response?->status() : null,
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];

        return $payload;
    }

    public function callbackUrl(): string
    {
        $configured = config('mpesa.callback_url');

        if (is_string($configured) && $configured !== '') {
            $this->assertHttpsCallback($configured);

            return $configured;
        }

        $secret = (string) config('mpesa.callback_secret');

        if ($secret === '') {
            throw new MpesaException('M-Pesa callback secret is not configured.');
        }

        $url = route('webhooks.mpesa.stk', ['secret' => $secret], absolute: true);

        $this->assertHttpsCallback($url);

        return $url;
    }

    public function isConfigured(): bool
    {
        $secret = (string) config('mpesa.callback_secret');

        return filled(config('mpesa.consumer_key'))
            && filled(config('mpesa.consumer_secret'))
            && filled(config('mpesa.shortcode'))
            && filled(config('mpesa.passkey'))
            && strlen($secret) >= 16;
    }

    private function accessToken(): string
    {
        $cacheKey = 'mpesa.oauth_token.'.(string) config('mpesa.environment');

        return Cache::remember($cacheKey, 3300, function (): string {
            try {
                $response = $this->client()
                    ->withBasicAuth(
                        (string) config('mpesa.consumer_key'),
                        (string) config('mpesa.consumer_secret'),
                    )
                    ->retry([200, 500], 0, $this->retryWhen(...))
                    ->throw()
                    ->get('/oauth/v1/generate', [
                        'grant_type' => 'client_credentials',
                    ]);
            } catch (ConnectionException|RequestException $exception) {
                Log::warning('M-Pesa OAuth connection error', [
                    'error' => $exception->getMessage(),
                    'status' => $exception instanceof RequestException ? $exception->response?->status() : null,
                ]);

                throw new MpesaException(
                    'Unable to authenticate with M-Pesa.',
                    $exception instanceof RequestException ? $exception->response?->status() : null,
                );
            }
            $token = $response->json('access_token');

            if (! is_string($token) || $token === '') {
                Log::warning('M-Pesa OAuth token missing', [
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                throw new MpesaException('Unable to authenticate with M-Pesa.', $response->status());
            }

            return $token;
        });
    }

    private function password(string $timestamp): string
    {
        return base64_encode((string) config('mpesa.shortcode').(string) config('mpesa.passkey').$timestamp);
    }

    private function client(): PendingRequest
    {
        // Point curl at the bundled CA certificate file so TLS verification
        // succeeds on cPanel servers where the system CA bundle may be
        // outdated or missing. The file ships with the project at
        // resources/ssl/cacert.pem (Mozilla/curl bundle).
        $caBundle = base_path('resources/ssl/cacert.pem');

        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->asJson()
            ->when(file_exists($caBundle), fn ($req) => $req->withOptions(['verify' => $caBundle]))
            ->connectTimeout((int) config('mpesa.timeouts.connect'))
            ->timeout((int) config('mpesa.timeouts.response'));
    }

    /**
     * Resolve the Daraja base URL from MPESA_ENVIRONMENT.
     *
     * Accepts "sandbox" or "production" (case-insensitive) and throws for
     * anything else, so a typo'd or invalid environment can't silently route
     * live STK traffic to the wrong host.
     */
    private function baseUrl(): string
    {
        return match (strtolower((string) config('mpesa.environment'))) {
            'production' => (string) config('mpesa.production_base_url'),
            'sandbox' => (string) config('mpesa.sandbox_base_url'),
            default => throw new MpesaException('MPESA_ENVIRONMENT must be set to "sandbox" or "production".'),
        };
    }

    private function retryWhen(Throwable $exception): bool
    {
        return $exception instanceof ConnectionException
            || ($exception instanceof RequestException
                && ($exception->response->serverError() || $exception->response->status() === 429));
    }

    private function assertHttpsCallback(string $url): void
    {
        if (! app()->isProduction()) {
            return;
        }

        if (! str_starts_with($url, 'https://')) {
            throw new MpesaException('M-Pesa callbacks require HTTPS. Set APP_URL to your public https origin.');
        }
    }
}
