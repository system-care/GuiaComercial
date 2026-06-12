<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} — Agendamento Online</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen font-sans antialiased">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-2xl mx-auto px-4 py-5 text-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h1>
            @if($tenant->city)
                <p class="text-sm text-gray-500 mt-0.5">{{ $tenant->city }}</p>
            @endif
        </div>
    </header>

    {{-- Wizard --}}
    <main class="max-w-2xl mx-auto px-4 py-8">
        @livewire('booking.booking-wizard', ['tenantId' => $tenant->id])
    </main>

    <footer class="text-center text-xs text-gray-400 py-6">
        &copy; {{ date('Y') }} Guia Comercial. Todos os direitos reservados.
    </footer>

    @livewireScripts
</body>
</html>
