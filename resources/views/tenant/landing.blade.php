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
        $whatsappUrl = $cleanWhatsapp ? 'https://wa.me/' . $cleanWhatsapp : null;
        $hasBooking = (bool) ($settings['allow_online_booking'] ?? true);
        $description = trim((string) ($settings['description'] ?? ''));
        $heroTitle = trim((string) ($settings['hero_title'] ?? ''));
        $heroSubtitle = trim((string) ($settings['hero_subtitle'] ?? ''));
        $address = trim((string) ($settings['address'] ?? ''));
        $location = $address !== '' ? $address : $tenant->city;
        $logoUrl = !empty($settings['logo_path']) ? asset('storage/' . $settings['logo_path']) : null;
        $bannerUrl = !empty($settings['banner_path']) ? asset('storage/' . $settings['banner_path']) : null;
        $nicheName = $tenant->niche->name ?? 'Empresa verificada';
    @endphp

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: 'var(--brand)',
                        brandDark: 'var(--brand-dark)',
                    },
                    boxShadow: {
                        soft: '0 24px 70px rgba(15, 23, 42, 0.10)',
                    },
                },
            },
        };
    </script>
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
        closeServiceModal() {
            this.serviceModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
    }"
    @keydown.escape.window="closeServiceModal()"
>
    <header class="sticky top-0 z-40 border-b border-white/70 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="#inicio" class="flex min-w-0 items-center gap-3">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $tenant->name }}" class="h-11 w-11 rounded-2xl object-contain ring-1 ring-slate-200">
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand text-lg font-black text-white">
                        {{ mb_substr($tenant->name, 0, 1) }}
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-sm font-black text-slate-950 sm:text-base">{{ $tenant->name }}</span>
                    <span class="hidden truncate text-xs font-semibold text-slate-500 sm:block">{{ $nicheName }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-bold text-slate-600 md:flex">
                <a href="#sobre" class="hover:text-slate-950">Sobre</a>
                @if ($services->count())
                    <a href="#servicos" class="hover:text-slate-950">Serviços</a>
                @endif
                @if (($settings['show_team'] ?? true) && $professionals->count())
                    <a href="#equipe" class="hover:text-slate-950">Equipe</a>
                @endif
                <a href="#agendar" class="hover:text-slate-950">Agendar</a>
            </nav>

            <div class="hidden items-center gap-2 sm:flex">
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700 transition hover:bg-emerald-100">
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
                <a href="#agendar" onclick="document.getElementById('mobile-menu').classList.add('hidden')" class="rounded-2xl px-3 py-2 hover:bg-slate-50">Agendar</a>
            </div>
        </div>
    </header>

    <main id="inicio">
        <section class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[72%] {{ $bannerUrl ? 'gc-hero-image bg-cover bg-center' : 'gc-hero-bg' }}"></div>
            <div class="absolute inset-x-0 top-0 h-[72%] bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,.32),transparent_28rem)]"></div>

            <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8 lg:pb-24 lg:pt-24">
                <div class="mx-auto flex max-w-4xl flex-col items-center text-center text-white">
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

                    <h1 class="max-w-4xl text-4xl font-black leading-[1.03] tracking-tight sm:text-5xl lg:text-7xl">
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
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="inline-flex min-h-14 items-center justify-center rounded-full border border-white/25 bg-white/10 px-8 py-4 text-sm font-black text-white backdrop-blur transition hover:bg-white/15">
                                Falar no WhatsApp
                            </a>
                        @endif
                    </div>
                </div>

                <div class="gc-glass mt-12 rounded-[2rem] border border-white/60 p-4 shadow-soft">
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
                <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
                    <div>
                        <p class="gc-section-label text-sm font-black uppercase tracking-[0.24em]">Sobre a empresa</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                            Atendimento pensado para facilitar sua rotina.
                        </h2>
                    </div>
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
                                        @click='openServiceModal(@js([
                                            'name' => $service->name,
                                            'description' => $serviceDescription !== '' ? $serviceDescription : 'Este serviço ainda não possui uma descrição completa cadastrada.',
                                            'duration' => $service->duration_minutes . ' min',
                                            'price' => $servicePrice,
                                        ]))'
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
                            <article class="rounded-[1.75rem] border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-soft">
                                <div class="gc-brand-ring mx-auto flex h-20 w-20 items-center justify-center rounded-3xl text-2xl font-black text-white" style="background-color: {{ $professional->color ?? $primary }}">
                                    {{ mb_substr($professional->name, 0, 1) }}
                                </div>
                                <h3 class="mt-5 text-base font-black text-slate-950">{{ $professional->name }}</h3>
                                @if ($professional->specialty)
                                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $professional->specialty }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section id="agendar" class="bg-slate-950 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
                    <div class="lg:sticky lg:top-28">
                        <p class="text-sm font-black uppercase tracking-[0.24em] text-white/50">Agendamento</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                            Escolha o melhor horário para você.
                        </h2>
                        <p class="mt-5 text-base leading-8 text-white/65">
                            {{ $hasBooking ? 'Selecione serviço, profissional e horário em poucos passos.' : 'No momento, esta empresa atende agendamentos por contato direto.' }}
                        </p>
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="mt-7 inline-flex min-h-[3.25rem] items-center justify-center rounded-full bg-emerald-500 px-6 py-4 text-sm font-black text-white transition hover:bg-emerald-600">
                                Falar no WhatsApp
                            </a>
                        @endif
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white text-slate-900 shadow-2xl shadow-black/30">
                        @if ($hasBooking)
                            @livewire('booking.booking-wizard', ['tenantId' => $tenant->id])
                        @else
                            <div class="p-8 text-center sm:p-12">
                                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-3xl">☎</div>
                                <h3 class="text-2xl font-black text-slate-950">Agendamento online indisponível</h3>
                                <p class="mt-3 text-slate-500">Entre em contato diretamente para combinar o melhor horário.</p>
                                @if ($whatsappUrl)
                                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="mt-6 inline-flex min-h-[3.25rem] items-center justify-center rounded-full bg-brand px-7 py-4 text-sm font-black text-white">
                                        Chamar no WhatsApp
                                    </a>
                                @elseif ($tenant->phone)
                                    <a href="tel:{{ $tenant->phone }}" class="mt-6 inline-flex min-h-[3.25rem] items-center justify-center rounded-full bg-brand px-7 py-4 text-sm font-black text-white">
                                        {{ $tenant->phone }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <p class="font-black text-slate-950">{{ $tenant->name }}</p>
                <p class="mt-1">© {{ date('Y') }}. Todos os direitos reservados.</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 font-bold">
                @if (!empty($settings['instagram']))
                    <a href="{{ $settings['instagram'] }}" target="_blank" rel="noopener" class="hover:text-slate-950">Instagram</a>
                @endif
                @if (!empty($settings['facebook']))
                    <a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener" class="hover:text-slate-950">Facebook</a>
                @endif
                @if (!empty($settings['website']))
                    <a href="{{ $settings['website'] }}" target="_blank" rel="noopener" class="hover:text-slate-950">Site</a>
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
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" title="Falar no WhatsApp" class="gc-wa-float fixed right-4 z-50 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500 text-white shadow-2xl shadow-emerald-950/25 transition hover:-translate-y-1 hover:bg-emerald-600 sm:right-6">
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347M12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
            </svg>
        </a>
    @endif

    @livewireScripts
</body>
</html>
