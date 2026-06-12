<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agendamento Confirmado</title>
<style>
  body { margin:0; padding:0; background:#f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color:#374151; }
  .wrapper { max-width:560px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
  .header { background:#7c3aed; padding:32px 40px; text-align:center; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; }
  .header p { margin:4px 0 0; color:#ddd6fe; font-size:14px; }
  .body { padding:32px 40px; }
  .greeting { font-size:16px; font-weight:600; color:#111827; margin-bottom:8px; }
  .lead { font-size:14px; color:#6b7280; margin-bottom:24px; }
  .card { background:#f5f3ff; border:1px solid #ddd6fe; border-radius:8px; padding:20px; margin-bottom:24px; }
  .card-row { display:flex; gap:12px; margin-bottom:10px; font-size:14px; }
  .card-row:last-child { margin-bottom:0; }
  .card-icon { width:20px; text-align:center; flex-shrink:0; }
  .card-label { color:#6b7280; min-width:90px; }
  .card-value { color:#1f2937; font-weight:500; }
  .protocol { text-align:center; font-size:12px; color:#9ca3af; margin-bottom:24px; }
  .protocol strong { color:#7c3aed; }
  .footer { background:#f9fafb; border-top:1px solid #e5e7eb; padding:20px 40px; text-align:center; }
  .footer p { margin:0; font-size:12px; color:#9ca3af; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $appointment->tenant->name }}</h1>
    <p>Agendamento confirmado ✓</p>
  </div>

  <div class="body">
    <p class="greeting">Olá, {{ $appointment->customer->name }}!</p>
    <p class="lead">
      Seu agendamento foi recebido com sucesso. Veja os detalhes abaixo:
    </p>

    <div class="card">
      <div class="card-row">
        <span class="card-icon">📋</span>
        <span class="card-label">Serviço</span>
        <span class="card-value">{{ $appointment->service->name }}</span>
      </div>
      <div class="card-row">
        <span class="card-icon">👤</span>
        <span class="card-label">Profissional</span>
        <span class="card-value">{{ $appointment->professional->name }}</span>
      </div>
      <div class="card-row">
        <span class="card-icon">📅</span>
        <span class="card-label">Data</span>
        <span class="card-value">
          {{ $appointment->date->translatedFormat('d \d\e F \d\e Y') }}
        </span>
      </div>
      <div class="card-row">
        <span class="card-icon">⏰</span>
        <span class="card-label">Horário</span>
        <span class="card-value">
          {{ substr($appointment->start_time, 0, 5) }} às {{ substr($appointment->end_time, 0, 5) }}
        </span>
      </div>
      @if($appointment->service->price)
      <div class="card-row">
        <span class="card-icon">💰</span>
        <span class="card-label">Valor</span>
        <span class="card-value">R$ {{ number_format($appointment->service->price, 2, ',', '.') }}</span>
      </div>
      @endif
    </div>

    <p class="protocol">
      Protocolo: <strong>#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</strong>
    </p>

    @if($appointment->notes)
    <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">
      <strong>Observação:</strong> {{ $appointment->notes }}
    </p>
    @endif

    <p style="font-size:13px;color:#6b7280;margin:0;">
      Em caso de dúvidas ou para reagendar, entre em contato conosco.
    </p>
  </div>

  <div class="footer">
    <p>{{ $appointment->tenant->name }} &mdash; {{ $appointment->tenant->city }}</p>
    <p style="margin-top:4px;">Este é um e-mail automático. Por favor, não responda.</p>
  </div>
</div>
</body>
</html>
