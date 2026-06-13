<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $metaTitle       = $title       ?? 'Guia Comercial';
        $metaDesc        = $description ?? 'Encontre empresas, profissionais e serviços locais com agendamento online.';
        $metaCanonical   = $canonical   ?? request()->url();
        $metaRobots      = $robots      ?? 'index,follow';
        $metaOgType      = $ogType      ?? 'website';
        $metaOgImage     = $ogImage     ?? null;
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <link rel="canonical" href="{{ $metaCanonical }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="{{ $metaOgType }}">
    <meta property="og:site_name"   content="Guia Comercial">
    <meta property="og:title"       content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url"         content="{{ $metaCanonical }}">
    @if ($metaOgImage)
    <meta property="og:image"       content="{{ $metaOgImage }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    @if ($metaOgImage)
    <meta name="twitter:image"       content="{{ $metaOgImage }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    <style>
        *, ::before, ::after { min-width: 0; }
        h1, h2, h3 { overflow-wrap: anywhere; }
        img, svg, video { max-width: 100%; }
    </style>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('logo/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/favicon.png') }}">

    @stack('jsonld')
</head>
<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900 antialiased">
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm shadow-slate-200/40 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2" aria-label="Ir para a home do Guia Comercial">
                <img src="{{ asset('logo/logo.png') }}"
                     srcset="{{ asset('logo/logo_head.png') }} 1x, {{ asset('logo/logo.png') }} 2x"
                     alt="Guia Comercial"
                     class="h-9 w-auto"
                     loading="eager"
                     width="165" height="36">
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navegação principal">
                <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500">Home</a>
                <a href="{{ url('/buscar') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500">Buscar Serviços</a>
                <a href="{{ url('/servicos/clinicas') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500">Categorias</a>
                <a href="{{ $panelLoginUrl }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-violet-500">Entrar</a>
                <a href="{{ $panelRegisterUrl }}" class="ml-2 rounded-xl bg-violet-600 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">Cadastrar Empresa</a>
            </nav>

            <button
                type="button"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden"
                aria-controls="mobile-menu"
                aria-expanded="false"
                aria-label="Abrir menu"
                onclick="const menu = document.getElementById('mobile-menu'); const opened = menu.classList.toggle('hidden') === false; this.setAttribute('aria-expanded', opened ? 'true' : 'false');"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-slate-100 bg-white px-4 py-4 lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-2" aria-label="Navegação mobile">
                <a href="{{ url('/') }}" class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Home</a>
                <a href="{{ url('/buscar') }}" class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Buscar Serviços</a>
                <a href="{{ url('/servicos/clinicas') }}" class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Categorias</a>
                <a href="{{ $panelLoginUrl }}" class="rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Entrar</a>
                <a href="{{ $panelRegisterUrl }}" class="mt-1 rounded-xl bg-violet-600 px-4 py-3 text-center text-sm font-black text-white shadow-sm">Cadastrar Empresa</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr] lg:px-8">
            <div>
                <div class="flex items-center">
                    <img src="{{ asset('logo/logo.png') }}"
                         srcset="{{ asset('logo/logo_head.png') }} 1x, {{ asset('logo/logo.png') }} 2x"
                         alt="Guia Comercial"
                         class="h-8 w-auto"
                         width="146" height="32">
                </div>
                <p class="mt-4 max-w-md text-sm leading-6 text-slate-600">Diretório local para encontrar serviços, conhecer empresas e iniciar agendamentos online com praticidade.</p>
            </div>
            <div>
                <h2 class="text-sm font-black text-slate-950">Navegação</h2>
                <div class="mt-3 flex flex-col gap-2 text-sm text-slate-600">
                    <a href="{{ url('/buscar') }}" class="hover:text-violet-700">Buscar serviços</a>
                    <a href="{{ url('/servicos/estetica') }}" class="hover:text-violet-700">Categorias</a>
                    <a href="{{ $panelLoginUrl }}" class="hover:text-violet-700">Área da empresa</a>
                </div>
            </div>
            <div>
                <h2 class="text-sm font-black text-slate-950">Para empresas</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">Publique seu perfil, apresente seus serviços e receba solicitações de agendamento.</p>
                <a href="{{ $panelRegisterUrl }}" class="mt-4 inline-flex rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white hover:bg-slate-800">Cadastrar empresa</a>
            </div>
        </div>
        <div class="border-t border-slate-100 px-4 py-4 text-center text-xs text-slate-500">
            © {{ date('Y') }} Guia Comercial. Todos os direitos reservados.
            <span class="mx-2 text-slate-300">·</span>
            <a href="{{ url('/politica-de-privacidade') }}" class="hover:text-violet-600 hover:underline">Política de Privacidade</a>
        </div>
    </footer>
    @livewireScripts
</body>
</html>
