<?php

use App\Http\Controllers\AppointmentConfirmationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarioFrameController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\CityController;
use App\Http\Controllers\Public\CompanyController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\TenantPageController;
use App\Models\Doctor;
use Illuminate\Support\Facades\Route;

// Calendário iframe — restrito ao domínio do painel em produção
$panelGroup = Route::middleware(['auth']);
if ($panelDomain = config('app.panel_domain')) {
    $panelGroup = $panelGroup->domain($panelDomain);
}
$panelGroup->group(function () {
    Route::get('/interno/calendario',                 [CalendarioFrameController::class, 'frame']         )->name('calendario.frame');
    Route::get('/interno/calendario/events',          [CalendarioFrameController::class, 'events']        )->name('calendario.events');
    Route::get('/interno/calendario/options',         [CalendarioFrameController::class, 'options']       )->name('calendario.options');
    Route::post('/interno/calendario/status',         [CalendarioFrameController::class, 'changeStatus']  )->name('calendario.status');
    Route::post('/interno/calendario/reschedule',     [CalendarioFrameController::class, 'reschedule']    )->name('calendario.reschedule');
    Route::post('/interno/calendario/store',          [CalendarioFrameController::class, 'store']         )->name('calendario.store');
    Route::post('/interno/calendario/quick-customer',    [CalendarioFrameController::class, 'quickCustomer']   )->name('calendario.quick-customer');
    Route::post('/interno/calendario/wa-link',            [CalendarioFrameController::class, 'waLink']           )->name('calendario.wa-link');
    Route::post('/interno/calendario/recurring',          [CalendarioFrameController::class, 'createRecurring']  )->name('calendario.recurring');
    Route::post('/interno/calendario/cancel-series',      [CalendarioFrameController::class, 'cancelSeries']     )->name('calendario.cancel-series');
});

// Rotas públicas — restritas ao domínio principal (guiacomercial.app).
// Evita que painel.guiacomercial.app/{slug} responda às mesmas rotas.
$publicGroup = Route::middleware([]);
if ($publicDomain = parse_url(config('app.url'), PHP_URL_HOST)) {
    $publicGroup = $publicGroup->domain($publicDomain);
}
$publicGroup->group(function () {
    // Google OAuth
    Route::get('/auth/google',          [GoogleAuthController::class, 'redirect']      )->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']      )->name('auth.google.callback');
    Route::get('/auth/google/complete', [GoogleAuthController::class, 'showComplete']  )->name('auth.google.complete');
    Route::post('/auth/google/complete',[GoogleAuthController::class, 'saveComplete']  )->name('auth.google.complete.save');

    // Política de Privacidade
    Route::get('/politica-de-privacidade', fn () => view('public.privacy-policy'))->name('public.privacy-policy');

    // Agendamento público
    Route::get('/agendar/{slug}', [BookingController::class, 'show'])->name('booking.show');

    // Confirmação de agendamento via WhatsApp
    Route::get('/confirmar/{token}',         [AppointmentConfirmationController::class, 'show']   )->name('appointment.confirm');
    Route::post('/confirmar/{token}/sim',    [AppointmentConfirmationController::class, 'confirm'] )->name('appointment.do-confirm');
    Route::post('/confirmar/{token}/nao',    [AppointmentConfirmationController::class, 'cancel']  )->name('appointment.do-cancel');

    Route::get('/sitemap.xml', SitemapController::class)->name('public.sitemap');
    Route::get('/', HomeController::class)->name('public.home');
    Route::get('/buscar', SearchController::class)->name('public.search');
    Route::get('/cidades/{city}', [CityController::class, 'show'])->name('public.cities.show');
    Route::get('/servicos/{category}/{city}', [CityController::class, 'category'])->name('public.category.city');
    Route::get('/servicos/{category}', CategoryController::class)->name('public.category');
    Route::get('/empresas/{slug}/agendar', [CompanyController::class, 'booking'])->name('public.companies.booking');
    Route::get('/empresas/{slug}', [CompanyController::class, 'show'])->name('public.companies.show');

    // Catch-all: landing page pública de cada tenant — DEVE SER A ÚLTIMA ROTA
    Route::get('/{slug}', [TenantPageController::class, 'show'])->name('tenant.landing');
});

$weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

// Criar médico de teste e configurar agenda
Route::get('/zap/setup', function () use ($weekdays) {
    $doctor = Doctor::firstOrCreate(
        ['name' => 'Dr. João Silva'],
        ['specialty' => 'Clínica Geral']
    );

    // Apagar agendas anteriores para não duplicar
    $doctor->schedules()->delete();

    $today = now()->format('Y-m-d');

    // Disponibilidade: seg-sex das 08:00 às 18:00
    zap()->for($doctor)
        ->availability()
        ->from($today)
        ->weekDays($weekdays, '08:00', '18:00')
        ->save();

    // Bloqueio: almoço seg-sex das 12:00 às 13:00
    zap()->for($doctor)
        ->blocked()
        ->from($today)
        ->weekly($weekdays)
        ->addPeriod('12:00', '13:00')
        ->save();

    return response()->json([
        'message' => 'Agenda configurada com sucesso!',
        'doctor'  => $doctor,
    ]);
});

// Consultar slots disponíveis para hoje
Route::get('/zap/slots', function () {
    $doctor = Doctor::where('name', 'Dr. João Silva')->first();

    if (! $doctor) {
        return response()->json(['error' => 'Execute /zap/setup primeiro'], 404);
    }

    $date  = now()->format('Y-m-d');
    $slots = $doctor->getBookableSlots(date: $date, slotDuration: 30);

    return response()->json([
        'doctor' => $doctor->name,
        'date'   => $date,
        'slots'  => $slots,
    ]);
});

// Verificar disponibilidade em um horário (ex: /zap/check/09:00/09:30)
Route::get('/zap/check/{start}/{end}', function (string $start, string $end) {
    $doctor = Doctor::where('name', 'Dr. João Silva')->first();

    if (! $doctor) {
        return response()->json(['error' => 'Execute /zap/setup primeiro'], 404);
    }

    $date      = now()->format('Y-m-d');
    $available = $doctor->isBookableAtTime(date: $date, startTime: $start, endTime: $end);

    return response()->json([
        'doctor'    => $doctor->name,
        'date'      => $date,
        'start'     => $start,
        'end'       => $end,
        'available' => $available,
    ]);
});

// Criar um agendamento de teste
Route::get('/zap/book', function () {
    $doctor = Doctor::where('name', 'Dr. João Silva')->first();

    if (! $doctor) {
        return response()->json(['error' => 'Execute /zap/setup primeiro'], 404);
    }

    $slot = $doctor->getNextBookableSlot(afterDate: now()->format('Y-m-d'), duration: 30);

    if (! $slot) {
        return response()->json(['message' => 'Nenhum slot disponível']);
    }

    zap()->for($doctor)
        ->appointment()
        ->on($slot['date'])
        ->addPeriod($slot['start_time'], $slot['end_time'])
        ->withMetadata(['patient' => 'Maria Oliveira', 'reason' => 'Consulta de rotina'])
        ->save();

    return response()->json([
        'message' => 'Consulta agendada!',
        'doctor'  => $doctor->name,
        'slot'    => $slot,
    ]);
});


