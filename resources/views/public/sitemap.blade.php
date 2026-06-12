<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Home --}}
    <url>
        <loc>{{ route('public.home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
        <lastmod>{{ $now }}</lastmod>
    </url>

    {{-- Categorias --}}
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('public.category', $category) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <lastmod>{{ $now }}</lastmod>
    </url>
    @endforeach

    {{-- Cidades --}}
    @foreach ($cities as $city)
    <url>
        <loc>{{ route('public.cities.show', $city) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
        <lastmod>{{ $now }}</lastmod>
    </url>
    @endforeach

    {{-- Categoria + cidade (combinações reais de tenants) --}}
    @foreach ($categoryCityPairs as $pair)
    <url>
        <loc>{{ route('public.category.city', ['category' => $pair['category'], 'city' => $pair['city']]) }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
        <lastmod>{{ $now }}</lastmod>
    </url>
    @endforeach

    {{-- Empresas --}}
    @foreach ($tenants as $tenant)
    @if ($tenant->slug)
    <url>
        <loc>{{ route('public.companies.show', $tenant->slug) }}</loc>
        <lastmod>{{ $tenant->updated_at->format('Y-m-d') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @endif
    @endforeach

</urlset>
