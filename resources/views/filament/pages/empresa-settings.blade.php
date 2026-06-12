<x-filament-panels::page>
    <style>
        [x-cloak] { display: none !important; }

        .gc-wa-admin-note,
        .gc-wa-card {
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }

        .gc-wa-admin-note {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-color: #c4b5fd;
            background: #f5f3ff;
            color: #5b21b6;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .gc-wa-card {
            margin-top: 1.5rem;
            overflow: hidden;
        }

        .gc-wa-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .gc-wa-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #111827;
        }

        .gc-wa-admin-badge,
        .gc-wa-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 999px;
            padding: 0.1875rem 0.75rem;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .gc-wa-admin-badge {
            background: #ede9fe;
            color: #6d28d9;
        }

        .gc-wa-body {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            padding: 1rem 1.25rem;
        }

        .gc-wa-muted {
            margin: 0;
            color: #64748b;
            font-size: 0.75rem;
        }

        .gc-wa-code {
            border-radius: 0.375rem;
            background: #f1f5f9;
            color: #4f46e5;
            padding: 0.125rem 0.375rem;
            font-size: 0.75rem;
        }

        .gc-wa-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
        }

        .gc-wa-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            display: inline-block;
        }

        .gc-wa-status-open {
            background: #dcfce7;
            color: #166534;
        }

        .gc-wa-status-connecting {
            background: #fef9c3;
            color: #854d0e;
        }

        .gc-wa-status-close {
            background: #fee2e2;
            color: #991b1b;
        }

        .gc-wa-status-unknown {
            background: #f1f5f9;
            color: #64748b;
        }

        .gc-wa-link-button {
            border: 0;
            background: transparent;
            padding: 0;
            color: #4f46e5;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .gc-wa-link-button:hover {
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .gc-wa-link-danger {
            color: #dc2626;
        }

        .gc-wa-phone-row {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .gc-wa-phone-input {
            flex: 1;
            min-width: 180px;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #0f172a;
            padding: 0.4375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .gc-wa-phone-input:focus {
            border-color: #6d28d9;
            box-shadow: 0 0 0 3px rgba(109,40,217,.12);
        }

        .gc-wa-phone-input::placeholder { color: #94a3b8; }

        .dark .gc-wa-phone-input,
        html.dark .gc-wa-phone-input,
        [data-theme="dark"] .gc-wa-phone-input {
            border-color: #3f3f46;
            background: #27272a;
            color: #f8fafc;
        }

        .gc-wa-code-box {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
        }

        .gc-wa-code-label {
            margin: 0;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #166534;
        }

        .gc-wa-code-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: .15em;
            color: #15803d;
            line-height: 1;
        }

        .gc-wa-code-steps {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .gc-wa-code-steps li {
            font-size: 0.75rem;
            color: #374151;
            padding-left: 1.25rem;
            position: relative;
        }

        .gc-wa-code-steps li::before {
            content: attr(data-n);
            position: absolute;
            left: 0;
            font-weight: 700;
            color: #16a34a;
        }

        .dark .gc-wa-code-box,
        html.dark .gc-wa-code-box,
        [data-theme="dark"] .gc-wa-code-box {
            border-color: rgba(34,197,94,.25);
            background: rgba(34,197,94,.07);
        }

        .dark .gc-wa-code-label,
        html.dark .gc-wa-code-label,
        [data-theme="dark"] .gc-wa-code-label { color: #86efac; }

        .dark .gc-wa-code-value,
        html.dark .gc-wa-code-value,
        [data-theme="dark"] .gc-wa-code-value { color: #4ade80; }

        .dark .gc-wa-code-steps li,
        html.dark .gc-wa-code-steps li,
        [data-theme="dark"] .gc-wa-code-steps li { color: #cbd5e1; }

        .gc-wa-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .gc-wa-modal-box {
            position: relative;
            background: #ffffff;
            border-radius: 1rem;
            padding: 2rem 2rem 1.75rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        .gc-wa-modal-close {
            position: absolute;
            top: .75rem;
            right: .75rem;
            border: 0;
            background: transparent;
            cursor: pointer;
            color: #64748b;
            font-size: 1rem;
            line-height: 1;
            padding: .25rem .4rem;
            border-radius: .375rem;
        }

        .gc-wa-modal-close:hover { background: #f1f5f9; color: #0f172a; }

        .gc-wa-modal-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            padding: 1rem 0;
        }

        .gc-wa-modal-loading-text {
            margin: 0;
            font-size: .9375rem;
            font-weight: 700;
            color: #111827;
        }

        .gc-wa-spinner {
            width: 2.5rem;
            height: 2.5rem;
            border: 3px solid #e5e7eb;
            border-top-color: #16a34a;
            border-radius: 50%;
            animation: gc-spin .75s linear infinite;
        }

        @keyframes gc-spin { to { transform: rotate(360deg); } }

        .gc-wa-modal-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: .2em;
            color: #15803d;
            text-align: center;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: .75rem;
            padding: .75rem 1rem;
            margin-top: .25rem;
        }

        .dark .gc-wa-modal-box,
        html.dark .gc-wa-modal-box,
        [data-theme="dark"] .gc-wa-modal-box {
            background: #18181b;
            box-shadow: 0 20px 60px rgba(0,0,0,.6);
        }

        .dark .gc-wa-modal-close,
        html.dark .gc-wa-modal-close,
        [data-theme="dark"] .gc-wa-modal-close { color: #94a3b8; }

        .dark .gc-wa-modal-close:hover,
        html.dark .gc-wa-modal-close:hover,
        [data-theme="dark"] .gc-wa-modal-close:hover { background: #27272a; color: #f8fafc; }

        .dark .gc-wa-modal-loading-text,
        html.dark .gc-wa-modal-loading-text,
        [data-theme="dark"] .gc-wa-modal-loading-text { color: #f8fafc; }

        .dark .gc-wa-modal-code,
        html.dark .gc-wa-modal-code,
        [data-theme="dark"] .gc-wa-modal-code {
            background: rgba(34,197,94,.07);
            border-color: rgba(34,197,94,.25);
            color: #4ade80;
        }

        .gc-wa-primary-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 0;
            border-radius: 0.5rem;
            background: #16a34a;
            color: #ffffff;
            cursor: pointer;
            padding: 0.5rem 1.125rem;
            font-size: 0.8125rem;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.16);
        }

        .gc-wa-primary-button:hover {
            background: #15803d;
        }

        .gc-wa-primary-button:disabled {
            cursor: wait;
            opacity: 0.7;
        }

        .dark .gc-wa-admin-note,
        html.dark .gc-wa-admin-note,
        [data-theme="dark"] .gc-wa-admin-note {
            border-color: rgba(139, 92, 246, 0.35);
            background: rgba(139, 92, 246, 0.12);
            color: #ddd6fe;
        }

        .dark .gc-wa-card,
        html.dark .gc-wa-card,
        [data-theme="dark"] .gc-wa-card {
            border-color: #2f3037;
            background: #18181b;
            color: #f8fafc;
            box-shadow: none;
        }

        .dark .gc-wa-header,
        html.dark .gc-wa-header,
        [data-theme="dark"] .gc-wa-header {
            border-bottom-color: #2f3037;
        }

        .dark .gc-wa-title,
        html.dark .gc-wa-title,
        [data-theme="dark"] .gc-wa-title,
        .dark .gc-wa-instructions,
        html.dark .gc-wa-instructions,
        [data-theme="dark"] .gc-wa-instructions {
            color: #f8fafc;
        }

        .dark .gc-wa-muted,
        html.dark .gc-wa-muted,
        [data-theme="dark"] .gc-wa-muted {
            color: #cbd5e1;
        }

        .dark .gc-wa-code,
        html.dark .gc-wa-code,
        [data-theme="dark"] .gc-wa-code {
            background: #27272a;
            color: #c4b5fd;
        }

        .dark .gc-wa-admin-badge,
        html.dark .gc-wa-admin-badge,
        [data-theme="dark"] .gc-wa-admin-badge {
            background: rgba(139, 92, 246, 0.16);
            color: #ddd6fe;
        }

        .dark .gc-wa-status-open,
        html.dark .gc-wa-status-open,
        [data-theme="dark"] .gc-wa-status-open {
            background: rgba(34, 197, 94, 0.16);
            color: #86efac;
        }

        .dark .gc-wa-status-connecting,
        html.dark .gc-wa-status-connecting,
        [data-theme="dark"] .gc-wa-status-connecting {
            background: rgba(234, 179, 8, 0.16);
            color: #fde68a;
        }

        .dark .gc-wa-status-close,
        html.dark .gc-wa-status-close,
        [data-theme="dark"] .gc-wa-status-close {
            background: rgba(239, 68, 68, 0.16);
            color: #fca5a5;
        }

        .dark .gc-wa-status-unknown,
        html.dark .gc-wa-status-unknown,
        [data-theme="dark"] .gc-wa-status-unknown {
            background: #27272a;
            color: #cbd5e1;
        }

        .dark .gc-wa-link-button,
        html.dark .gc-wa-link-button,
        [data-theme="dark"] .gc-wa-link-button {
            color: #c4b5fd;
        }

        .dark .gc-wa-link-danger,
        html.dark .gc-wa-link-danger,
        [data-theme="dark"] .gc-wa-link-danger {
            color: #fca5a5;
        }
    </style>

    @if(auth()->user()?->isSuperAdmin())
        <div class="gc-wa-admin-note">
            ℹ️ <strong>Painel do Sistema.</strong>
            As configurações de perfil abaixo não se aplicam ao super admin.
            Use esta página para <strong>conectar a instância WhatsApp do sistema</strong> usada para envio de OTPs e notificações de cadastro.
        </div>
    @endif

    {{ $this->content }}

    {{--
        Polling via Alpine.js setInterval (5 s) em vez de wire:poll.
        wire:poll reinicia o contador cada vez que o Livewire faz morphdom no componente
        (ex.: ao exibir o QR), fazendo o status parecer "travado".
        $wire.call() chama o método diretamente no componente Livewire, de forma independente
        do ciclo de render.
    --}}
    <div
        class="gc-wa-card"
        x-data="{
            waStatus: $wire.entangle('waStatus'),
            waQrCode: $wire.entangle('waQrCode'),
            dismissed: false,
            _t: null,
            // Visibilidade derivada PURAMENTE do estado — nunca dessincroniza.
            get showModal() {
                if (this.waStatus === 'open') return false;
                return (this.waStatus === 'connecting' || !!this.waQrCode) && !this.dismissed;
            },
            startConnect() {
                this.dismissed = false;       // garante modal aberto numa nova tentativa
                $wire.connectWhatsApp();
            },
            // Polling recursivo: nunca sobrepõe requisições (evita race de snapshots).
            startPolling() {
                const tick = async () => {
                    try { await $wire.refreshWhatsAppStatus(); } catch (e) {}
                    const delay = (this.waStatus === 'open') ? 8000 : 2500;
                    this._t = setTimeout(tick, delay);
                };
                this._t = setTimeout(tick, 2500);
            }
        }"
        x-init="startPolling()"
        x-destroy="clearTimeout(_t)"
    >
        <div class="gc-wa-header">
            <span class="gc-wa-title">📱 WhatsApp — Conexão</span>
            @if(auth()->user()?->isSuperAdmin())
                <span class="gc-wa-admin-badge">
                    Instância Admin · OTPs e Notificações do Sistema
                </span>
            @endif
        </div>

        @if($this->isPaidPlan())
            <div class="gc-wa-body">
                <p class="gc-wa-muted">
                    Instância: <code class="gc-wa-code">{{ $this->waInstanceName() }}</code>
                </p>

                {{-- Status badge + verificar --}}
                <div class="gc-wa-row">
                    @if($waStatus === 'open')
                        <span class="gc-wa-status gc-wa-status-open">
                            <span class="gc-wa-dot" style="background:#22c55e;"></span>
                            Conectado ✅
                        </span>
                        <button
                            type="button"
                            wire:click="disconnectWhatsApp"
                            class="gc-wa-link-button gc-wa-link-danger"
                        >
                            Desconectar
                        </button>
                    @elseif($waStatus === 'connecting')
                        <span class="gc-wa-status gc-wa-status-connecting">
                            <span class="gc-wa-dot" style="background:#eab308;"></span>
                            Aguardando pareamento
                        </span>
                    @elseif($waStatus === 'close')
                        <span class="gc-wa-status gc-wa-status-close">
                            <span class="gc-wa-dot" style="background:#ef4444;"></span>
                            Desconectado
                        </span>
                    @else
                        <span class="gc-wa-status gc-wa-status-unknown">
                            <span class="gc-wa-dot" style="background:#94a3b8;"></span>
                            Não conectado
                        </span>
                    @endif

                    <button
                        type="button"
                        wire:click="refreshWhatsAppStatus"
                        class="gc-wa-link-button"
                    >
                        ↻ Verificar status
                    </button>
                </div>

                {{-- Campo de telefone + botão de conexão --}}
                @if($waStatus !== 'open')
                    <div class="gc-wa-phone-row">
                        <input
                            type="tel"
                            wire:model.live="waPhoneNumber"
                            placeholder="5511999999999"
                            class="gc-wa-phone-input"
                            maxlength="15"
                        >
                        <button
                            type="button"
                            @click="startConnect()"
                            wire:loading.attr="disabled"
                            wire:target="connectWhatsApp"
                            class="gc-wa-primary-button"
                        >
                            <span wire:loading.remove wire:target="connectWhatsApp">📲 Conectar</span>
                            <span wire:loading wire:target="connectWhatsApp">Aguarde...</span>
                        </button>
                    </div>
                    <p class="gc-wa-muted">
                        Informe o número completo com DDI e DDD, ex: <code class="gc-wa-code">5511999999999</code>
                    </p>
                @endif
            </div>
        @else
            <div class="gc-wa-body">
                <p class="gc-wa-muted" style="display:flex;align-items:center;gap:0.5rem;">
                    🔒 Disponível nos planos <strong>Starter, Pro e Ultra</strong>.
                </p>
            </div>
        @endif

        {{-- Modal de pareamento — visibilidade 100% derivada de showModal --}}
        <div
            x-show="showModal"
            x-cloak
            x-transition.opacity
            class="gc-wa-modal-overlay"
            @click.self="dismissed = true"
        >
            <div
                x-show="showModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="gc-wa-modal-box"
                @click.stop
            >
                {{-- Fechar --}}
                <button type="button" class="gc-wa-modal-close" @click="dismissed = true">✕</button>

                {{-- QR pronto --}}
                <template x-if="waQrCode">
                    <div>
                        <p class="gc-wa-code-label" style="margin-bottom:.75rem; text-align:center;">
                            Escaneie com o WhatsApp
                        </p>
                        <div style="display:flex; justify-content:center;">
                            <img :src="waQrCode" alt="QR Code WhatsApp" style="width:220px; height:220px; border-radius:.5rem;">
                        </div>
                        <ol class="gc-wa-code-steps" style="margin-top:1rem;">
                            <li data-n="1.">Abra o WhatsApp no celular</li>
                            <li data-n="2.">Toque em <strong>⋮ Menu → Dispositivos conectados</strong></li>
                            <li data-n="3.">Toque em <strong>Conectar dispositivo</strong></li>
                            <li data-n="4.">Aponte a câmera para o QR Code acima</li>
                        </ol>
                        <p class="gc-wa-muted" style="text-align:center; margin-top:.75rem;">
                            O QR Code é renovado automaticamente. A janela fecha sozinha ao conectar.
                        </p>
                    </div>
                </template>

                {{-- Aguardando QR --}}
                <template x-if="!waQrCode && waStatus === 'connecting'">
                    <div class="gc-wa-modal-loading">
                        <div class="gc-wa-spinner"></div>
                        <p class="gc-wa-modal-loading-text">Gerando QR Code…</p>
                        <p class="gc-wa-muted" style="text-align:center;">Isso pode levar alguns segundos.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-filament-panels::page>
