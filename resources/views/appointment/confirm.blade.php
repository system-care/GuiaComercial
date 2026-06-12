<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Agendamento — {{ $appointment->tenant->name ?? 'Agendamento' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Logo / nome da empresa --}}
        <div class="text-center mb-6">
            @php
                $settings = $appointment->tenant->settings?->settings ?? [];
                $logo = $settings['logo_path'] ?? null;
            @endphp
            @if($logo)
                <img src="{{ asset('storage/' . $logo) }}" alt="{{ $appointment->tenant->name }}" class="h-12 mx-auto mb-3 object-contain">
            @endif
            <p class="text-sm text-gray-500">{{ $appointment->tenant->name }}</p>
        </div>

        @php
            $address = $settings['address'] ?? null;
        @endphp

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-indigo-600 px-6 py-5 text-white">
                <h1 class="text-xl font-bold">Confirmar Agendamento</h1>
                <p class="text-indigo-200 text-sm mt-1">Olá, {{ $appointment->customer->name }}!</p>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">

                    {{-- Data --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Data</p>
                            <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    {{-- Horário --}}
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Horário</p>
                            <p class="font-semibold text-gray-800">{{ substr($appointment->start_time, 0, 5) }}</p>
                        </div>
                    </div>

                    {{-- Serviço --}}
                    @if($appointment->service)
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Serviço</p>
                            <p class="font-semibold text-gray-800">{{ $appointment->service->name }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Profissional --}}
                    @if($appointment->professional)
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Profissional</p>
                            <p class="font-semibold text-gray-800">{{ $appointment->professional->name }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Endereço --}}
                    @if($address)
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Endereço</p>
                            <p class="font-semibold text-gray-800">{{ $address }}</p>
                        </div>
                    </div>
                    @endif

                </div>

                @if(in_array($appointment->status, ['confirmed']))
                    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-center">
                        <p class="text-green-700 font-semibold text-sm">✅ Agendamento já confirmado!</p>
                    </div>
                @elseif(in_array($appointment->status, ['cancelled', 'no_show']))
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-center">
                        <p class="text-red-700 font-semibold text-sm">Este agendamento foi cancelado.</p>
                    </div>
                @else
                    <p class="text-sm text-gray-600 text-center">Você vai comparecer?</p>

                    <form method="POST" action="{{ route('appointment.do-confirm', $token) }}">
                        @csrf
                        <button type="submit"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-xl transition text-sm">
                            ✅ Sim, vou comparecer
                        </button>
                    </form>

                    <form method="POST" action="{{ route('appointment.do-cancel', $token) }}">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Tem certeza que deseja cancelar?')"
                            class="w-full bg-white hover:bg-red-50 text-red-500 border border-red-200 font-semibold py-3 px-4 rounded-xl transition text-sm mt-2">
                            ❌ Não poderei comparecer
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} Guia Comercial. Todos os direitos reservados.
        </p>
    </div>
</body>
</html>
