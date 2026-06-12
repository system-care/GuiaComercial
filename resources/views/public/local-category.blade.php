@extends('public.layouts.app', [
    'title'       => $categoryName . ' em ' . $cityName . ' | Guia Comercial',
    'description' => 'Veja empresas e profissionais de ' . $categoryName . ' em ' . $cityName . ' cadastrados no Guia Comercial.',
    'canonical'   => route('public.category.city', ['category' => $categorySlug, 'city' => $citySlug]),
])

@section('content')
<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap gap-3">
            @php
                $geoSuffix = ($hasGeo ?? false) ? '?lat=' . $userLat . '&lng=' . $userLng : '';
            @endphp
            <a href="{{ route('public.category', $categorySlug) . $geoSuffix }}" class="inline-flex text-sm font-black text-violet-700 hover:text-violet-800">← Ver {{ $categoryName }}</a>
            <a href="{{ route('public.cities.show', $citySlug) . $geoSuffix }}" class="inline-flex text-sm font-black text-violet-700 hover:text-violet-800">Ver serviços em {{ $cityName }}</a>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1fr_420px] lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-violet-700">Categoria local</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $categoryName }} em {{ $cityName }}</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Veja empresas e profissionais de {{ $categoryName }} em {{ $cityName }} cadastrados no Guia Comercial.</p>
                <p class="mt-3 text-sm font-semibold text-slate-700"><span class="font-black">{{ $tenants->total() }}</span> empresa(s) encontrada(s).</p>
            </div>

            <div>
                <form action="{{ url('/buscar') }}" method="GET" aria-label="Buscar {{ $categoryName }} em {{ $cityName }}"
                      class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md sm:flex-row sm:items-stretch sm:overflow-visible">
                    <input type="hidden" name="category" value="{{ $categorySlug }}">

                    <label class="flex min-w-0 flex-1 items-center gap-3 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="sr-only">Buscar em {{ $categoryName }}</span>
                        <input name="q" type="text" placeholder="Buscar em {{ $categoryName }}"
                               class="h-12 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
                    </label>

                    <div class="mx-4 h-px bg-slate-200 sm:mx-0 sm:my-3 sm:h-auto sm:w-px sm:shrink-0"></div>

                    <div class="relative flex min-w-0 flex-1 items-center gap-3 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <label class="sr-only" for="input-location-local-cat">Cidade ou bairro</label>
                        <input id="input-location-local-cat" name="location" type="text" value="{{ $cityName }}" placeholder="Cidade ou bairro" autocomplete="off"
                               class="h-12 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
                        <div id="geo-dropdown-local-cat" class="absolute left-0 top-full z-50 mt-1 hidden w-full min-w-[200px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                            <button type="button" id="btn-geo-local-cat" class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-slate-700 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                                </svg>
                                <span id="btn-geo-local-cat-label">Minha localização</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="flex items-center justify-center gap-2 bg-orange-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-orange-600 sm:rounded-r-2xl sm:py-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="sm:hidden">Buscar</span>
                    </button>
                </form>
                @include('public.components.geo-script', ['inputId' => 'input-location-local-cat', 'dropdownId' => 'geo-dropdown-local-cat', 'btnId' => 'btn-geo-local-cat', 'labelId' => 'btn-geo-local-cat-label'])
            </div>
        </div>
    </div>
</section>

<section class="bg-slate-50 py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-2xl font-black tracking-tight text-slate-950">Empresas de {{ $categoryName }} em {{ $cityName }}</h2>
            <p class="mt-2 text-sm text-slate-600">Resultados filtrados por categoria e localização textual.</p>
        </div>

        @include('public.components.company-cards', [
            'companies' => $companies,
            'emptyTitle' => 'Nenhuma empresa encontrada para ' . $categoryName . ' em ' . $cityName,
            'emptyText' => 'Tente buscar por outra cidade, bairro ou tipo de serviço.',
            'emptyActionLabel' => 'Limpar filtros',
            'emptyActionUrl' => url('/buscar'),
            'secondaryActionLabel' => 'Ver serviços em ' . $cityName,
            'secondaryActionUrl' => route('public.cities.show', $citySlug),
        ])

        @if ($tenants->hasPages())
            <div class="mt-8">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</section>
@endsection

@push('jsonld')
@php
$_ld = [
    '@context'    => 'https://schema.org',
    '@type'       => 'CollectionPage',
    'name'        => $categoryName . ' em ' . $cityName . ' | Guia Comercial',
    'description' => 'Veja empresas e profissionais de ' . $categoryName . ' em ' . $cityName . ' cadastrados no Guia Comercial.',
    'url'         => route('public.category.city', ['category' => $categorySlug, 'city' => $citySlug]),
    'breadcrumb'  => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',          'item' => route('public.home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $categoryName,   'item' => route('public.category', $categorySlug)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $cityName,       'item' => route('public.category.city', ['category' => $categorySlug, 'city' => $citySlug])],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endpush
