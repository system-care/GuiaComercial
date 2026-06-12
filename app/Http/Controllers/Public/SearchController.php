<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NicheCategory;
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

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $term     = trim((string) $request->query('q', ''));
        $location = trim((string) ($request->query('location', $request->query('local', ''))));
        $category = trim((string) ($request->query('category', $request->query('categoria', ''))));

        [$userLat, $userLng] = PublicSearch::geoParams($request);
        $hasGeo          = $userLat !== null;
        $hasFilters      = $term !== '' || $location !== '' || $category !== '' || $hasGeo;
        $metaDescription = $this->metaDescription($term, $location);
        $robots          = $hasFilters ? 'noindex,follow' : 'index,follow';

        $categories = Schema::hasTable('niche_categories')
            ? NicheCategory::active()->get()->map(fn ($c) => ['label' => $c->name, 'value' => $c->key])->all()
            : [];

        if (! Schema::hasTable('tenants')) {
            $tenants = $this->paginateTenants(collect(), $request);

            return view('public.search', [
                'companies'       => [],
                'tenants'         => $tenants,
                'term'            => $term,
                'location'        => $location,
                'category'        => $category,
                'categories'      => $categories,
                'searchTitle'     => $this->searchTitle($term, $location, $hasGeo),
                'metaDescription' => $metaDescription,
                'robots'          => $robots,
                'userLat'         => $userLat,
                'userLng'         => $userLng,
                'hasGeo'          => $hasGeo,
            ]);
        }

        $tenants          = $this->baseTenants();
        $servicesByTenant = $this->servicesByTenant($tenants);
        $filteredTenants  = $this->filterTenants($tenants, $servicesByTenant, $term, $location, $category);

        if ($hasGeo) {
            $filteredTenants = PublicSearch::sortByDistance($filteredTenants, $userLat, $userLng);
        }

        $paginatedTenants = $this->paginateTenants($filteredTenants, $request);

        return view('public.search', [
            'companies'       => $this->companyCards($paginatedTenants->getCollection()),
            'tenants'         => $paginatedTenants,
            'term'            => $term,
            'location'        => $location,
            'category'        => $category,
            'categories'      => $categories,
            'searchTitle'     => $this->searchTitle($term, $location, $hasGeo),
            'metaDescription' => $metaDescription,
            'robots'          => $robots,
            'userLat'         => $userLat,
            'userLng'         => $userLng,
            'hasGeo'          => $hasGeo,
        ]);
    }

    private function baseTenants(): Collection
    {
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

    private function filterTenants(Collection $tenants, Collection $servicesByTenant, string $term, string $location, string $category): Collection
    {
        $termTerms     = PublicSearch::terms($term);
        $locationTerms = PublicSearch::terms($location);
        $categoryTerms = $category !== '' ? PublicSearch::categoryTerms($category) : [];

        return $tenants
            ->filter(function (Tenant $tenant) use ($servicesByTenant, $termTerms, $locationTerms, $categoryTerms) {
                $services = $servicesByTenant->get($tenant->id, collect());

                if ($termTerms && ! PublicSearch::tenantMatches($tenant, $services, $termTerms)) {
                    return false;
                }

                if ($locationTerms && ! PublicSearch::tenantLocationMatches($tenant, $locationTerms)) {
                    return false;
                }

                if ($categoryTerms && ! PublicSearch::tenantMatches($tenant, $services, $categoryTerms)) {
                    return false;
                }

                return true;
            })
            ->values();
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

    private function metaDescription(string $term, string $location): string
    {
        if ($term !== '' && $location !== '') {
            return 'Veja resultados para ' . $term . ' em ' . Str::title($location) . ' no Guia Comercial.';
        }
        if ($term !== '') {
            return 'Veja resultados para ' . $term . ' cadastrados no Guia Comercial.';
        }
        if ($location !== '') {
            return 'Encontre serviços em ' . Str::title($location) . ' cadastrados no Guia Comercial.';
        }

        return 'Busque empresas, profissionais e serviços locais com agendamento online no Guia Comercial.';
    }

    private function searchTitle(string $term, string $location, bool $hasGeo = false): string
    {
        if ($term !== '' && $location !== '') {
            return 'Resultados para ' . Str::title($term) . ' em ' . Str::title($location);
        }

        if ($term !== '') {
            return 'Resultados para ' . Str::lower($term);
        }

        if ($location !== '') {
            return 'Serviços em ' . Str::title($location);
        }

        if ($hasGeo) {
            return 'Serviços perto de você';
        }

        return 'Buscar serviços';
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
