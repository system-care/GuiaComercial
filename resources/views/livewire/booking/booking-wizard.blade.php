<div>

    {{-- Indicador de progresso --}}
    @if($step < 4)
    <div class="flex items-center justify-center gap-2 mb-8">
        @foreach([1 => 'Serviço', 2 => 'Horário', 3 => 'Seus dados'] as $n => $label)
            <div class="flex items-center gap-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold
                    {{ $step >= $n ? 'bg-violet-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                    {{ $n }}
                </div>
                <span class="text-sm {{ $step >= $n ? 'text-violet-700 font-medium' : 'text-gray-400' }} hidden sm:inline">
                    {{ $label }}
                </span>
            </div>
            @if($n < 3)
                <div class="w-8 h-px {{ $step > $n ? 'bg-violet-400' : 'bg-gray-300' }}"></div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- ====== STEP 1: Selecionar serviço ====== --}}
    @if($step === 1)
    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Qual serviço você deseja?</h2>

        @if($this->services->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Nenhum serviço disponível no momento.</p>
            </div>
        @else
            <div class="grid gap-3">
                @foreach($this->services as $service)
                <button wire:click="selectService({{ $service->id }})"
                        class="w-full text-left p-4 bg-white border-2 rounded-xl hover:border-violet-500 hover:shadow-md
                               transition-all duration-150 cursor-pointer group
                               {{ $selectedServiceId === $service->id ? 'border-violet-500 shadow-md' : 'border-gray-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $service->color }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 group-hover:text-violet-700">{{ $service->name }}</div>
                            @if($service->description)
                                <div class="text-sm text-gray-500 truncate">{{ $service->description }}</div>
                            @endif
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <div class="text-sm font-medium text-gray-700">{{ $service->duration_minutes }} min</div>
                            @if($service->price)
                                <div class="text-sm text-violet-600 font-semibold">
                                    R$ {{ number_format($service->price, 2, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- ====== STEP 2: Profissional + Data + Slot ====== --}}
    @if($step === 2)
    <div>
        <button wire:click="$set('step', 1)" class="flex items-center gap-1 text-sm text-violet-600 hover:text-violet-800 mb-4">
            ← Voltar
        </button>

        <h2 class="text-xl font-semibold text-gray-800 mb-4">Escolha data, profissional e horário</h2>

        {{-- Profissional --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Profissional</label>
            @if($this->professionals->isEmpty())
                <p class="text-sm text-gray-500">Nenhum profissional disponível.</p>
            @else
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($this->professionals as $prof)
                    <button wire:click="$set('selectedProfessionalId', {{ $prof->id }})"
                            class="flex items-center gap-3 p-3 rounded-lg border-2 text-left transition-all
                                   {{ $selectedProfessionalId === $prof->id
                                       ? 'border-violet-500 bg-violet-50'
                                       : 'border-gray-200 hover:border-violet-300' }}">
                        <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold"
                             style="background-color: {{ $prof->color }}">
                            {{ strtoupper(substr($prof->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $prof->name }}</div>
                            @if($prof->specialty)
                                <div class="text-xs text-gray-500">{{ $prof->specialty }}</div>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Data --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
            <input type="date"
                   wire:model.live="selectedDate"
                   min="{{ now()->format('Y-m-d') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm p-2 border">
        </div>

        {{-- Slots --}}
        @if($selectedProfessionalId && $selectedDate)
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">Horários disponíveis</label>

            @if($noSlots)
                <p class="text-sm text-gray-500 text-center py-4">
                    Nenhum horário disponível nessa data. Tente outro dia ou profissional.
                </p>
            @elseif(empty($slots))
                <p class="text-sm text-gray-400 text-center py-4">Selecione profissional e data para ver os horários.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach($slots as $slot)
                        @php $key = $slot['start_time'] . '|' . $slot['end_time']; @endphp
                        <button wire:click="selectSlot('{{ $key }}')"
                                class="py-2 px-1 rounded-lg text-center text-sm font-medium border-2 transition-all
                                       {{ $selectedSlotKey === $key
                                           ? 'border-violet-600 bg-violet-600 text-white'
                                           : 'border-gray-200 text-gray-700 hover:border-violet-400 hover:bg-violet-50' }}">
                            {{ substr($slot['start_time'], 0, 5) }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        @error('selectedSlotKey')
            <p class="text-sm text-red-600 mb-3">{{ $message }}</p>
        @enderror

        <button wire:click="goToStep3"
                @if(!$selectedProfessionalId || !$selectedDate || !$selectedSlotKey) disabled @endif
                class="w-full py-3 rounded-xl font-semibold text-white transition-all
                       {{ $selectedProfessionalId && $selectedDate && $selectedSlotKey
                           ? 'bg-violet-600 hover:bg-violet-700'
                           : 'bg-gray-300 cursor-not-allowed' }}">
            Continuar →
        </button>
    </div>
    @endif

    {{-- ====== STEP 3: Dados do cliente ====== --}}
    @if($step === 3)
    <div>
        <button wire:click="$set('step', 2)" class="flex items-center gap-1 text-sm text-violet-600 hover:text-violet-800 mb-4">
            ← Voltar
        </button>

        {{-- Resumo do agendamento --}}
        @php
            [$start, $end] = explode('|', $selectedSlotKey ?: '|');
            $service    = $this->selectedService;
            $profName   = $this->professionals->find($selectedProfessionalId)?->name;
        @endphp
        <div class="bg-violet-50 border border-violet-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-violet-800 mb-1">Resumo do agendamento</p>
            <p class="text-sm text-violet-700">
                📋 {{ $service?->name }} ({{ $service?->duration_minutes }} min)
            </p>
            <p class="text-sm text-violet-700">
                👤 {{ $profName }}
            </p>
            <p class="text-sm text-violet-700">
                📅 {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d \d\e F \d\e Y') }}
                · {{ substr($start, 0, 5) }} às {{ substr($end, 0, 5) }}
            </p>
        </div>

        <h2 class="text-xl font-semibold text-gray-800 mb-4">Seus dados</h2>

        <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
                <input type="text" wire:model="customerName" placeholder="Seu nome"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm p-2 border">
                @error('customerName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone / WhatsApp *</label>
                <input type="tel" wire:model="customerPhone" placeholder="(11) 99999-9999"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm p-2 border">
                @error('customerPhone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail (opcional)</label>
                <input type="email" wire:model="customerEmail" placeholder="seu@email.com"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm p-2 border">
                @error('customerEmail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações (opcional)</label>
                <textarea wire:model="notes" rows="2" placeholder="Alguma observação para o profissional?"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm p-2 border"></textarea>
            </div>
        </div>

        <button wire:click="confirm" wire:loading.attr="disabled"
                class="w-full mt-4 py-3 rounded-xl font-semibold text-white bg-violet-600 hover:bg-violet-700 transition-all">
            <span wire:loading.remove wire:target="confirm">Confirmar agendamento</span>
            <span wire:loading wire:target="confirm">Aguarde…</span>
        </button>
    </div>
    @endif

    {{-- ====== STEP 4: Confirmação ====== --}}
    @if($step === 4)
    <div class="text-center py-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Agendamento confirmado!</h2>
        <p class="text-gray-600 mb-2">Obrigado, <strong>{{ $customerName }}</strong>!</p>
        <p class="text-gray-500 text-sm mb-6">
            Seu agendamento foi registrado. Aguarde a confirmação via telefone.
        </p>

        @php
            [$start, $end] = explode('|', $selectedSlotKey ?: '|');
            $service    = $this->selectedService;
            $profName   = $this->professionals->find($selectedProfessionalId)?->name;
        @endphp
        <div class="bg-violet-50 border border-violet-200 rounded-xl p-5 text-left max-w-sm mx-auto mb-6">
            <div class="space-y-2 text-sm text-violet-800">
                <div class="flex gap-2"><span>📋</span><span>{{ $service?->name }}</span></div>
                <div class="flex gap-2"><span>👤</span><span>{{ $profName }}</span></div>
                <div class="flex gap-2"><span>📅</span>
                    <span>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>
                <div class="flex gap-2"><span>⏰</span>
                    <span>{{ substr($start, 0, 5) }} às {{ substr($end, 0, 5) }}</span>
                </div>
                @if($appointmentId)
                <div class="flex gap-2 text-xs text-violet-600 mt-1">
                    <span>#</span><span>Protocolo {{ str_pad($appointmentId, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                @endif
            </div>
        </div>

        <button wire:click="restart"
                class="py-2 px-6 rounded-lg border border-violet-600 text-violet-600 hover:bg-violet-50 text-sm font-medium transition-all">
            Fazer outro agendamento
        </button>
    </div>
    @endif

</div>
