@extends('public.layouts.app', [
    'title'       => 'Guia Comercial | Encontre serviços perto de você',
    'description' => 'Busque empresas, profissionais e serviços locais com agendamento online no Guia Comercial.',
    'canonical'   => route('public.home'),
])

@section('content')
<section class="relative overflow-hidden flex items-center" style="background-image:url('{{ asset('logo/profissoes-fade-full.webp') }}');background-size:cover;background-position:top center;background-repeat:no-repeat;min-height:100vh;">
    <div class="absolute inset-0 bg-white/60"></div>
    <img src="{{ asset('logo/bg-long.svg') }}" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none">
    <div class="relative w-full mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20 text-center">
        <span class="mb-5 inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-violet-700">Guia local com agenda online</span>
        <h1 class="mt-4 w-full text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-5xl lg:text-6xl lg:whitespace-nowrap">Encontre serviços perto de você</h1>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">Busque empresas, profissionais e prestadores locais, compare informações públicas e agende online quando disponível.</p>

        <form action="{{ url('/buscar') }}" method="GET" aria-label="Buscar serviços locais"
              class="mx-auto mt-8 w-full max-w-2xl flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 sm:flex-row sm:items-stretch sm:overflow-visible">

            {{-- Campo: O que procura --}}
            <label class="flex min-w-0 flex-1 items-center gap-3 px-5 sm:border-b-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="sr-only">O que você procura?</span>
                <input name="q" type="text" placeholder="O que você procura?"
                       class="h-14 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
            </label>

            {{-- Divisor horizontal (mobile) / vertical (desktop) --}}
            <div class="mx-5 h-px bg-slate-200 sm:mx-0 sm:my-3 sm:h-auto sm:w-px sm:shrink-0"></div>

            {{-- Campo: Localização --}}
            <div class="relative flex min-w-0 flex-1 items-center gap-3 px-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <label class="sr-only" for="input-location-home">Cidade ou bairro</label>
                <input id="input-location-home" name="location" type="text" value="{{ $detectedCity }}" placeholder="Cidade ou bairro" autocomplete="off"
                       class="h-14 w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 outline-none">
                {{-- Dropdown de sugestão --}}
                <div id="geo-dropdown-home"
                     class="absolute left-0 top-full z-50 mt-1 hidden w-full min-w-[200px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                    <button type="button" id="btn-geo-home"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-slate-700 hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                        </svg>
                        <span id="btn-geo-home-label">Minha localização</span>
                    </button>
                </div>
            </div>

            {{-- Botão buscar --}}
            <button type="submit"
                    class="flex items-center justify-center gap-2 bg-orange-500 px-8 py-4 text-sm font-semibold text-white transition-colors hover:bg-orange-600 sm:rounded-r-2xl sm:py-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="sm:hidden">Buscar serviços</span>
                <span class="sr-only hidden sm:not-sr-only sm:hidden">Buscar</span>
            </button>
        </form>

        @include('public.components.geo-script', ['inputId' => 'input-location-home', 'dropdownId' => 'geo-dropdown-home', 'btnId' => 'btn-geo-home', 'labelId' => 'btn-geo-home-label'])

        <div class="mt-5 flex flex-wrap justify-center items-center gap-x-1 gap-y-2 text-sm">
            <span class="font-semibold text-slate-600 mr-1">Popular:</span>
            @foreach ($categories as $category)
                <a href="{{ url('/servicos/' . $category['slug']) }}" class="rounded-full border border-slate-200 bg-white/70 px-3 py-1 text-slate-700 hover:border-orange-200 hover:bg-orange-50 hover:text-orange-600 transition-colors">{{ $category['name'] }}</a>
            @endforeach
        </div>
    </div>
</section>

<section class="border-t border-slate-100 bg-slate-50 py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-violet-700">Vitrine local</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Empresas disponíveis no Guia</h2>
                <p class="mt-2 text-sm text-slate-600">Empresas e prestadores ativos cadastrados no Guia Comercial.</p>
            </div>
            <a href="{{ url('/buscar') }}" class="text-sm font-black text-violet-700 hover:text-violet-800">Ver busca completa</a>
        </div>

        @include('public.components.company-cards', ['companies' => $companies])
    </div>
</section>

@if (! empty($cityLinks))
<section class="border-y border-slate-200 bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6 max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-violet-700">Busca local</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Explore por cidade</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Veja empresas cadastradas nas cidades que já possuem perfis publicados no Guia.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($cityLinks as $city)
                <a href="{{ $city['url'] }}" class="rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700">Serviços em {{ $city['name'] }}</a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="border-y border-slate-200 bg-slate-50 py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wide text-violet-700">Como funciona</p>
            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Do interesse ao agendamento em poucos passos</h2>
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([['Buscar', 'Pesquise por serviço, categoria, cidade ou bairro.'], ['Conhecer', 'Abra o perfil da empresa e veja informações públicas.'], ['Agendar', 'Quando disponível, avance para o agendamento online.']] as $step)
                <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-lg font-black text-violet-700">{{ $loop->iteration }}</div>
                    <h3 class="text-lg font-black text-slate-950">{{ $step[0] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $step[1] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>


<section class="bg-slate-950 py-14 text-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight">Sua empresa também pode aparecer no Guia Comercial</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Publique seu perfil, apresente seus serviços e facilite o caminho para novos agendamentos.</p>
        </div>
        <a href="{{ $panelRegisterUrl }}" class="inline-flex shrink-0 justify-center rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 hover:bg-slate-100">Cadastrar minha empresa</a>
    </div>
</section>

@endsection

@push('jsonld')
@php
$_ld = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => 'Guia Comercial',
    'url'      => route('public.home'),
    'potentialAction' => [
        '@type'       => 'SearchAction',
        'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => url('/buscar') . '?q={search_term_string}'],
        'query-input' => 'required name=search_term_string',
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endpush
