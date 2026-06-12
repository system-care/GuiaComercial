<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Recebe e processa webhooks do ASAAS.
 *
 * Segurança: valida o token via header `asaas-access-token`
 * (configurado em ASAAS_WEBHOOK_TOKEN no .env).
 *
 * Endpoint: POST /api/webhooks/asaas
 */
class AsaasWebhookController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        // Valida token de segurança
        $expectedToken = config('services.asaas.webhook_token');

        if ($expectedToken && $request->header('asaas-access-token') !== $expectedToken) {
            Log::warning('AsaasWebhookController: token inválido', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event   = $request->input('event');
        $payload = $request->all();

        Log::info('AsaasWebhookController: evento recebido', ['event' => $event]);

        // Persiste o webhook antes de processar — garante rastreabilidade mesmo se o handler crashar
        $logId = DB::table('webhook_logs')->insertGetId([
            'source'     => 'asaas',
            'event'      => $event ?? 'unknown',
            'payload'    => json_encode($payload),
            'status'     => 'received',
            'created_at' => now(),
        ]);

        try {
            match ($event) {
                'PAYMENT_RECEIVED'     => $this->subscriptionService->handlePaymentReceived(
                    $request->input('payment', $payload)
                ),
                'PAYMENT_CONFIRMED'    => $this->subscriptionService->handlePaymentConfirmed(
                    $request->input('payment', $payload)
                ),
                'PAYMENT_OVERDUE'      => $this->subscriptionService->handlePaymentOverdue(
                    $request->input('payment', $payload)
                ),
                'SUBSCRIPTION_DELETED' => $this->subscriptionService->handleSubscriptionDeleted(
                    $request->input('subscription', $payload)
                ),
                default => Log::info("AsaasWebhookController: evento ignorado [{$event}]"),
            };

            DB::table('webhook_logs')->where('id', $logId)->update([
                'status'       => 'processed',
                'processed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AsaasWebhookController: erro ao processar evento', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            DB::table('webhook_logs')->where('id', $logId)->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Retorna 200 para evitar reenvio infinito do ASAAS
            return response()->json(['message' => 'Error logged'], 200);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
