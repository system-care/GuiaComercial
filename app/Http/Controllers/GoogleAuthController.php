<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        abort_unless(SystemSetting::get('google_oauth_enabled', false), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        abort_unless(SystemSetting::get('google_oauth_enabled', false), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect($this->loginUrl())->withErrors(['email' => 'Erro ao autenticar com Google. Tente novamente.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            auth()->login($user, true);
            session()->regenerate();

            return redirect()->intended(route('filament.admin.pages.dashboard'));
        }

        // Novo usuário — precisa completar o cadastro
        session(['google_oauth' => [
            'id'    => $googleUser->getId(),
            'name'  => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
        ]]);

        return redirect(route('auth.google.complete'));
    }

    public function showComplete()
    {
        if (! session('google_oauth')) {
            return redirect($this->loginUrl());
        }

        return view('auth.google-complete', [
            'google'        => session('google_oauth'),
            'niches'        => $this->loadNiches(),
            'panelLoginUrl' => $this->loginUrl(),
        ]);
    }

    public function saveComplete(Request $request)
    {
        $google = session('google_oauth');

        if (! $google) {
            return redirect($this->loginUrl());
        }

        $request->validate([
            'company_name'        => 'required|string|max:100',
            'phone'               => 'required|string|max:30',
            'business_niche_ids'  => 'required|array|min:1',
            'business_niche_ids.*' => 'integer|exists:business_niches,id',
        ], [
            'company_name.required'       => 'Informe o nome da empresa.',
            'phone.required'              => 'Informe o WhatsApp.',
            'business_niche_ids.required' => 'Selecione pelo menos um segmento.',
        ]);

        if (User::where('email', $google['email'])->exists()) {
            return back()->withErrors(['email' => 'Este e-mail já está cadastrado. Faça login normalmente.']);
        }

        $user = DB::transaction(function () use ($google, $request) {
            $slug = $this->uniqueSlug($request->company_name);

            $normalizedPhone = preg_replace('/\D+/', '', $request->phone);
            if (strlen($normalizedPhone) <= 11) {
                $normalizedPhone = '55' . ltrim($normalizedPhone, '0');
            }

            $nicheIds  = array_map('intval', $request->business_niche_ids);
            $primaryId = $nicheIds[0] ?? null;

            $tenant = Tenant::create([
                'name'               => $request->company_name,
                'slug'               => $slug,
                'email'              => $google['email'],
                'phone'              => $normalizedPhone,
                'business_niche_id'  => $primaryId,
                'business_niche_ids' => $nicheIds,
                'active'             => true,
                'plan'               => 'trial',
            ]);

            TenantSetting::create(['tenant_id' => $tenant->id]);

            return User::create([
                'name'              => $google['name'],
                'email'             => $google['email'],
                'google_id'         => $google['id'],
                'password'          => bcrypt(Str::random(32)),
                'tenant_id'         => $tenant->id,
                'role'              => User::ROLE_GESTOR,
                'email_verified_at' => now(),
            ]);
        });

        session()->forget('google_oauth');
        auth()->login($user, true);
        session()->regenerate();

        return redirect(route('filament.admin.pages.empresa-settings'));
    }

    private function loginUrl(): string
    {
        $domain = config('app.panel_domain');
        return $domain ? "https://{$domain}/login" : route('filament.admin.auth.login');
    }

    private function loadNiches(): array
    {
        return \App\Models\NicheCategory::with(['niches' => fn ($q) => $q->where('active', true)->orderBy('name')])
            ->active()
            ->get()
            ->toArray();
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
