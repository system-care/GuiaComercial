<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EvolutionService
{
    // ── Mensagens ─────────────────────────────────────────────────────────────

    public function sendText(string $baseUrl, string $token, string $instance, string $to, string $text): array
    {
        $response = Http::withHeaders([
                'apikey'       => $token,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->timeout(15)
            ->post(rtrim($baseUrl, '/') . "/message/sendText/{$instance}", [
                'number'      => $to,
                'text'        => $text,
                'linkPreview' => false,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Evolution API error [' . $response->status() . ']: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    // ── Instância ─────────────────────────────────────────────────────────────

    /**
     * Cria instância. Quando phoneNumber é informado, o Baileys gera pairing code
     * em vez de (apenas) QR. Se já existir (409/403) retorna vazio sem lançar erro.
     */
    public function createInstance(string $baseUrl, string $token, string $instance, string $phoneNumber = ''): array
    {
        $instanceToken = hash('sha256', $instance . config('app.key', $instance));

        $payload = [
            'instanceName' => $instance,
            'token'        => $instanceToken,
            'integration'  => 'WHATSAPP-BAILEYS',
            'qrcode'       => true,
        ];

        if ($phoneNumber !== '') {
            $payload['phoneNumber'] = $phoneNumber;
        }

        $response = Http::withHeaders(['apikey' => $token, 'Content-Type' => 'application/json'])
            ->withoutVerifying()
            ->timeout(15)
            ->post(rtrim($baseUrl, '/') . '/instance/create', $payload);

        Log::info('Evolution createInstance', ['status' => $response->status(), 'instance' => $instance]);

        if ($response->status() === 409 || $response->status() === 403) {
            return [];
        }

        if (! $response->successful()) {
            throw new RuntimeException('Falha ao criar instância [' . $response->status() . ']: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Retorna o QR Code atual (data URI base64) da instância via /instance/connect.
     * Null se a instância não existir ou já estiver conectada.
     */
    public function fetchQrCode(string $baseUrl, string $token, string $instance): ?string
    {
        $response = Http::withHeaders(['apikey' => $token])
            ->withoutVerifying()
            ->timeout(10)
            ->get(rtrim($baseUrl, '/') . "/instance/connect/{$instance}");

        if ($response->failed()) {
            return null;
        }

        return $response->json()['base64'] ?? null;
    }

    /**
     * Aguarda e retorna o pairing code gerado pelo Baileys via /instance/connect.
     * O código fica disponível alguns segundos após createInstance com phoneNumber.
     * Tenta até $maxAttempts vezes com 2 s de intervalo.
     */
    public function getPairingCode(string $baseUrl, string $token, string $instance, int $maxAttempts = 6): ?string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            if ($i > 0) {
                sleep(2);
            }

            $response = Http::withHeaders(['apikey' => $token])
                ->withoutVerifying()
                ->timeout(15)
                ->get(rtrim($baseUrl, '/') . "/instance/connect/{$instance}");

            if ($response->failed()) {
                break;
            }

            $data = $response->json() ?? [];
            $code = $data['pairingCode'] ?? null;

            Log::info('Evolution getPairingCode', [
                'attempt'  => $i + 1,
                'instance' => $instance,
                'found'    => $code !== null,
            ]);

            if ($code !== null) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Retorna o estado da conexão: 'open' | 'connecting' | 'close' | 'unknown'
     */
    public function connectionStatus(string $baseUrl, string $token, string $instance): string
    {
        $response = Http::withHeaders(['apikey' => $token])
            ->withoutVerifying()
            ->timeout(10)
            ->get(rtrim($baseUrl, '/') . "/instance/connectionState/{$instance}");

        if ($response->failed()) {
            return 'unknown';
        }

        $data  = $response->json() ?? [];
        $state = $data['instance']['state']
            ?? $data['state']
            ?? $data['connectionStatus']
            ?? 'unknown';

        return match (strtolower((string) $state)) {
            'open'           => 'open',
            'connecting'     => 'connecting',
            'close', 'closed' => 'close',
            default          => 'unknown',
        };
    }

    /**
     * Desconecta a sessão WA (mantém a instância no servidor).
     */
    public function logout(string $baseUrl, string $token, string $instance): void
    {
        Http::withHeaders(['apikey' => $token])
            ->withoutVerifying()
            ->timeout(10)
            ->delete(rtrim($baseUrl, '/') . "/instance/logout/{$instance}");
    }

    /**
     * Deleta a instância completamente. Ignora 404 (não existe).
     */
    public function deleteInstance(string $baseUrl, string $token, string $instance): void
    {
        $response = Http::withHeaders(['apikey' => $token])
            ->withoutVerifying()
            ->timeout(10)
            ->delete(rtrim($baseUrl, '/') . "/instance/delete/{$instance}");

        Log::info('Evolution deleteInstance', ['status' => $response->status(), 'instance' => $instance]);
    }
}
