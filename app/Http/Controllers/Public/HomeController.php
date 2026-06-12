<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NicheCategory;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\PublicSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        if (! Schema::hasTable('tenants')) {
            return view('public.home', [
                'categories'   => $this->categories(),
                'companies'    => [],
                'cityLinks'    => [],
                'detectedCity' => '',
            ]);
        }

        [$detectedCity, $detectedLat, $detectedLng] = $this->detectCityFromIp();

        $pool = Tenant::query()
            ->with(['settings', 'niche'])
            ->where('active', true)
            ->latest()
            ->limit(PublicSearch::PUBLIC_TENANT_LIMIT)
            ->get();

        if ($detectedCity !== '') {
            $locationTerms = PublicSearch::cityTerms($detectedCity);
            $local = $pool->filter(
                fn (Tenant $t) => PublicSearch::tenantLocationMatches($t, $locationTerms)
            )->values();
        } else {
            $local = collect();
        }

        if ($local->isNotEmpty()) {
            $tenants = $local->take(8);
        } elseif ($detectedLat !== null && $detectedLng !== null) {
            $tenants = PublicSearch::sortByDistance($pool, $detectedLat, $detectedLng)->take(8);
        } else {
            $tenants = $pool->take(8);
        }

        return view('public.home', [
            'categories'   => $this->categories(),
            'companies'    => $this->companyCards($tenants),
            'cityLinks'    => PublicSearch::cityLinks($this->cityTenants()),
            'detectedCity' => $detectedCity,
        ]);
    }

    private function detectCityFromIp(): array
    {
        $ip = request()->ip();

        if (in_array($ip, ['127.0.0.1', '::1'])
            || str_starts_with($ip, '192.168.')
            || str_starts_with($ip, '10.')
        ) {
            return ['', null, null];
        }

        return Cache::remember("ip_geo_{$ip}", 3600, function () use ($ip) {
            try {
                $data = Http::timeout(3)
                    ->get("https://ip-api.com/json/{$ip}?fields=city,lat,lon&lang=pt-BR")
                    ->json();

                return [
                    $data['city'] ?? '',
                    isset($data['lat']) ? (float) $data['lat'] : null,
                    isset($data['lon']) ? (float) $data['lon'] : null,
                ];
            } catch (\Throwable) {
                return ['', null, null];
            }
        });
    }

    private function cityTenants(): Collection
    {
        return Tenant::query()
            ->with('settings')
            ->where('active', true)
            ->latest()
            ->limit(PublicSearch::PUBLIC_TENANT_LIMIT)
            ->get();
    }

    private function categories(): array
    {
        if (! Schema::hasTable('niche_categories')) {
            return [];
        }

        return NicheCategory::active()
            ->get()
            ->map(fn (NicheCategory $cat) => [
                'name' => $cat->name,
                'slug' => $cat->key,
                'icon' => $this->categoryIcon($cat),
            ])
            ->all();
    }

    private function categoryIcon(NicheCategory $category): string
    {
        return match ($category->key) {
            'pets' => 'gc-pet',
            'profissional' => 'heroicon-o-academic-cap',
            'eventos' => 'heroicon-o-cake',
            default => $category->icon ?: 'heroicon-o-briefcase',
        };
    }

    private function companyCards(Collection $tenants): array
    {
        if ($tenants->isEmpty() || ! Schema::hasTable('services')) {
            return $tenants->map(fn (Tenant $tenant) => $this->cardData($tenant, collect()))->all();
        }

        $services = Service::withoutGlobalScope('tenant')
            ->whereIn('tenant_id', $tenants->pluck('id'))
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('tenant_id');

        return $tenants
            ->map(fn (Tenant $tenant) => $this->cardData($tenant, $services->get($tenant->id, collect())))
            ->all();
    }

    private function cardData(Tenant $tenant, Collection $services): array
    {
        $settings = $tenant->settings?->settings ?? [];
        $logoPath = $settings['logo_path'] ?? null;

        return [
            'name' => $tenant->name,
            'category' => $tenant->niche?->name ?? 'Serviços',
            'location' => $this->locationLabel($tenant, $settings),
            'description' => Str::limit($settings['description'] ?? 'Perfil em construção.', 120),
            'logo_url' => $logoPath ? asset('storage/' . $logoPath) : null,
            'accent' => $this->accentFor($tenant->id),
            'initials' => $this->initials($tenant->name),
            'services' => $services->pluck('name')->take(3)->values()->all(),
            'profile_url' => route('public.companies.show', $tenant->slug),
            'booking_url' => route('tenant.landing', $tenant->slug) . '#agendar',
            'has_online_booking' => (bool) ($settings['allow_online_booking'] ?? true),
        ];
    }

    private function locationLabel(Tenant $tenant, array $settings): string
    {
        $neighborhood = $settings['neighborhood'] ?? $settings['bairro'] ?? null;

        if ($tenant->city && $neighborhood) {
            return "{$neighborhood}, {$tenant->city}";
        }

        return $tenant->city ?: ($neighborhood ?: ($settings['address'] ?? 'Cidade não informada'));
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

    private function accentFor(int $id): string
    {
        $accents = [
            'bg-sky-100 text-sky-700',
            'bg-rose-100 text-rose-700',
            'bg-emerald-100 text-emerald-700',
            'bg-violet-100 text-violet-700',
            'bg-amber-100 text-amber-700',
            'bg-cyan-100 text-cyan-700',
        ];

        return $accents[$id % count($accents)];
    }
}
