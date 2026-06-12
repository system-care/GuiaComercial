<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'success' ? 'Confirmado' : ($type === 'cancelled' ? 'Cancelado' : 'Aviso') }} — Agendamento</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gray-50">
    <div class="w-full max-w-sm text-center">
        @if($type === 'success')
            <div class="text-6xl mb-4">✅</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Confirmado!</h1>
        @elseif($type === 'cancelled')
            <div class="text-6xl mb-4">😔</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Cancelado</h1>
        @else
            <div class="text-6xl mb-4">⚠️</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Atenção</h1>
        @endif

        <p class="text-gray-600 mb-6">{{ $message }}</p>

        @if(isset($appointment) && $type === 'success')
            <div class="bg-white rounded-xl shadow p-4 text-left text-sm text-gray-700 mb-6">
                <p><span class="font-semibold">Data:</span> {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y') }}</p>
                <p><span class="font-semibold">Horário:</span> {{ substr($appointment->start_time, 0, 5) }}</p>
                @if(isset($appointment->service) && $appointment->service)
                    <p><span class="font-semibold">Serviço:</span> {{ $appointment->service->name }}</p>
                @endif
                <p><span class="font-semibold">Local:</span> {{ $appointment->tenant->name ?? '' }}</p>
            </div>
        @endif

        @if($type === 'cancelled' && isset($appointment))
            @php $phone = $appointment->tenant->phone ?? null; @endphp
            @if($phone)
                <a href="https://wa.me/55{{ preg_replace('/\D/','',$phone) }}"
                   class="inline-block bg-green-500 text-white font-semibold px-5 py-2.5 rounded-xl text-sm hover:bg-green-600 transition">
                    Remarcar pelo WhatsApp
                </a>
            @endif
        @endif

        <p class="text-xs text-gray-400 mt-8">&copy; {{ date('Y') }} Guia Comercial. Todos os direitos reservados.</p>
    </div>
</body>
</html>
