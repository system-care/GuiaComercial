<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wrapper para a API ASAAS v3.
 *
 * Configuração via .env:
 *   ASAAS_API_KEY=<token>
 *   ASAAS_ENVIRONMENT=sandbox   # ou production
 */
class AsaasService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $env = config('services.asaas.environment', 'sandbox');
        $this->apiKey = config('services.asaas.api_key', '');

        $this->baseUrl = $env === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
    }

    // ── Customers ─────────────────────────────────────────────────────────────

    /**
     * Cria um customer no ASAAS para o tenant.
     *
     * @param array{name: string, email: string, cpfCnpj?: string, phone?: string} $data
     */
    public function createCustomer(array $data): array
    {
        $response = $this->post('/customers', $data);
        Log::info('AsaasService::createCustomer', ['response' => $response]);
        return $response;
    }

    /**
     * Busca um customer pelo ID. Retorna array vazio se não existir ou removido.
     */
    public function getCustomer(string $customerId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get($this->baseUrl . "/customers/{$customerId}");

            $data = $response->json() ?? [];

            if (! empty($data['deleted']) || $response->status() === 404) {
                return [];
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('AsaasService::getCustomer failed', ['id' => $customerId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Atualiza dados de um customer existente no ASAAS.
     */
    public function updateCustomer(string $customerId, array $data): array
    {
        $response = $this->post("/customers/{$customerId}", $data, 'PUT');
        Log::info('AsaasService::updateCustomer', ['id' => $customerId]);
        return $response;
    }

    // ── Subscriptions ─────────────────────────────────────────────────────────

    /**
     * Cria uma assinatura recorrente no ASAAS.
     *
     * @param array{
     *   customer: string,
     *   billingType: string,
     *   value: float,
     *   nextDueDate: string,
     *   cycle: string,
     *   description?: string,
     *   notificationDisabled?: bool
     * } $data
     */
    public function createSubscription(array $data): array
    {
        $response = $this->post('/subscriptions', $data);
        Log::info('AsaasService::createSubscription', ['response' => $response]);
        return $response;
    }

    /**
     * Cancela uma assinatura no ASAAS.
     */
    public function cancelSubscription(string $asaasSubscriptionId): array
    {
        $response = $this->delete("/subscriptions/{$asaasSubscriptionId}");
        Log::info('AsaasService::cancelSubscription', ['id' => $asaasSubscriptionId]);
        return $response;
    }

    /**
     * Atualiza o valor de uma assinatura existente (troca de plano).
     */
    public function updateSubscription(string $asaasSubscriptionId, array $data): array
    {
        $response = $this->post("/subscriptions/{$asaasSubscriptionId}", $data, 'PUT');
        Log::info('AsaasService::updateSubscription', ['id' => $asaasSubscriptionId]);
        return $response;
    }

    // ── Payments ──────────────────────────────────────────────────────────────

    /**
     * Busca os pagamentos de uma assinatura no ASAAS.
     */
    public function getSubscriptionPayments(string $asaasSubscriptionId): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get($this->baseUrl . '/payments', ['subscription' => $asaasSubscriptionId]);

            return $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            Log::warning('AsaasService::getSubscriptionPayments failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    private function post(string $endpoint, array $data, string $method = 'POST'): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->send($method, $this->baseUrl . $endpoint, ['json' => $data]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("AsaasService::post {$method} {$endpoint} failed", ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    private function delete(string $endpoint): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->delete($this->baseUrl . $endpoint);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("AsaasService::delete {$endpoint} failed", ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    private function headers(): array
    {
        return [
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }
}
