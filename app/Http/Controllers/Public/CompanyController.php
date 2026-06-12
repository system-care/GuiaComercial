<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\GeoDistance;
use App\Support\PublicSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $tenant = Tenant::query()
            ->with(['settings', 'niche'])
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $services = Service::forTenant($tenant->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $professionals = Professional::forTenant($tenant->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $settings        = $tenant->settings?->settings ?? [];
        $distanceLabel   = $this->distanceLabel($request, $tenant);
        $profileData     = $this->profileData($tenant, $settings);
        $rawDescription  = trim((string) ($settings['description'] ?? ''));
        $metaDescription = $rawDescription !== ''
            ? Str::limit($rawDescription, 155)
            : 'Conheça ' . $tenant->name . ', veja serviços disponíveis e agende online pelo Guia Comercial.';

        return view('public.companies.show', [
            'tenant'          => $tenant,
            'settings'        => $settings,
            'services'        => $services,
            'professionals'   => $professionals,
            'profile'         => $profileData,
            'distanceLabel'   => $distanceLabel,
            'metaDescription' => $metaDescription,
        ]);
    }

    public function booking(string $slug): RedirectResponse
    {
        $tenant = Tenant::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        return redirect()->route('booking.show', $tenant->slug);
    }

    private function profileData(Tenant $tenant, array $settings): array
    {
        $logoPath = Arr::get($settings, 'logo_path');
        $bannerPath = Arr::get($settings, 'banner_path');
        $whatsapp = Arr::get($settings, 'whatsapp') ?: Arr::get($settings, 'whatsapp_phone') ?: $tenant->phone;
        $description = trim((string) Arr::get($settings, 'description', ''));
        $publicAddress = $this->publicAddress($tenant, $settings);
        $workingHours = $tenant->settings?->working_hours ?: Arr::get($settings, 'working_hours', []);

        return [
            'name' => $tenant->name,
            'category' => $tenant->niche?->name ?? 'Serviços',
            'description' => $description !== '' ? $description : 'Este perfil ainda está em construção. Em breve a empresa poderá publicar mais detalhes sobre sua atuação, estrutura e diferenciais.',
            'logo_url' => $logoPath ? asset('storage/' . $logoPath) : null,
            'banner_url' => $bannerPath ? asset('storage/' . $bannerPath) : null,
            'initials' => $this->initials($tenant->name),
            'location' => $this->locationLabel($tenant, $settings),
            'public_address' => $publicAddress,
            'phone' => $this->publicPhone($tenant, $settings),
            'whatsapp_url' => $this->whatsappUrl($whatsapp),
            'booking_url' => route('booking.show', $tenant->slug),
            'booking_redirect_url' => route('public.companies.booking', $tenant->slug),
            'has_online_booking' => (bool) Arr::get($settings, 'allow_online_booking', true),
            'show_prices'        => (bool) Arr::get($settings, 'show_prices', true),
            'show_team'          => (bool) Arr::get($settings, 'show_team', true),
            'working_hours' => is_array($workingHours) ? $workingHours : [],
            'attendance_modes' => $this->attendanceModes($settings),
            'cancellation_policy' => Arr::get($settings, 'cancellation_policy'),
        ];
    }

    private function locationLabel(Tenant $tenant, array $settings): string
    {
        $neighborhood = Arr::get($settings, 'neighborhood') ?: Arr::get($settings, 'bairro');

        if ($tenant->city && $neighborhood) {
            return "{$neighborhood}, {$tenant->city}";
        }

        return $tenant->city ?: ($neighborhood ?: 'Localização não informada');
    }

    private function publicAddress(Tenant $tenant, array $settings): ?string
    {
        $showAddress = Arr::get($settings, 'show_address', Arr::get($settings, 'show_exact_address', true));

        if ($showAddress === false) {
            return null;
        }

        $address = trim((string) Arr::get($settings, 'address', ''));

        return $address !== '' ? $address : null;
    }

    private function publicPhone(Tenant $tenant, array $settings): ?string
    {
        $phone = trim((string) (Arr::get($settings, 'public_phone') ?: Arr::get($settings, 'phone') ?: $tenant->phone));

        return $phone !== '' ? $phone : null;
    }

    private function whatsappUrl(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (! $digits) {
            return null;
        }

        if (strlen($digits) <= 11) {
            $digits = '55' . ltrim($digits, '0');
        }

        return 'https://wa.me/' . $digits;
    }

    private function attendanceModes(array $settings): array
    {
        $modes = Arr::get($settings, 'attendance_modes', Arr::get($settings, 'service_modes', []));

        if (is_string($modes)) {
            return array_values(array_filter(array_map('trim', explode(',', $modes))));
        }

        if (is_array($modes)) {
            return array_values(array_filter($modes, fn ($mode) => is_string($mode) && trim($mode) !== ''));
        }

        return [];
    }

    private function distanceLabel(Request $request, Tenant $tenant): ?string
    {
        [$userLat, $userLng] = PublicSearch::geoParams($request);

        if ($userLat === null) {
            return null;
        }

        $lat = $tenant->settings?->latitude;
        $lng = $tenant->settings?->longitude;

        if ($lat === null || $lng === null) {
            return null;
        }

        $km = GeoDistance::haversineKm($userLat, $userLng, (float) $lat, (float) $lng);

        return 'Aproximadamente ' . GeoDistance::formatKm($km);
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('') ?: 'GC';
    }
}
