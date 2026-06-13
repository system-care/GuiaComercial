@extends('public.layouts.app', [
    'title'       => $searchTitle . ' | Guia Comercial',
    'description' => $metaDescription,
    'canonical'   => route('public.search'),
    'robots'      => $robots,
])

@section('content')
<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-violet-700">Busca local</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $searchTitle }}</h1>
            <p class="mt-4 text-base leading-7 text-slate-600">
                <span class="font-black text-slate-950">{{ $tenants->total() }}</span> empresa(s) encontrada(s). Refine por serviço, cidade ou bairro.
            </p>
        </div>

        <form action="{{ url('/buscar') }}" method="GET" aria-label="Refinar busca"
              class="mt-8 flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md sm:flex-row sm:items-stretch sm:overflow-visible">

            {{-- Campo: Serviço --}}
            <label class="flex min-w-0 flex-1 items-center gap-3 px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="sr-only">Serviço, empresa ou profissional</span>
                <input name="q" type="text" value="{{ $term }}" placeholder="Serviço, empresa ou profissional"
                       class="h-14 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
            </label>

            {{-- Divisor --}}
            <div class="mx-5 h-px bg-slate-200 sm:mx-0 sm:my-3 sm:h-auto sm:w-px sm:shrink-0"></div>

            {{-- Campo: Localização com dropdown --}}
            <div class="relative flex min-w-0 flex-1 items-center gap-3 px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <label class="sr-only" for="input-location-search">Cidade ou bairro</label>
                <input id="input-location-search" name="location" type="text" value="{{ $location }}" placeholder="Cidade ou bairro" autocomplete="off"
                       class="h-14 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
                <div id="geo-dropdown-search"
                     class="absolute left-0 top-full z-50 mt-1 hidden w-full min-w-[200px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                    <button type="button" id="btn-geo-search"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-slate-700 hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                        </svg>
                        <span id="btn-geo-search-label">Minha localização</span>
                    </button>
                </div>
            </div>

            @if ($category !== '')
                <input type="hidden" name="category" value="{{ $category }}">
            @endif

            {{-- Botão buscar --}}
            <button type="submit"
                    class="flex items-center justify-center gap-2 bg-orange-500 px-8 py-4 text-sm font-semibold text-white transition-colors hover:bg-orange-600 sm:rounded-r-2xl sm:py-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="sm:hidden">Buscar serviços</span>
            </button>
        </form>

        @include('public.components.geo-script', ['inputId' => 'input-location-search', 'dropdownId' => 'geo-dropdown-search', 'btnId' => 'btn-geo-search', 'labelId' => 'btn-geo-search-label'])

        <div class="mt-6 flex flex-wrap items-center gap-2">
            @foreach ($categories as $filter)
                <a href="{{ url('/buscar?' . http_build_query(array_filter(['q' => $term, 'location' => $location, 'category' => $filter['value'], 'lat' => $userLat, 'lng' => $userLng]))) }}"
                   class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $category === $filter['value'] ? 'border-violet-300 bg-violet-50 text-violet-700 shadow-sm shadow-violet-100' : 'border-slate-200 bg-white text-slate-700 hover:border-violet-200 hover:text-violet-700 hover:shadow-sm' }}">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full {{ $category === $filter['value'] ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-500' }}">
                        @include('public.components.category-icon', ['icon' => $filter['icon'] ?? null])
                    </span>
                    <span>{{ $filter['label'] }}</span>
                </a>
            @endforeach

            <a href="{{ url('/buscar') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-100">Limpar filtros</a>
            <a href="{{ url('/') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-100">Voltar para a home</a>
        </div>
    </div>
</section>

<section class="bg-slate-50 py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-950">Resultados</h2>
                <p class="mt-2 text-sm text-slate-600">Perfis públicos encontrados no Guia Comercial.</p>
            </div>
        </div>

        @include('public.components.company-cards', [
            'companies' => $companies,
            'emptyTitle' => 'Nenhuma empresa encontrada',
            'emptyText' => 'Tente remover filtros ou buscar por outro serviço, cidade ou bairro.',
            'emptyActionLabel' => 'Limpar filtros',
            'emptyActionUrl' => url('/buscar'),
            'secondaryActionLabel' => 'Voltar para a home',
            'secondaryActionUrl' => url('/'),
        ])

        @if ($tenants->hasPages())
            <div class="mt-8">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</section>

@endsection
