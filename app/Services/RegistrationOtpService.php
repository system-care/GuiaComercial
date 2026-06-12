<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use App\Models\RegistrationOtp;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationOtpService
{
    public function __construct(
        private EvolutionService $evolution,
    ) {}

    /**
     * Gera código, persiste e envia para email + WhatsApp simultaneamente.
     * Se o WhatsApp falhar, email ainda é enviado (não bloqueia o cadastro).
     */
    public function createAndSend(array $data, ?string $ip = null): RegistrationOtp
    {
        // Invalida qualquer OTP anterior não verificado do mesmo email
        RegistrationOtp::where('email', $data['email'])
            ->whereNull('verified_at')
            ->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $nicheIds  = (array) ($data['business_niche_ids'] ?? []);
        $primaryId = $nicheIds[0] ?? ($data['business_niche_id'] ?? null);

        $otp = RegistrationOtp::create([
            'company_name'       => $data['company_name'],
            'gestor_name'        => $data['gestor_name'],
            'email'              => $data['email'],
            'phone'              => $this->normalizePhone($data['phone']),
            'password'           => bcrypt($data['password']),
            'business_niche_id'  => $primaryId,
            'business_niche_ids' => $nicheIds,
            'code'               => $code,
            'ip'                 => $ip,
            'expires_at'         => now()->addMinutes(10),
        ]);

        $this->sendEmail($otp);
        $this->sendWhatsApp($otp);

        return $otp;
    }

    /**
     * Verifica se o código informado é válido para o email.
     * Lança \RuntimeException em caso de falha.
     */
    public function verify(string $email, string $code): RegistrationOtp
    {
        $otp = RegistrationOtp::where('email', $email)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp) {
            throw new \RuntimeException('Nenhum código pendente para este e-mail.');
        }

        if ($otp->isExpired()) {
            throw new \RuntimeException('Código expirado. Clique em "Reenviar código" para gerar um novo.');
        }

        if (! hash_equals($otp->code, trim($code))) {
            throw new \RuntimeException('Código incorreto. Verifique e tente novamente.');
        }

        return $otp;
    }

    /**
     * Finaliza o cadastro: cria Tenant, TenantSetting e User (gestor).
     * Marca o OTP como verificado. Retorna o User autenticável.
     */
    public function complete(RegistrationOtp $otp): User
    {
        return DB::transaction(function () use ($otp) {
            $slug = $this->uniqueSlug($otp->company_name);

            $tenant = Tenant::create([
                'name'               => $otp->company_name,
                'slug'               => $slug,
                'email'              => $otp->email,
                'phone'              => $otp->phone,
                'business_niche_id'  => $otp->business_niche_id,
                'business_niche_ids' => $otp->business_niche_ids,
                'active'             => true,
                'plan'               => 'trial',
            ]);

            TenantSetting::create(['tenant_id' => $tenant->id]);

            $user = User::create([
                'name'      => $otp->gestor_name,
                'email'     => $otp->email,
                'password'  => $otp->password, // já está hashed pelo bcrypt do createAndSend
                'tenant_id' => $tenant->id,
                'role'      => User::ROLE_GESTOR,
            ]);

            $otp->update(['verified_at' => now()]);

            return $user;
        });
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function sendEmail(RegistrationOtp $otp): void
    {
        try {
            Mail::to($otp->email)->send(new OtpVerificationMail($otp));
        } catch (\Throwable $e) {
            Log::error('OTP: falha ao enviar e-mail', [
                'email' => $otp->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWhatsApp(RegistrationOtp $otp): void
    {
        $baseUrl  = config('services.evolution.base_url');
        $token    = config('services.evolution.token');
        $instance = config('services.evolution.admin_instance');

        if (! $baseUrl || ! $token || ! $instance) {
            Log::warning('OTP: Evolution API não configurada (admin_instance ausente), OTP apenas por e-mail.');
            return;
        }

        $text = implode("\n", [
            '🔐 *Código de verificação — Guia Comercial*',
            '',
            "Olá, {$otp->gestor_name}!",
            '',
            'Seu código de verificação para cadastrar a empresa',
            "*{$otp->company_name}* é:",
            '',
            "✅ *{$otp->code}*",
            '',
            'Válido por *10 minutos*.',
            'Não compartilhe este código com ninguém.',
        ]);

        try {
            $this->evolution->sendText($baseUrl, $token, $instance, $otp->phone, $text);
        } catch (\Throwable $e) {
            Log::error('OTP: falha ao enviar WhatsApp', [
                'phone' => $otp->phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        // Número brasileiro sem código do país: adiciona 55
        if (strlen($digits) <= 11) {
            $digits = '55' . ltrim($digits, '0');
        }

        return $digits;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
