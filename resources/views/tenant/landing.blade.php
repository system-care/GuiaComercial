<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} | Guia Comercial</title>
    <meta name="description" content="{{ $settings['description'] ?? 'Agende seu horário online de forma rápida e fácil.' }}">

    <meta property="og:title" content="{{ $tenant->name }}">
    <meta property="og:description" content="{{ $settings['description'] ?? 'Agende seu horário online de forma rápida e fácil.' }}">
    @if (!empty($settings['logo_path']))
        <meta property="og:image" content="{{ asset('storage/' . $settings['logo_path']) }}">
    @endif
    <meta property="og:type" content="website">

    @php
        $primary = $settings['primary_color'] ?? '#7c3aed';
        $cleanWhatsapp = preg_replace('/\D+/', '', (string) ($settings['whatsapp'] ?? $tenant->phone ?? ''));
        if ($cleanWhatsapp && strlen($cleanWhatsapp) <= 11 && ! str_starts_with($cleanWhatsapp, '55')) {
            $cleanWhatsapp = '55' . $cleanWhatsapp;
        }
        $whatsappUrl = $cleanWhatsapp ? 'https://wa.me/' . $cleanWhatsapp . '?text=' . rawurlencode('Seja bem-vindo! Como podemos lhe ajudar?') : null;
        $waEncoded   = $cleanWhatsapp ? base64_encode($cleanWhatsapp) : null;
        $hasBooking = (bool) ($settings['allow_online_booking'] ?? true);
        $description = trim((string) ($settings['description'] ?? ''));
        $heroTitle = trim((string) ($settings['hero_title'] ?? ''));
        $heroSubtitle = trim((string) ($settings['hero_subtitle'] ?? ''));
        $address = trim((string) ($settings['address'] ?? ''));
        $location = $address !== '' ? $address : $tenant->city;
        $logoUrl           = !empty($settings['logo_path'])            ? asset('storage/' . $settings['logo_path'])            : null;
        $logoHorizontalUrl = !empty($settings['logo_horizontal_path']) ? asset('storage/' . $settings['logo_horizontal_path']) : null;
        $bannerUrl         = !empty($settings['banner_path'])          ? asset('storage/' . $settings['banner_path'])          : null;
        $aboutImageUrl     = !empty($settings['about_image_path'])     ? asset('storage/' . $settings['about_image_path'])     : null;
        $nicheName     = $tenant->niche->name ?? 'Empresa verificada';
    @endphp

    @vite(['resources/css/app.css'])
    <style>
        :root {
            --brand: {{ $primary }};
            --brand-dark: {{ $primary }};
        }

        body {
            background:
                radial-gradient(circle at top left, color-mix(in srgb, var(--brand) 14%, transparent), transparent 36rem),
                linear-gradient(180deg, #f8fafc 0%, #ffffff 42%, #f8fafc 100%);
        }

        .gc-glass {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(18px);
        }

        .gc-hero-bg {
            background:
                linear-gradient(135deg, color-mix(in srgb, var(--brand) 96%, #111827 4%) 0%, #111827 100%);
        }

        .gc-hero-image {
            background-image:
                linear-gradient(135deg, rgba(15, 23, 42, .74), rgba(15, 23, 42, .32)),
                url('{{ $bannerUrl }}');
        }

        .gc-section-label {
            color: var(--brand);
        }

        .gc-brand-ring {
            box-shadow: 0 0 0 8px color-mix(in srgb, var(--brand) 10%, transparent);
        }

        .gc-wa-float {
            bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @livewireStyles
    <script>function openWa(e){window.open('https://wa.me/'+atob(e)+'?text='+encodeURIComponent('Seja bem-vindo! Como podemos lhe ajudar?'),'_blank','noopener,noreferrer')}</script>
</head>
<body
    class="min-h-screen font-sans text-slate-800 antialiased"
    x-data="{
        serviceModalOpen: false,
        serviceModal: {
            name: '',
            description: '',
            duration: '',
            price: '',
        },
        openServiceModal(service) {
            this.serviceModal = service;
            this.serviceModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        openServiceModalFromButton(button) {
            this.openServiceModal({
                name: button.dataset.serviceName || '',
                description: button.dataset.serviceDescription || 'Este serviço ainda não possui uma descrição completa cadastrada.',
                duration: button.dataset.serviceDuration || '',
                price: button.dataset.servicePrice || '',
            });
        },
        closeServiceModal() {
            this.serviceModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        profModalOpen: false,
        profModal: { name: '', specialty: '', bio: '', avatar: '', color: '' },
        openProfModal(data) {
            this.profModal = data;
            this.profModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeProfModal() {
            this.profModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
    }"
    @keydown.escape.window="closeServiceModal(); closeProfModal()"
>
    <header class="sticky top-0 z-40 border-b border-white/70 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="#inicio" class="flex min-w-0 items-center gap-3">
                @if ($logoHorizontalUrl)
                    <img src="{{ $logoHorizontalUrl }}" alt="Logo {{ $tenant->name }}" class="h-9 w-auto max-w-[200px] object-contain">
                @elseif ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $tenant->name }}" class="h-11 w-11 rounded-2xl object-contain ring-1 ring-slate-200">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-black text-slate-950 sm:text-base">{{ $tenant->name }}</span>
                        <span class="hidden truncate text-xs font-semibold text-slate-500 sm:block">{{ $nicheName }}</span>
                    </span>
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand text-lg font-black text-white">
                        {{ mb_substr($tenant->name, 0, 1) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-black text-slate-950 sm:text-base">{{ $tenant->name }}</span>
                        <span class="hidden truncate text-xs font-semibold text-slate-500 sm:block">{{ $nicheName }}</span>
                    </span>
                @endif
            </a>

            <nav class="hidden items-center gap-7 text-sm font-bold text-slate-600 md:flex">
                <a href="#sobre" class="hover:text-slate-950">Sobre</a>
                @if ($services->count())
                    <a href="#servicos" class="hover:text-slate-950">Serviços</a>
                @endif
                @if (($settings['show_team'] ?? true) && $professionals->count())
                    <a href="#equipe" class="hover:text-slate-950">Equipe</a>
                @endif
                @if ($hasBooking)
                    <a href="#agendar" class="hover:text-slate-950">Agendar</a>
                @endif
            </nav>

            <div class="hidden items-center gap-2 sm:flex">
                @if ($whatsappUrl)
                    <a href="#" onclick="openWa('{{ $waEncoded }}')" rel="noopener noreferrer" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700 transition hover:bg-emerald-100">
                        WhatsApp
                    </a>
                @endif
                @if ($hasBooking)
                    <a href="#agendar" class="rounded-full bg-brand px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:opacity-90">
                        Agendar agora
                    </a>
                @endif
            </div>

            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white md:hidden" aria-label="Abrir menu" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-slate-100 bg-white px-4 py-4 md:hidden">
            <div class="grid gap-2 text-sm font-bold text-slate-700">
                <a href="#sobre" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="rounded-2xl px-3 py-2 hover:bg-slate-50">Sobre</a>
                @if ($services->count())
                    <a href="#servicos" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="rounded-2xl px-3 py-2 hover:bg-slate-50">Serviços</a>
                @endif
                @if (($settings['show_team'] ?? true) && $professionals->count())
                    <a href="#equipe" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="rounded-2xl px-3 py-2 hover:bg-slate-50">Equipe</a>
                @endif
                @if ($hasBooking)
                    <a href="#agendar" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="rounded-2xl px-3 py-2 hover:bg-slate-50">Agendar</a>
                @endif
            </div>
        </div>
    </header>

    <main id="inicio">
        <section class="relative overflow-visible bg-slate-50">
            <div class="absolute inset-x-0 top-0 bottom-16 {{ $bannerUrl ? 'gc-hero-image bg-cover bg-top' : 'gc-hero-bg' }}"></div>
            <div class="absolute inset-x-0 top-0 bottom-16 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,.32),transparent_28rem)]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-28 pt-12 sm:px-6 sm:pb-32 sm:pt-16 lg:px-8 lg:pb-36 lg:pt-24">
                <div class="mx-auto flex max-w-6xl flex-col items-center text-center text-white">
                    <div class="mb-6 flex flex-wrap items-center justify-center gap-3">
                        <span class="rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase tracking-[0.24em] text-white ring-1 ring-white/20 backdrop-blur">
                            {{ $nicheName }}
                        </span>
                        @if ($tenant->city)
                            <span class="rounded-full bg-white px-4 py-2 text-xs font-black text-slate-900">
                                {{ $tenant->city }}
                            </span>
                        @endif
                    </div>

                    <h1 class="max-w-6xl text-4xl font-black leading-[1.03] tracking-tight sm:text-5xl lg:text-7xl">
                        {{ $heroTitle !== '' ? $heroTitle : $tenant->name }}
                    </h1>

                    <p class="mt-6 max-w-3xl text-base leading-8 text-white/82 sm:text-lg">
                        {{ $heroSubtitle !== '' ? $heroSubtitle : ($description !== '' ? $description : 'Atendimento profissional, serviços selecionados e agendamento online em poucos passos.') }}
                    </p>

                    <div class="mt-8 flex w-full flex-col justify-center gap-3 sm:w-auto sm:flex-row">
                        @if ($hasBooking)
                            <a href="#agendar" class="inline-flex min-h-14 items-center justify-center rounded-full bg-white px-8 py-4 text-sm font-black text-slate-950 shadow-2xl shadow-black/20 transition hover:-translate-y-0.5">
                                Agende seu horário
                            </a>
                        @endif
                        @if ($whatsappUrl)
                            <a href="#" onclick="openWa('{{ $waEncoded }}')" rel="noopener noreferrer" class="inline-flex min-h-14 items-center justify-center rounded-full border border-white/25 bg-white/10 px-8 py-4 text-sm font-black text-white backdrop-blur transition hover:bg-white/15">
                                Falar no WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="relative z-10 mx-auto -mt-16 max-w-7xl px-4 sm:-mt-20 sm:px-6 lg:px-8">
                <div class="gc-glass rounded-[2rem] border border-white/60 p-4 shadow-soft">
                    <div class="grid gap-3 rounded-[1.5rem] bg-white p-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">✓</span>
                            <div>
                                <p class="text-sm font-black text-slate-950">Perfil verificado</p>
                                <p class="text-xs font-bold text-slate-500">{{ $nicheName }}</p>
                            </div>
                        </div>

                        @if ($location)
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-brand shadow-sm">📍</span>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-950">Localização</p>
                                    <p class="truncate text-xs font-bold text-slate-500">{{ $location }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-brand shadow-sm">{{ $services->count() }}</span>
                            <div>
                                <p class="text-sm font-black text-slate-950">Serviços</p>
                                <p class="text-xs font-bold text-slate-500">opções disponíveis</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-brand shadow-sm">{{ $professionals->count() }}</span>
                            <div>
                                <p class="text-sm font-black text-slate-950">Equipe</p>
                                <p class="text-xs font-bold text-slate-500">profissionais ativos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="sobre" class="py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:gap-0 lg:grid-cols-[2fr_48px_3fr] lg:items-start">

                    {{-- Coluna esquerda: imagem 1:1 --}}
                    @if ($aboutImageUrl)
                        <div class="overflow-hidden rounded-[2rem]">
                            <img src="{{ $aboutImageUrl }}" alt="Sobre {{ $tenant->name }}"
                                 class="aspect-square w-full object-cover">
                        </div>
                    @else
                        <div class="flex aspect-square items-center justify-center rounded-[2rem] bg-slate-100">
                            <span class="text-sm text-slate-400">Imagem "Sobre" não configurada</span>
                        </div>
                    @endif

                    {{-- Divisor vertical --}}
                    <div class="hidden lg:flex lg:items-center lg:justify-center self-stretch">
                        <div class="w-px h-full min-h-[200px]"
                             style="background: linear-gradient(to bottom, transparent, #cbd5e1 20%, #cbd5e1 80%, transparent)">
                        </div>
                    </div>

                    {{-- Coluna direita: label + h2 acima do card --}}
                    <div>
                        <p class="gc-section-label text-sm font-black uppercase tracking-[0.24em]">Sobre a empresa</p>
                        <h2 class="mt-3 mb-6 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                            Atendimento pensado para facilitar sua rotina.
                        </h2>

                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                            <p class="text-base leading-8 text-slate-600">
                                {{ $description !== '' ? $description : 'Conheça os serviços disponíveis, escolha o melhor horário e fale diretamente com a equipe quando precisar.' }}
                            </p>
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                @if ($location)
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">Endereço</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700">{{ $location }}</p>
                                    </div>
                                @endif
                                @if ($tenant->email)
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">E-mail</p>
                                        <p class="mt-1 break-words text-sm font-bold text-slate-700">{{ $tenant->email }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        @if ($services->count())
            <section id="servicos" class="bg-slate-50 py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-10 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <p class="gc-section-label text-sm font-black uppercase tracking-[0.24em]">Serviços</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Serviços em destaque</h2>
                        </div>
                        @if ($hasBooking)
                            <a href="#agendar" class="inline-flex items-center justify-center rounded-full bg-white px-5 py-3 text-sm font-black text-slate-800 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-100">
                                Ver horários
                            </a>
                        @endif
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($services as $service)
                            @php
                                $serviceDescription = trim((string) ($service->description ?? ''));
                                $servicePrice = null;
                                if (($settings['show_prices'] ?? true) && $service->price > 0) {
                                    $servicePrice = 'R$ ' . number_format($service->price, 2, ',', '.');
                                } elseif (($settings['show_prices'] ?? true) && (float) $service->price === 0.0) {
                                    $servicePrice = 'Gratuito';
                                }
                            @endphp
                            <article class="group rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-soft">
                                <div class="mb-5 flex items-start justify-between gap-4">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-lg shadow-slate-950/10" style="background-color: {{ $service->color ?? $primary }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950"
                                        title="Ver mais"
                                        aria-label="Ver descrição completa de {{ $service->name }}"
                                        data-service-name="{{ e($service->name) }}"
                                        data-service-description="{{ e($serviceDescription !== '' ? $serviceDescription : 'Este serviço ainda não possui uma descrição completa cadastrada.') }}"
                                        data-service-duration="{{ e($service->duration_minutes . ' min') }}"
                                        data-service-price="{{ e($servicePrice ?? '') }}"
                                        @click="openServiceModalFromButton($el)"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                    </button>
                                </div>
                                <h3 class="text-lg font-black text-slate-950">{{ $service->name }}</h3>
                                @if ($serviceDescription !== '')
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ \Illuminate\Support\Str::limit($serviceDescription, 120) }}</p>
                                @else
                                    <p class="mt-2 text-sm leading-6 text-slate-500">Clique no olho para ver os detalhes deste serviço.</p>
                                @endif
                                <div class="mt-6 flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600">{{ $service->duration_minutes }} min</span>
                                    @if (($settings['show_prices'] ?? true) && $service->price > 0)
                                        <span class="text-lg font-black text-slate-950">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                                    @elseif (($settings['show_prices'] ?? true) && (float) $service->price === 0.0)
                                        <span class="text-sm font-black text-emerald-600">Gratuito</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (($settings['show_team'] ?? true) && $professionals->count())
            <section id="equipe" class="py-16 sm:py-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto mb-10 max-w-2xl text-center">
                        <p class="gc-section-label text-sm font-black uppercase tracking-[0.24em]">Equipe</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Nossa equipe</h2>
                        <p class="mt-4 text-slate-500">Profissionais disponíveis para atender você.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($professionals as $professional)
                            @php
                                $profAvatar = $professional->avatar_path ? asset('storage/' . $professional->avatar_path) : '';
                                $profColor  = $professional->color ?? $primary;
                                $profBio    = $professional->bio ?? '';
                            @endphp
                            <article class="group relative rounded-[1.75rem] border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-soft">
                                @if ($professional->avatar_path)
                                    <img src="{{ $profAvatar }}"
                                         alt="{{ $professional->name }}"
                                         class="gc-brand-ring mx-auto h-20 w-20 rounded-3xl object-cover">
                                @else
                                    <div class="gc-brand-ring mx-auto flex h-20 w-20 items-center justify-center rounded-3xl text-2xl font-black text-white" style="background-color: {{ $profColor }}">
                                        {{ mb_substr($professional->name, 0, 1) }}
                                    </div>
                                @endif
                                <h3 class="mt-5 text-base font-black text-slate-950">{{ $professional->name }}</h3>
                                @if ($professional->specialty)
                                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $professional->specialty }}</p>
                                @endif

                                <button
                                    type="button"
                                    class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700"
                                    @click="openProfModal({{ Illuminate\Support\Js::from(['name' => $professional->name, 'specialty' => $professional->specialty ?? '', 'bio' => $profBio, 'avatar' => $profAvatar, 'color' => $profColor]) }})"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Visualizar
                                </button>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Modal profissional --}}
            <div
                x-show="profModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="display:none"
            >
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeProfModal()"></div>
                <div
                    x-show="profModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-md rounded-[2rem] bg-white shadow-2xl shadow-black/30 overflow-hidden"
                >
                    {{-- Header colorido --}}
                    <div class="h-24" :style="'background-color: ' + profModal.color + '20'"></div>

                    {{-- Avatar sobreposto --}}
                    <div class="absolute left-1/2 top-10 -translate-x-1/2">
                        <template x-if="profModal.avatar">
                            <img :src="profModal.avatar" :alt="profModal.name" class="h-28 w-28 rounded-3xl object-cover ring-4 ring-white shadow-lg">
                        </template>
                        <template x-if="!profModal.avatar">
                            <div class="h-28 w-28 rounded-3xl ring-4 ring-white shadow-lg flex items-center justify-center text-4xl font-black text-white"
                                 :style="'background-color: ' + profModal.color">
                                <span x-text="profModal.name.charAt(0).toUpperCase()"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Fechar --}}
                    <button type="button" @click="closeProfModal()"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-2xl bg-white/80 backdrop-blur text-slate-500 hover:text-slate-800 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    {{-- Conteúdo --}}
                    <div class="px-8 pb-8 pt-20 text-center">
                        <h3 class="text-2xl font-black text-slate-950" x-text="profModal.name"></h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500" x-text="profModal.specialty" x-show="profModal.specialty"></p>
                        <div class="mt-5 text-sm leading-7 text-slate-600 text-left" x-html="profModal.bio" x-show="profModal.bio"></div>
                        @if ($hasBooking)
                            <a href="#agendar" @click="closeProfModal()"
                               class="mt-6 inline-flex w-full items-center justify-center rounded-full py-3.5 text-sm font-black text-white transition hover:opacity-90"
                               :style="'background-color: ' + profModal.color">
                                Agendar com este profissional
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($hasBooking)
        <section id="agendar" class="bg-slate-950 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
                    <div class="lg:sticky lg:top-28">
                        <p class="text-sm font-black uppercase tracking-[0.24em] text-white/50">Agendamento</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                            Escolha o melhor horário para você.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-white/65">
                            Selecione serviço, profissional e horário em poucos passos.
                        </p>
                        @if ($whatsappUrl)
                            <a href="#" onclick="openWa('{{ $waEncoded }}')" rel="noopener noreferrer" class="mt-7 inline-flex min-h-[3.25rem] items-center justify-center rounded-full bg-emerald-500 px-6 py-4 text-sm font-black text-white transition hover:bg-emerald-600">
                                Falar no WhatsApp
                            </a>
                        @endif
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white text-slate-900 shadow-2xl shadow-black/30">
                        @livewire('booking.booking-wizard', ['tenantId' => $tenant->id])
                    </div>
                </div>
            </div>
        </section>
        @endif
    </main>

    <footer class="bg-white py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <p class="font-black text-slate-950">{{ $tenant->name }}</p>
                <p class="mt-1">
                    © {{ date('Y') }}. Todos os direitos reservados.
                    <span class="mx-1.5 opacity-40">·</span>
                    <a href="{{ route('public.privacy-policy') }}" target="_blank" rel="noopener" class="hover:text-slate-700 hover:underline underline-offset-2">Política de Privacidade</a>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if (!empty($settings['instagram']))
                    <a href="{{ $settings['instagram'] }}" target="_blank" rel="noopener" title="Instagram"
                       class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/></svg>
                    </a>
                @endif
                @if (!empty($settings['facebook']))
                    <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener" title="Facebook"
                       class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/></svg>
                    </a>
                @endif
                @if (!empty($settings['youtube']))
                    <a href="{{ $settings['youtube'] }}" target="_blank" rel="noopener" title="YouTube"
                       class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd"/></svg>
                    </a>
                @endif
                @if (!empty($settings['tiktok']))
                    <a href="{{ $settings['tiktok'] }}" target="_blank" rel="noopener" title="TikTok"
                       class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.77 1.52V6.76a4.85 4.85 0 01-1-.07z"/></svg>
                    </a>
                @endif
                @if (!empty($settings['website']))
                    <a href="{{ $settings['website'] }}" target="_blank" rel="noopener" title="Site"
                       class="flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </footer>

    <div
        x-cloak
        x-show="serviceModalOpen"
        x-transition.opacity
        class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        @click.self="closeServiceModal()"
    >
        <div
            x-show="serviceModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-4 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-4 scale-95 opacity-0"
            class="relative max-h-[88vh] w-full max-w-2xl overflow-y-auto rounded-[2rem] bg-white p-6 shadow-2xl sm:p-8"
        >
            <button
                type="button"
                class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-950"
                aria-label="Fechar modal"
                @click="closeServiceModal()"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="pr-12">
                <p class="gc-section-label text-xs font-black uppercase tracking-[0.24em]">Detalhes do serviço</p>
                <h3 class="mt-3 text-2xl font-black leading-tight text-slate-950 sm:text-3xl" x-text="serviceModal.name"></h3>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-black text-slate-700" x-text="serviceModal.duration"></span>
                <template x-if="serviceModal.price">
                    <span class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700" x-text="serviceModal.price"></span>
                </template>
            </div>

            <div class="mt-7 rounded-3xl bg-slate-50 p-5">
                <p class="whitespace-pre-line text-base leading-8 text-slate-600" x-text="serviceModal.description"></p>
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                @if ($hasBooking)
                    <a href="#agendar" class="inline-flex min-h-12 items-center justify-center rounded-full bg-brand px-6 py-3 text-sm font-black text-white transition hover:opacity-90" @click="closeServiceModal()">
                        Agendar este serviço
                    </a>
                @endif
                <button type="button" class="inline-flex min-h-12 items-center justify-center rounded-full border border-slate-200 px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50" @click="closeServiceModal()">
                    Fechar
                </button>
            </div>
        </div>
    </div>

    @if ($whatsappUrl)
        <a href="#" onclick="openWa('{{ $waEncoded }}')" rel="noopener noreferrer" title="Falar no WhatsApp" class="gc-wa-float fixed right-4 z-50 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl shadow-emerald-950/25 transition hover:-translate-y-1 hover:bg-emerald-600 sm:right-6">
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
            </svg>
        </a>
    @endif

    @livewireScripts
</body>
</html>
