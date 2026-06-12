<?php

namespace App\Livewire\Booking;

use App\Jobs\SendWhatsAppConfirmationJob;
use App\Mail\AppointmentConfirmed;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\SchedulingService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class BookingWizard extends Component
{
    public int $tenantId;
    public int $step = 1;

    // Step 1 — Serviço
    public ?int $selectedServiceId = null;

    // Step 2 — Profissional + data + slot
    public ?int $selectedProfessionalId = null;
    public string $selectedDate = '';
    public string $selectedSlotKey = '';
    public array $slots = [];
    public bool $loadingSlots = false;
    public bool $noSlots = false;

    // Step 3 — Dados do cliente
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerEmail = '';
    public string $notes = '';

    // Step 4 — Confirmação
    public ?int $appointmentId = null;

    protected $rules = [
        'selectedServiceId'      => 'required|integer',
        'selectedProfessionalId' => 'required|integer',
        'selectedDate'           => 'required|date',
        'selectedSlotKey'        => 'required|string',
        'customerName'           => 'required|min:3|max:255',
        'customerPhone'          => 'required|min:8|max:20',
        'customerEmail'          => 'nullable|email|max:255',
    ];

    public function mount(int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function getTenantProperty(): Tenant
    {
        return Tenant::findOrFail($this->tenantId);
    }

    public function getServicesProperty()
    {
        return Service::where('tenant_id', $this->tenantId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getProfessionalsProperty()
    {
        return Professional::where('tenant_id', $this->tenantId)
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedServiceProperty(): ?Service
    {
        return $this->selectedServiceId
            ? Service::find($this->selectedServiceId)
            : null;
    }

    public function selectService(int $serviceId): void
    {
        $this->selectedServiceId = $serviceId;
        $this->step = 2;
        $this->loadSlots();
    }

    public function updatedSelectedProfessionalId(): void
    {
        $this->selectedSlotKey = '';
        $this->loadSlots();
    }

    public function updatedSelectedDate(): void
    {
        $this->selectedSlotKey = '';
        $this->loadSlots();
    }

    public function loadSlots(): void
    {
        $this->slots = [];
        $this->noSlots = false;

        if (! $this->selectedProfessionalId || ! $this->selectedDate || ! $this->selectedServiceId) {
            return;
        }

        $professional = Professional::find($this->selectedProfessionalId);
        $service      = Service::find($this->selectedServiceId);

        if (! $professional || ! $service) {
            return;
        }

        $scheduling = app(SchedulingService::class);
        $slots      = $scheduling->getAvailableSlots($professional, $this->selectedDate, $service->duration_minutes);

        $available = array_filter($slots, fn ($s) => $s['is_available'] ?? false);

        $this->slots   = array_values($available);
        $this->noSlots = count($this->slots) === 0;
    }

    public function selectSlot(string $slotKey): void
    {
        $this->selectedSlotKey = $slotKey;
    }

    public function goToStep3(): void
    {
        $this->validate([
            'selectedProfessionalId' => 'required|integer',
            'selectedDate'           => 'required|date',
            'selectedSlotKey'        => 'required|string',
        ]);

        $this->step = 3;
    }

    public function confirm(): void
    {
        $this->validate([
            'customerName'  => 'required|min:3|max:255',
            'customerPhone' => 'required|min:8|max:20',
            'customerEmail' => 'nullable|email|max:255',
        ]);

        [$startTime, $endTime] = explode('|', $this->selectedSlotKey);

        $professional = Professional::find($this->selectedProfessionalId);
        $service      = Service::find($this->selectedServiceId);
        $scheduling   = app(SchedulingService::class);

        $booked = $scheduling->book(
            professional: $professional,
            service: $service,
            date: $this->selectedDate,
            startTime: $startTime,
            endTime: $endTime,
        );

        if (! $booked) {
            $this->addError('selectedSlotKey', 'Este horário não está mais disponível. Escolha outro.');
            $this->step = 2;
            $this->loadSlots();
            return;
        }

        $customer = Customer::firstOrCreate(
            ['tenant_id' => $this->tenantId, 'phone' => $this->customerPhone],
            ['name'  => $this->customerName, 'email' => $this->customerEmail ?: null]
        );

        $appointment = Appointment::create([
            'tenant_id'       => $this->tenantId,
            'customer_id'     => $customer->id,
            'service_id'      => $this->selectedServiceId,
            'professional_id' => $this->selectedProfessionalId,
            'date'            => $this->selectedDate,
            'start_time'      => $startTime,
            'end_time'        => $endTime,
            'status'          => 'pending',
            'notes'           => $this->notes ?: null,
        ]);

        $this->appointmentId = $appointment->id;

        if ($customer->email) {
            Mail::to($customer->email)->send(new AppointmentConfirmed($appointment->load(['tenant', 'customer', 'service', 'professional'])));
        }

        SendWhatsAppConfirmationJob::dispatch($appointment->id)->delay(5);

        $this->step = 4;
    }

    public function restart(): void
    {
        $this->reset([
            'step', 'selectedServiceId', 'selectedProfessionalId',
            'selectedDate', 'selectedSlotKey', 'slots', 'noSlots',
            'customerName', 'customerPhone', 'customerEmail', 'notes',
            'appointmentId',
        ]);
        $this->selectedDate = now()->format('Y-m-d');
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.booking.booking-wizard');
    }
}
