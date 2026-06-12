<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Verifica assinaturas com grace period vencido e suspende o acesso.
 * Executar diariamente via scheduler.
 */
class CheckOverdueSubscriptions extends Command
{
    protected $signature   = 'subscriptions:check-overdue';
    protected $description = 'Verifica e processa assinaturas vencidas além do grace period';

    public function handle(): int
    {
        $expired = Subscription::where('status', Subscription::STATUS_OVERDUE)
            ->whereNotNull('overdue_since')
            ->get()
            ->filter(fn ($s) => $s->isGracePeriodExpired());

        foreach ($expired as $subscription) {
            $subscription->update(['status' => Subscription::STATUS_SUSPENDED]);

            Log::info('CheckOverdueSubscriptions: assinatura suspensa por inadimplência', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'overdue_since'   => $subscription->overdue_since,
            ]);
        }

        $count = $expired->count();
        $this->info("Processado: {$count} assinatura(s) suspensa(s).");

        return self::SUCCESS;
    }
}
