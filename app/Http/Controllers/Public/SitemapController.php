<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\PublicSearch;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    private const CATEGORIES = [
        'clinicas',
        'estetica',
        'saloes',
        'barbearias',
        'lava-jato',
        'pet-shop',
        'servicos-domiciliares',
        'consultorias',
    ];

    public function __invoke(): Response
    {
        $tenants = Schema::hasTable('tenants')
            ? Tenant::query()
                ->where('active', true)
                ->with(['settings', 'niche'])
                ->orderBy('updated_at', 'desc')
                ->limit(1000)
                ->get(['id', 'slug', 'city', 'updated_at', 'business_niche_id'])
            : collect();

        $cities = $tenants
            ->pluck('city')
            ->filter(fn ($city) => is_string($city) && trim($city) !== '')
            ->map(fn ($city) => PublicSearch::citySlug($city))
            ->filter()
            ->unique()
            ->values();

        // Combinações categoria+cidade baseadas em tenants reais
        $categoryCityPairs = collect();
        foreach (self::CATEGORIES as $catSlug) {
            $catTerms = PublicSearch::categoryTerms($catSlug);
            foreach ($tenants as $tenant) {
                if (! $tenant->city || ! $tenant->slug) {
                    continue;
                }
                if (PublicSearch::tenantMatches($tenant, collect(), $catTerms)) {
                    $categoryCityPairs->push([
                        'category' => $catSlug,
                        'city'     => PublicSearch::citySlug($tenant->city),
                    ]);
                }
            }
        }
        $categoryCityPairs = $categoryCityPairs
            ->unique(fn ($p) => $p['category'] . '/' . $p['city'])
            ->values()
            ->take(500);

        $content = view('public.sitemap', [
            'tenants'             => $tenants,
            'categories'          => self::CATEGORIES,
            'cities'              => $cities,
            'categoryCityPairs'   => $categoryCityPairs,
            'now'                 => now()->format('Y-m-d'),
        ])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
