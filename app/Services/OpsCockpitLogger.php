<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OpsCockpitLogger
{
    protected $credentialsPath;
    protected $topicName;
    protected $projectId;
    protected $enabled;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/ops-cockpit-credentials.json');
        $this->projectId = config('services.ops_cockpit.project_id', 'absolute-water-387410');
        $this->topicName = config('services.ops_cockpit.topic', 'ops-cockpit-events');
        $this->enabled = config('services.ops_cockpit.enabled', false);
    }

    /**
     * Publish an event to GCP Pub/Sub.
     */
    public function publish(string $eventType, array $payload): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!file_exists($this->credentialsPath)) {
            Log::warning('OpsCockpit: credentials file not found at ' . $this->credentialsPath);
            return;
        }

        try {
            $message = [
                'event_type' => $eventType,
                'tenant_id' => config('app.url', 'asttrolok.com'),
                'timestamp' => now()->toIso8601String(),
                'payload' => $payload,
            ];

            $this->publishToPubSub($message);
        } catch (\Throwable $e) {
            Log::warning('OpsCockpit: failed to publish event', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log an HTTP request event.
     */
    public function logRequest(array $data): void
    {
        $this->publish('HTTP_REQUEST', $data);
    }

    /**
     * Log an application error event.
     */
    public function logError(array $data): void
    {
        $this->publish('APP_ERROR', $data);
    }

    /**
     * Publish message to GCP Pub/Sub using REST API.
     */
    protected function publishToPubSub(array $message): void
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return;
        }

        $url = sprintf(
            'https://pubsub.googleapis.com/v1/projects/%s/topics/%s:publish',
            $this->projectId,
            $this->topicName
        );

        $data = [
            'messages' => [
                [
                    'data' => base64_encode(json_encode($message)),
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::warning('OpsCockpit: Pub/Sub publish failed', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }
    }

    /**
     * Get OAuth2 access token from GCP service account credentials.
     */
    protected function getAccessToken(): ?string
    {
        static $cachedToken = null;
        static $tokenExpiry = null;

        if ($cachedToken && $tokenExpiry && time() < $tokenExpiry) {
            return $cachedToken;
        }

        try {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);

            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $now = time();
            $claimSet = base64_encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/pubsub',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signatureInput = $header . '.' . $claimSet;
            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = $signatureInput . '.' . base64_encode($signature);

            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);

            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (isset($response['access_token'])) {
                $cachedToken = $response['access_token'];
                $tokenExpiry = $now + ($response['expires_in'] ?? 3500) - 60;
                return $cachedToken;
            }

            Log::warning('OpsCockpit: failed to get access token', ['response' => $response]);
            return null;
        } catch (\Throwable $e) {
            Log::warning('OpsCockpit: access token error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
