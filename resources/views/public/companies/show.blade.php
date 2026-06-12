@extends('public.layouts.app', [
    'title'       => $profile['name'] . ' | Guia Comercial',
    'description' => $metaDescription,
    'canonical'   => route('public.companies.show', $tenant->slug),
    'ogType'      => 'business.business',
    'ogImage'     => $profile['logo_url'] ?? $profile['banner_url'] ?? null,
])

@section('content')
    <main class="bg-slate-50 pb-32 lg:pb-0">
        <section class="relative overflow-hidden text-white {{ $profile['banner_url'] ? 'bg-transparent' : 'bg-slate-950' }}">
            @if ($profile['banner_url'])
                <div class="absolute inset-0">
                    <img src="{{ $profile['banner_url'] }}" alt="" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-black/50"></div>
                </div>
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.35),_transparent_35%),linear-gradient(135deg,_#020617,_#0f172a_55%,_#0369a1)]"></div>
            @endif

            <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8 lg:py-16">
                <div>
                    <a href="{{ route('public.search') }}" class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        ← Voltar para a busca
                    </a>

                    <div class="mt-8 flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl border border-white/20 bg-white text-3xl font-black text-sky-700 shadow-2xl">
                            @if ($profile['logo_url'])
                                <img src="{{ $profile['logo_url'] }}" alt="Logo de {{ $profile['name'] }}" class="h-full w-full object-cover">
                            @else
                                {{ $profile['initials'] }}
                            @endif
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-sky-400/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-sky-100">{{ $profile['category'] }}</span>
                                <span class="rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-emerald-100">
                                    {{ $profile['has_online_booking'] ? 'Agenda online' : 'Perfil publicado' }}
                                </span>
                            </div>
                            <h1 class="mt-4 text-4xl font-black tracking-tight sm:text-5xl">{{ $profile['name'] }}</h1>
                            <p class="mt-3 max-w-2xl text-base text-slate-200 sm:text-lg">{{ $profile['location'] }}</p>
                        </div>
                    </div>

                    <p class="mt-8 max-w-3xl text-lg leading-8 text-slate-100">{{ $profile['description'] }}</p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $profile['booking_url'] }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-sky-500 px-6 py-4 text-sm font-black text-white shadow-lg shadow-sky-950/30 transition hover:bg-sky-400">
                            Agendar atendimento
                        </a>
                        @if ($profile['whatsapp_url'])
                            <a href="{{ $profile['whatsapp_url'] }}" target="_blank" rel="noopener" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-white/20 bg-white/10 px-6 py-4 text-sm font-black text-white backdrop-blur transition hover:bg-white/15">
                                Chamar no WhatsApp
                            </a>
                        @endif
                        <a href="#servicos" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-white/20 px-6 py-4 text-sm font-black text-white transition hover:bg-white/10">
                            Ver serviços
                        </a>
                    </div>
                </div>

                <aside class="rounded-3xl border border-white/15 bg-white/10 p-6 shadow-2xl backdrop-blur lg:self-end">
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-sky-100">Resumo</p>
                    <div class="mt-5 space-y-4 text-sm text-slate-100">
                        <div>
                            <p class="font-black text-white">Categoria</p>
                            <p>{{ $profile['category'] }}</p>
                        </div>
                        <div>
                            <p class="font-black text-white">Localização</p>
                            <p>{{ $profile['location'] }}</p>
                            @if (! empty($distanceLabel))
                                <p class="mt-1 font-semibold text-sky-300">{{ $distanceLabel }}</p>
                            @endif
                        </div>
                        @if ($profile['phone'])
                            <div>
                                <p class="font-black text-white">Contato público</p>
                                <p>{{ $profile['phone'] }}</p>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </section>

        @php
            $mapAddress = $profile['public_address']
                ? ($profile['public_address'] . ($tenant->city ? ', ' . $tenant->city : ''))
                : ($tenant->city ?: null);
            $socialLinks = array_filter([
                'Instagram' => $settings['instagram'] ?? null,
                'Facebook'  => $settings['facebook']  ?? null,
                'YouTube'   => $settings['youtube']   ?? null,
                'TikTok'    => $settings['tiktok']    ?? null,
                'Website'   => $settings['website']   ?? null,
            ]);
        @endphp

        <style>[x-cloak]{display:none!important}</style>

        <div x-data="{ modal: null }" @keydown.escape.window="modal = null">

            {{-- ── Modal overlay ─────────────────────────────────────────────── --}}
            <div
                x-show="modal !== null"
                x-cloak
                class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
            >
                {{-- Backdrop --}}
                <div
                    x-show="modal !== null"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    @click="modal = null"
                ></div>

                {{-- Dialog --}}
                <div
                    x-show="modal !== null"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-10 w-full max-h-[90vh] overflow-y-auto rounded-t-3xl bg-white shadow-2xl sm:max-w-2xl sm:rounded-3xl"
                >
                    <div class="mx-auto mt-3 h-1 w-10 rounded-full bg-slate-300 sm:hidden"></div>

                    {{-- Botão fechar fixo --}}
                    <button @click="modal = null"
                        class="absolute right-4 top-4 z-20 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>

                    {{-- ── Modais por serviço ──────────────────────────── --}}
                    @foreach ($services as $service)
                        <div x-show="modal === 'service-{{ $service->id }}'">
                            <div class="px-6 pb-2 pt-6">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-600">Serviço</p>
                                <h3 class="mt-1 pr-10 text-2xl font-black text-slate-950">{{ $service->name }}</h3>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
                                    @if ($service->duration_minutes)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $service->duration_minutes }} min</span>
                                    @endif
                                    @if ($profile['show_prices'] && ! is_null($service->price))
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">R$ {{ number_format((float) $service->price, 2, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="px-6 pb-6 pt-4">
                                @if ($service->description)
                                    <div class="prose prose-sm max-w-none leading-7 text-slate-600">{!! $service->description !!}</div>
                                @else
                                    <p class="text-sm text-slate-400">Sem descrição cadastrada.</p>
                                @endif
                                <a href="{{ $profile['booking_url'] }}" class="mt-6 flex items-center justify-center rounded-2xl bg-sky-600 px-5 py-4 text-sm font-black text-white transition hover:bg-sky-700">
                                    Agendar este serviço
                                </a>
                            </div>
                        </div>
                    @endforeach

                    {{-- ── Modais por profissional ─────────────────────── --}}
                    @foreach ($professionals as $professional)
                        @php
                            $initials = \Illuminate\Support\Str::of($professional->name)->explode(' ')->filter()->take(2)->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))->implode('') ?: 'PR';
                            $proSocials = array_filter((array) ($professional->social_links ?? []));
                        @endphp
                        <div x-show="modal === 'professional-{{ $professional->id }}'">
                            <div class="flex items-center gap-4 px-6 pb-2 pt-6">
                                @if ($professional->avatar_path)
                                    <img src="{{ asset('storage/' . $professional->avatar_path) }}" alt="{{ $professional->name }}" class="h-16 w-16 shrink-0 rounded-2xl object-cover">
                                @else
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-lg font-black text-sky-700">{{ $initials }}</div>
                                @endif
                                <div class="pr-10">
                                    <h3 class="text-2xl font-black text-slate-950">{{ $professional->name }}</h3>
                                    @if ($professional->specialty)
                                        <p class="mt-1 text-sm font-semibold text-sky-600">{{ $professional->specialty }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="px-6 pb-6 pt-4">
                                @if ($professional->bio)
                                    <div class="prose prose-sm max-w-none leading-7 text-slate-600">{!! $professional->bio !!}</div>
                                @else
                                    <p class="text-sm text-slate-400">Sem bio cadastrada.</p>
                                @endif
                                @if (! empty($proSocials))
                                    <div class="mt-5 flex flex-wrap gap-3">
                                        @foreach ($proSocials as $net => $url)
                                            @if ($url)
                                                <a href="{{ $url }}" target="_blank" rel="noopener"
                                                   class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 transition hover:border-sky-200 hover:bg-sky-50">
                                                    {{ ucfirst($net) }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                                <a href="{{ $profile['booking_url'] }}" class="mt-6 flex items-center justify-center rounded-2xl bg-sky-600 px-5 py-4 text-sm font-black text-white transition hover:bg-sky-700">
                                    Agendar com {{ explode(' ', $professional->name)[0] }}
                                </a>
                            </div>
                        </div>
                    @endforeach

                    {{-- ── Modal localização ───────────────────────────── --}}
                    <div x-show="modal === 'localizacao'">
                        <div class="px-6 pb-2 pt-6">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-600">Localização</p>
                            <h3 class="mt-1 pr-10 text-2xl font-black text-slate-950">Onde atendemos</h3>
                        </div>
                        <div class="space-y-4 px-6 pb-6 pt-4">
                            @if ($mapAddress)
                                <div class="overflow-hidden rounded-2xl border border-slate-200">
                                    <iframe src="https://maps.google.com/maps?q={{ urlencode($mapAddress) }}&output=embed&hl=pt"
                                        width="100%" height="260" style="border:0;" allowfullscreen="" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            @endif
                            <div class="grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-5">
                                    <p class="font-black text-slate-950">Região</p>
                                    <p class="mt-2">{{ $profile['location'] }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-5">
                                    <p class="font-black text-slate-950">Endereço</p>
                                    <p class="mt-2">{{ $profile['public_address'] ?: 'Endereço completo não publicado.' }}</p>
                                </div>
                                @if (! empty($profile['attendance_modes']))
                                    <div class="rounded-2xl bg-slate-50 p-5 sm:col-span-2">
                                        <p class="font-black text-slate-950">Modalidades</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($profile['attendance_modes'] as $mode)
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">{{ $mode }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if ($profile['cancellation_policy'])
                                    <div class="rounded-2xl bg-slate-50 p-5 sm:col-span-2">
                                        <p class="font-black text-slate-950">Política de cancelamento</p>
                                        <p class="mt-2">{{ $profile['cancellation_policy'] }}</p>
                                    </div>
                                @endif
                            </div>
                            @if ($mapAddress)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($mapAddress) }}" target="_blank" rel="noopener"
                                   class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-sky-200 hover:bg-sky-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    Abrir no Google Maps
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Conteúdo principal ──────────────────────────────────────────── --}}
            <section class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
                <div class="space-y-8">

                    {{-- Sobre --}}
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Sobre</p>
                        <h2 class="mt-3 text-2xl font-black text-slate-950">Conheça {{ $profile['name'] }}</h2>
                        <p class="mt-4 leading-7 text-slate-600">{{ $profile['description'] }}</p>
                    </article>

                    {{-- Serviços — grid 3 colunas, cards quadrados --}}
                    @if ($services->isNotEmpty())
                        <div id="servicos">
                            <p class="mb-4 text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Serviços</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($services as $service)
                                    <button type="button" @click="modal = 'service-{{ $service->id }}'"
                                        class="group relative flex aspect-square w-full flex-col justify-between rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:border-sky-300 hover:shadow-md">
                                        <div class="flex-1 overflow-hidden">
                                            <p class="font-black leading-snug text-slate-950">{{ $service->name }}</p>
                                            @if ($service->description)
                                                <p class="mt-2 text-xs leading-5 text-slate-500 line-clamp-6">{{ strip_tags($service->description) }}</p>
                                            @endif
                                        </div>
                                        <div class="mt-3 flex items-end justify-between gap-2">
                                            <div class="flex flex-wrap gap-1 text-xs font-bold">
                                                @if ($service->duration_minutes)
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-slate-600">{{ $service->duration_minutes }}min</span>
                                                @endif
                                                @if ($profile['show_prices'] && ! is_null($service->price))
                                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700">R${{ number_format((float) $service->price, 2, ',', '.') }}</span>
                                                @endif
                                            </div>
                                            <span class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-sky-100 group-hover:text-sky-600">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Equipe — grid 3 colunas, cards quadrados --}}
                    @if ($profile['show_team'] && $professionals->isNotEmpty())
                        <div>
                            <p class="mb-4 text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Equipe</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($professionals as $professional)
                                    @php
                                        $initials = \Illuminate\Support\Str::of($professional->name)->explode(' ')->filter()->take(2)->map(fn ($p) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($p, 0, 1)))->implode('') ?: 'PR';
                                    @endphp
                                    <button type="button" @click="modal = 'professional-{{ $professional->id }}'"
                                        class="group relative flex aspect-square w-full flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm transition hover:border-sky-300 hover:shadow-md">
                                        @if ($professional->avatar_path)
                                            <img src="{{ asset('storage/' . $professional->avatar_path) }}" alt="{{ $professional->name }}" class="h-14 w-14 shrink-0 rounded-2xl object-cover">
                                        @else
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-base font-black text-sky-700">{{ $initials }}</div>
                                        @endif
                                        <div class="min-w-0 w-full">
                                            <p class="truncate font-black text-slate-950">{{ $professional->name }}</p>
                                            @if ($professional->specialty)
                                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $professional->specialty }}</p>
                                            @endif
                                            @if ($professional->bio)
                                                <p class="mt-1 text-xs leading-4 text-slate-400 line-clamp-4">{{ strip_tags($professional->bio) }}</p>
                                            @endif
                                        </div>
                                        <span class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-sky-100 group-hover:text-sky-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Localização — card com prévia --}}
                    <div>
                        <p class="mb-4 text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Localização e atendimento</p>
                        <button type="button" @click="modal = 'localizacao'"
                            class="group flex w-full items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-sky-300 hover:shadow-md">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div>
                                    <p class="font-black text-slate-950">{{ $profile['public_address'] ?: $profile['location'] }}</p>
                                    @if ($profile['public_address'])
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $profile['location'] }}</p>
                                    @endif
                                    @if ($mapAddress)
                                        <p class="mt-1 text-xs font-semibold text-sky-600">Ver no mapa →</p>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-sky-100 group-hover:text-sky-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </button>
                    </div>

                    {{-- Informações úteis --}}
                    @if (! empty($profile['working_hours']) || ! empty($profile['attendance_modes']) || $profile['cancellation_policy'])
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Informações úteis</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-950">Antes de agendar</h2>
                            <div class="mt-5 grid gap-4">
                                @if (! empty($profile['working_hours']))
                                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">
                                        <p class="font-black text-slate-950">Horários</p>
                                        <p class="mt-2">Horários de atendimento cadastrados pela empresa. A disponibilidade final aparece no fluxo de agendamento.</p>
                                    </div>
                                @endif
                                @if (! empty($profile['attendance_modes']))
                                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">
                                        <p class="font-black text-slate-950">Modalidades</p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($profile['attendance_modes'] as $mode)
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">{{ $mode }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if ($profile['cancellation_policy'])
                                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">
                                        <p class="font-black text-slate-950">Política de cancelamento</p>
                                        <p class="mt-2">{{ $profile['cancellation_policy'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endif

                </div>

                <aside class="hidden lg:flex lg:flex-col lg:gap-4">
                    <div class="sticky top-6 flex flex-col gap-4">
                        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl">
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Agendamento</p>
                            <h2 class="mt-3 text-2xl font-black text-slate-950">Fale com a empresa</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">Use os canais públicos do perfil para consultar horários, serviços e disponibilidade.</p>
                            <div class="mt-6 space-y-3">
                                <a href="{{ $profile['booking_url'] }}" class="flex items-center justify-center rounded-2xl bg-sky-600 px-5 py-4 text-sm font-black text-white transition hover:bg-sky-700">
                                    Agendar agora
                                </a>
                                @if ($profile['whatsapp_url'])
                                    <a href="{{ $profile['whatsapp_url'] }}" target="_blank" rel="noopener" class="flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-4 text-sm font-black text-slate-800 transition hover:border-emerald-200 hover:bg-emerald-50">
                                        WhatsApp
                                    </a>
                                @endif
                            </div>
                            <div class="mt-6 border-t border-slate-200 pt-6 text-sm text-slate-600">
                                <p class="font-black text-slate-950">{{ $profile['has_online_booking'] ? 'Agenda online disponível' : 'Perfil publicado' }}</p>
                                <p class="mt-2">{{ $profile['location'] }}</p>
                                @if (! empty($distanceLabel))
                                    <p class="mt-1 font-semibold text-sky-700">{{ $distanceLabel }}</p>
                                @endif
                            </div>
                        </div>

                        @if (! empty($socialLinks))
                            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                                <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-600">Redes sociais</p>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    @foreach ($socialLinks as $label => $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                           class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">
                                            @if ($label === 'Instagram')
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.8" fill="currentColor" stroke="none"/></svg>
                                            @elseif ($label === 'Facebook')
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                            @elseif ($label === 'YouTube')
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.96-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/></svg>
                                            @elseif ($label === 'TikTok')
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.2 8.2 0 0 0 4.78 1.52V6.75a4.85 4.85 0 0 1-1.01-.06z"/></svg>
                                            @elseif ($label === 'Website')
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                            @endif
                                            {{ $label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </aside>
            </section>

        </div>

        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-3 pt-3 shadow-2xl backdrop-blur lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <div class="mx-auto flex max-w-7xl gap-2">
                <a href="{{ $profile['booking_url'] }}" class="flex min-h-12 flex-1 items-center justify-center rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">
                    Agendar
                </a>
                @if ($profile['whatsapp_url'])
                    <a href="{{ $profile['whatsapp_url'] }}" target="_blank" rel="noopener" class="flex min-h-12 flex-1 items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-800">
                        WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </main>
@endsection

@push('jsonld')
@php
$_ld = [
    '@context'    => 'https://schema.org',
    '@type'       => 'LocalBusiness',
    'name'        => $profile['name'],
    'description' => $metaDescription,
    'url'         => route('public.companies.show', $tenant->slug),
];
if ($profile['logo_url'])        $_ld['image'] = $profile['logo_url'];
elseif ($profile['banner_url'])  $_ld['image'] = $profile['banner_url'];
if ($profile['phone'])           $_ld['telephone'] = $profile['phone'];
if ($profile['public_address']) {
    $_ld['address'] = array_filter([
        '@type'           => 'PostalAddress',
        'streetAddress'   => $profile['public_address'],
        'addressLocality' => $tenant->city ?: null,
        'addressCountry'  => 'BR',
    ]);
} elseif ($tenant->city) {
    $_ld['address'] = ['@type' => 'PostalAddress', 'addressLocality' => $tenant->city, 'addressCountry' => 'BR'];
}
if ($tenant->settings?->latitude && $tenant->settings?->longitude) {
    $_ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $tenant->settings->latitude, 'longitude' => $tenant->settings->longitude];
}
@endphp
<script type="application/ld+json">{!! json_encode($_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endpush
