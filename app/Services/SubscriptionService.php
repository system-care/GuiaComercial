<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orquestra o ciclo de vida das assinaturas do Agendamento SaaS.
 *
 * Modelo de billing: assinatura mensal recorrente via ASAAS.
 *  - Trial gratuito por N dias → tenant escolhe plano → ASAAS gera link de pagamento
 *  - ASAAS cobra mensalmente → webhook confirma → assinatura renovada
 *  - Pagamento vencido → overdue → grace period de 3 dias → acesso bloqueado
 */
class SubscriptionService
{
    public function __construct(
        private readonly AsaasService $asaas,
    ) {}

    // ── Trial ─────────────────────────────────────────────────────────────────

    /**
     * Cria uma assinatura trial gratuita para um tenant recém-cadastrado.
     */
    public function createTrial(Tenant $tenant): Subscription
    {
        $plan = Plan::where('slug', 'free_trial')->first()
            ?? Plan::where('price', 0)->orderBy('id')->first();

        if (! $plan) {
            throw new \RuntimeException('Nenhum plano trial configurado.');
        }

        return Subscription::create([
            'tenant_id'     => $tenant->id,
            'plan_id'       => $plan->id,
            'status'        => Subscription::STATUS_TRIAL,
            'trial_ends_at' => Carbon::now()->addDays($plan->trial_days ?: 14),
        ]);
    }

    // ── Assinatura paga ───────────────────────────────────────────────────────

    /**
     * Assina um plano pago: cria customer no ASAAS (se necessário), cria assinatura
     * recorrente e retorna a URL de pagamento da primeira cobrança.
     *
     * @return array{subscription: Subscription, invoice_url: string|null, payment: Payment}
     */
    public function subscribe(Tenant $tenant, Plan $plan, string $billingType = 'PIX'): array
    {
        return DB::transaction(function () use ($tenant, $plan, $billingType) {
            // 1. Garante customer no ASAAS
            $asaasCustomerId = $this->ensureAsaasCustomer($tenant);

            // 2. Cancela assinatura ASAAS anterior (se existir)
            $existing = $tenant->activeSubscription();
            if ($existing?->asaas_subscription_id) {
                $this->asaas->cancelSubscription($existing->asaas_subscription_id);
            }

            // 3. Cria assinatura recorrente no ASAAS
            $nextDue  = Carbon::now()->addDay()->format('Y-m-d');
            $asaasSub = $this->asaas->createSubscription([
                'customer'             => $asaasCustomerId,
                'billingType'          => $billingType,
                'value'                => (float) $plan->price,
                'nextDueDate'          => $nextDue,
                'cycle'                => 'MONTHLY',
                'description'          => "Assinatura {$plan->name} — {$tenant->name}",
                'notificationDisabled' => true,
            ]);

            if (isset($asaasSub['error']) || empty($asaasSub['id'])) {
                throw new \RuntimeException('Erro ao criar assinatura no ASAAS: ' . ($asaasSub['error'] ?? json_encode($asaasSub)));
            }

            // 4. Atualiza ou cria subscription local
            if ($existing && ! in_array($existing->status, [Subscription::STATUS_CANCELED])) {
                $existing->update([
                    'plan_id'               => $plan->id,
                    'asaas_subscription_id' => $asaasSub['id'],
                    'status'                => Subscription::STATUS_PENDING_PAYMENT,
                    'billing_type'          => $billingType,
                    'trial_ends_at'         => null,
                    'current_period_end'    => null,
                    'overdue_since'         => null,
                ]);
                $subscription = $existing->fresh();
            } else {
                $subscription = Subscription::create([
                    'tenant_id'             => $tenant->id,
                    'plan_id'               => $plan->id,
                    'asaas_subscription_id' => $asaasSub['id'],
                    'status'                => Subscription::STATUS_PENDING_PAYMENT,
                    'billing_type'          => $billingType,
                ]);
            }

            // 5. Busca pagamento inicial gerado pelo ASAAS
            $asaasPayments  = $this->asaas->getSubscriptionPayments($asaasSub['id']);
            $firstPayment   = $asaasPayments[0] ?? null;

            $payment = Payment::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription->id,
                'asaas_payment_id' => $firstPayment['id'] ?? null,
                'value'           => $plan->price,
                'status'          => 'PENDING',
                'billing_type'    => $billingType,
                'due_date'        => $nextDue,
                'invoice_url'     => $firstPayment['invoiceUrl'] ?? null,
            ]);

            Log::info('SubscriptionService::subscribe', [
                'tenant_id'             => $tenant->id,
                'plan_id'               => $plan->id,
                'asaas_subscription_id' => $asaasSub['id'],
            ]);

            return [
                'subscription' => $subscription,
                'invoice_url'  => $payment->invoice_url,
                'payment'      => $payment,
            ];
        });
    }

    // ── Troca de plano ────────────────────────────────────────────────────────

    /**
     * Troca o plano de uma assinatura ativa no ASAAS.
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): Subscription
    {
        if ($subscription->asaas_subscription_id) {
            $this->asaas->updateSubscription($subscription->asaas_subscription_id, [
                'value'       => (float) $newPlan->price,
                'description' => "Assinatura {$newPlan->name} — " . ($subscription->tenant->name ?? ''),
            ]);
        }

        $subscription->update(['plan_id' => $newPlan->id]);

        Log::info('SubscriptionService::changePlan', [
            'subscription_id' => $subscription->id,
            'new_plan_id'     => $newPlan->id,
        ]);

        return $subscription->fresh();
    }

    // ── Cancelamento ──────────────────────────────────────────────────────────

    /**
     * Cancela a assinatura localmente e no ASAAS.
     */
    public function cancel(Subscription $subscription): void
    {
        if ($subscription->asaas_subscription_id) {
            $this->asaas->cancelSubscription($subscription->asaas_subscription_id);
        }

        $subscription->update(['status' => Subscription::STATUS_CANCELED]);

        Log::info('SubscriptionService::cancel', ['subscription_id' => $subscription->id]);
    }

    // ── Webhook handlers ──────────────────────────────────────────────────────

    public function handlePaymentReceived(array $payload): void
    {
        $this->upsertPayment($payload, 'RECEIVED', now());
    }

    /**
     * Pagamento confirmado → ativa assinatura e renova período.
     */
    public function handlePaymentConfirmed(array $payload): void
    {
        $payment = $this->upsertPayment($payload, 'CONFIRMED', now());

        if (! $payment) {
            return;
        }

        $subscription = $payment->subscription;
        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status'             => Subscription::STATUS_ACTIVE,
            'overdue_since'      => null,
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        Log::info('SubscriptionService::handlePaymentConfirmed', [
            'subscription_id' => $subscription->id,
            'payment_id'      => $payment->id,
        ]);
    }

    /**
     * Pagamento vencido → marca overdue com grace period.
     */
    public function handlePaymentOverdue(array $payload): void
    {
        $payment = $this->upsertPayment($payload, 'OVERDUE');

        if (! $payment?->subscription) {
            return;
        }

        $subscription = $payment->subscription;

        if ($subscription->status !== Subscription::STATUS_OVERDUE) {
            $subscription->update([
                'status'        => Subscription::STATUS_OVERDUE,
                'overdue_since' => Carbon::now(),
            ]);
        }

        Log::info('SubscriptionService::handlePaymentOverdue', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Assinatura deletada no ASAAS → cancela localmente.
     */
    public function handleSubscriptionDeleted(array $payload): void
    {
        $asaasSubId = $payload['id'] ?? null;
        if (! $asaasSubId) {
            return;
        }

        Subscription::where('asaas_subscription_id', $asaasSubId)
            ->whereNotIn('status', [Subscription::STATUS_CANCELED])
            ->update(['status' => Subscription::STATUS_CANCELED]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Garante que o tenant tem um customer_id válido no ASAAS.
     * Se já existir e estiver ativo, sincroniza os dados. Se não, cria novo.
     */
    private function ensureAsaasCustomer(Tenant $tenant): string
    {
        if ($tenant->asaas_customer_id) {
            $existing = $this->asaas->getCustomer($tenant->asaas_customer_id);
            if (! empty($existing['id'])) {
                return $existing['id'];
            }
        }

        $result = $this->asaas->createCustomer([
            'name'                 => $tenant->name,
            'email'                => $tenant->email ?? '',
            'phone'                => preg_replace('/\D/', '', $tenant->phone ?? ''),
            'notificationDisabled' => true,
        ]);

        if (empty($result['id'])) {
            throw new \RuntimeException('Erro ao criar customer no ASAAS: ' . json_encode($result));
        }

        $tenant->update(['asaas_customer_id' => $result['id']]);

        return $result['id'];
    }

    private function upsertPayment(array $payload, string $status, ?\DateTimeInterface $paidAt = null): ?Payment
    {
        $paymentData    = $payload['payment'] ?? $payload;
        $asaasPaymentId = $paymentData['id'] ?? null;

        if (! $asaasPaymentId) {
            return null;
        }

        $asaasSubId   = $paymentData['subscription'] ?? null;
        $subscription = $asaasSubId
            ? Subscription::where('asaas_subscription_id', $asaasSubId)->first()
            : null;

        if (! $subscription) {
            $existing     = Payment::where('asaas_payment_id', $asaasPaymentId)->first();
            $subscription = $existing?->subscription;
        }

        if (! $subscription) {
            Log::warning('SubscriptionService::upsertPayment: subscription não encontrada', [
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_sub_id'     => $asaasSubId,
            ]);
            return null;
        }

        return Payment::updateOrCreate(
            ['asaas_payment_id' => $asaasPaymentId],
            [
                'tenant_id'       => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'value'           => $paymentData['value'] ?? 0,
                'status'          => $status,
                'billing_type'    => $paymentData['billingType'] ?? 'UNDEFINED',
                'due_date'        => $paymentData['dueDate'] ?? null,
                'paid_at'         => $paidAt,
                'invoice_url'     => $paymentData['invoiceUrl'] ?? null,
            ]
        );
    }
}
