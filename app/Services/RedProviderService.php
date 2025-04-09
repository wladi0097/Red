<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RedProviderService
{
    protected string $baseUrl;
    protected string $certPath;

    public function __construct()
    {
        $this->baseUrl = env('RED_PROVIDER_URL');
        $this->certPath = base_path(env('RED_PROVIDER_CERT_PATH'));
    }
    protected function getToken(): string
    {
        return Cache::remember('redprovider_token', 55, function () {
            $response = Http::withOptions([
                'verify' => $this->certPath,
            ])->post("$this->baseUrl/token", [
                'client_id' => env('RED_PROVIDER_CLIENT_ID'),
                'client_secret' => env('RED_PROVIDER_CLIENT_SECRET'),
            ]);

            return $response['access_token'];
        });
    }

    public function createOrder(string $type): array
    {
        if (env('RED_PROVIDER_USE_MOCK')) {
            return [
                'id' => uniqid('mock_', true),
                'type' => $type,
                'status' => 'ordered',
            ];
        }

        return Http::withToken($this->getToken())
            ->withOptions(['verify' => $this->certPath])
            ->post("$this->baseUrl/orders", [
                'type' => $type,
            ])->json();
    }

    public function getOrderStatus(string $id): ?string
    {
        if (env('RED_PROVIDER_USE_MOCK')) {
            return 'completed';
        }

        return Http::withToken($this->getToken())
            ->withOptions(['verify' => $this->certPath])
            ->get("$this->baseUrl/order/$id")
            ->json('status');
    }

    public function deleteOrder(string $id): void
    {
        if (env('RED_PROVIDER_USE_MOCK')) {
            return;
        }

        Http::withToken($this->getToken())
            ->withOptions(['verify' => $this->certPath])
            ->delete("$this->baseUrl/order/$id");
    }
}
