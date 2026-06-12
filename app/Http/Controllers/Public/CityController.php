<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\GeoDistance;
use App\Support\PublicSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function show(Request $request, string $city): View
    {
        [$userLat, $userLng] = PublicSearch::geoParams($request);
        $hasGeo = $userLat !== null;

        $tenants          = $this->baseTenants();
        $servicesByTenant = $this->servicesByTenant($tenants);
        $cityName         = PublicSearch::cityName($city, $tenants);
        $cityTerms        = PublicSearch::cityTerms($city);

        $filteredTenants = $tenants
            ->filter(fn (Tenant $tenant) => PublicSearch::tenantLocationMatches($tenant, $cityTerms))
            ->values();

        if ($hasGeo) {
            $filteredTenants = PublicSearch::sortByDistance($filteredTenants, $userLat, $userLng);
        }

        $paginatedTenants = $this->paginateTenants($filteredTenants, $request);
        $citySlug         = PublicSearch::citySlug($cityName);

        return view('public.city', [
            'citySlug'      => $citySlug,
            'cityName'      => $cityName,
            'companies'     => $this->companyCards($paginatedTenants->getCollection()),
            'tenants'       => $paginatedTenants,
            'categoryLinks' => $this->categoryLinks($cityName, $hasGeo, $userLat, $userLng),
            'hasGeo'        => $hasGeo,
            'userLat'       => $userLat,
            'userLng'       => $userLng,
            'removeUrl'     => route('public.cities.show', $citySlug),
        ]);
    }

    public function category(Request $request, string $category, string $city): View
    {
        [$userLat, $userLng] = PublicSearch::geoParams($request);
        $hasGeo = $userLat !== null;

        $tenants          = $this->baseTenants();
        $servicesByTenant = $this->servicesByTenant($tenants);
        $cityName         = PublicSearch::cityName($city, $tenants);
        $cityTerms        = PublicSearch::cityTerms($city);
        $categoryTerms    = PublicSearch::categoryTerms($category);

        $filteredTenants = $tenants
            ->filter(function (Tenant $tenant) use ($servicesByTenant, $cityTerms, $categoryTerms) {
                $services = $servicesByTenant->get($tenant->id, collect());

                return PublicSearch::tenantLocationMatches($tenant, $cityTerms)
                    && PublicSearch::tenantMatches($tenant, $services, $categoryTerms);
            })
            ->values();

        if ($hasGeo) {
            $filteredTenants = PublicSearch::sortByDistance($filteredTenants, $userLat, $userLng);
        }

        $paginatedTenants = $this->paginateTenants($filteredTenants, $request);
        $categorySlug     = Str::slug($category);
        $citySlug         = PublicSearch::citySlug($cityName);

        return view('public.local-category', [
            'categorySlug' => $categorySlug,
            'categoryName' => PublicSearch::categoryName($category),
            'citySlug'     => $citySlug,
            'cityName'     => $cityName,
            'companies'    => $this->companyCards($paginatedTenants->getCollection()),
            'tenants'      => $paginatedTenants,
            'hasGeo'       => $hasGeo,
            'userLat'      => $userLat,
            'userLng'      => $userLng,
            'removeUrl'    => route('public.category.city', ['category' => $categorySlug, 'city' => $citySlug]),
        ]);
    }

    private function baseTenants(): Collection
    {
        if (! Schema::hasTable('tenants')) {
            return collect();
        }

        return Tenant::query()
            ->with(['settings', 'niche'])
            ->where('active', true)
            ->latest()
            ->limit(PublicSearch::PUBLIC_TENANT_LIMIT)
            ->get();
    }

    private function servicesByTenant(Collection $tenants): Collection
    {
        if ($tenants->isEmpty() || ! Schema::hasTable('services')) {
            return collect();
        }

        return Service::withoutGlobalScope('tenant')
            ->whereIn('tenant_id', $tenants->pluck('id'))
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->groupBy('tenant_id');
    }

    private function paginateTenants(Collection $tenants, Request $request): LengthAwarePaginator
    {
        $perPage = 12;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $items   = $tenants->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $tenants->count(),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ],
        );
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
        $settings      = $tenant->settings?->settings ?? [];
        $logoPath      = $settings['logo_path'] ?? null;
        $distanceLabel = isset($tenant->distance_km) && $tenant->distance_km !== null
            ? GeoDistance::formatKm((float) $tenant->distance_km)
            : null;

        return [
            'name'               => $tenant->name,
            'category'           => $tenant->niche?->name ?? 'Serviços',
            'location'           => $this->locationLabel($tenant, $settings),
            'description'        => Str::limit($settings['description'] ?? 'Perfil em construção.', 120),
            'logo_url'           => $logoPath ? asset('storage/' . $logoPath) : null,
            'accent'             => $this->accentFor($tenant->id),
            'initials'           => $this->initials($tenant->name),
            'services'           => $services->pluck('name')->take(3)->values()->all(),
            'profile_url'        => route('public.companies.show', $tenant->slug),
            'booking_url'        => route('booking.show', $tenant->slug),
            'has_online_booking' => (bool) ($settings['allow_online_booking'] ?? true),
            'distance_label'     => $distanceLabel,
        ];
    }

    private function categoryLinks(string $cityName, bool $hasGeo, ?float $userLat, ?float $userLng): array
    {
        return collect([
            ['name' => 'Clínicas',    'slug' => 'clinicas'],
            ['name' => 'Estética',    'slug' => 'estetica'],
            ['name' => 'Salões',      'slug' => 'saloes'],
            ['name' => 'Barbearias',  'slug' => 'barbearias'],
            ['name' => 'Lava Jato',   'slug' => 'lava-jato'],
            ['name' => 'Pet Shop',    'slug' => 'pet-shop'],
        ])
            ->map(function (array $category) use ($cityName, $hasGeo, $userLat, $userLng) {
                $url = route('public.category.city', [
                    'category' => $category['slug'],
                    'city'     => PublicSearch::citySlug($cityName),
                ]);

                if ($hasGeo) {
                    $url .= '?lat=' . $userLat . '&lng=' . $userLng;
                }

                return [...$category, 'url' => $url];
            })
            ->all();
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
