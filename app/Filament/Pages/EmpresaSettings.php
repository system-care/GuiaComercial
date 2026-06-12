<?php

namespace App\Filament\Pages;

use App\Models\Subscription;
use App\Models\TenantSetting;
use App\Services\EvolutionService;
use App\Support\Permission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;

class EmpresaSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Minha Empresa';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.pages.empresa-settings';

    // ── Campos do formulário ──────────────────────────────────────────────────
    public array   $business_niche_ids = [];
    public string  $description   = '';
    public string  $whatsapp      = '';
    public string  $address       = '';
    public string  $instagram     = '';
    public string  $facebook      = '';
    public string  $youtube       = '';
    public string  $tiktok        = '';
    public string  $website       = '';
    public string  $hero_title    = '';
    public string  $hero_subtitle = '';
    public string  $primary_color = '#7c3aed';
    public bool    $show_prices          = true;
    public bool    $show_team            = true;
    public bool    $allow_online_booking = true;
    public array $logo_path   = [];
    public array $banner_path = [];

    // ── WhatsApp / Evolution ─────────────────────────────────────────────────
    public string $confirmation_message = '';
    public bool   $evolution_enabled    = false;
    public int    $reminder_1          = 0;
    public int    $reminder_2          = 0;

    // ── Aniversário ───────────────────────────────────────────────────────────
    public bool   $birthday_enabled    = false;
    public string $birthday_message    = '';

    // Estado da conexão — não persistido, apenas em memória durante a sessão Livewire
    public ?string $waStatus      = null;
    public string  $waPhoneNumber = '';
    public ?string $waPairingCode = null;
    public ?string $waQrCode      = null;
    public ?int    $waQrAt        = null;   // timestamp (int) da última geração de QR
    public bool    $waLoading     = false;

    public static function canAccess(): bool
    {
        return Permission::isGestorOrAbove();
    }

    public function isPaidPlan(): bool
    {
        $user = auth()->user();
        if (! $user || $user->isSuperAdmin()) {
            return true;
        }

        return Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->exists();
    }

    public function mount(): void
    {
        $user     = auth()->user();
        $settings = [];

        if (! $user->isSuperAdmin()) {
            $settings = TenantSetting::where('tenant_id', $user->tenant_id)->value('settings') ?? [];
        }

        $tenant = $user->isSuperAdmin() ? null : $user->tenant;
        $niches = $tenant?->business_niche_ids ?? [];
        if (empty($niches) && $tenant?->business_niche_id) {
            $niches = [$tenant->business_niche_id];
        }
        $this->business_niche_ids = array_map('intval', $niches);

        $this->description          = $settings['description']          ?? '';
        $this->whatsapp             = $settings['whatsapp']             ?? '';
        $this->address              = $settings['address']              ?? '';
        $this->instagram            = $settings['instagram']            ?? '';
        $this->facebook             = $settings['facebook']             ?? '';
        $this->youtube              = $settings['youtube']              ?? '';
        $this->tiktok               = $settings['tiktok']               ?? '';
        $this->website              = $settings['website']              ?? '';
        $this->hero_title           = $settings['hero_title']           ?? '';
        $this->hero_subtitle        = $settings['hero_subtitle']        ?? '';
        $this->primary_color        = $settings['primary_color']        ?? '#7c3aed';
        $this->show_prices          = (bool) ($settings['show_prices']          ?? true);
        $this->show_team            = (bool) ($settings['show_team']            ?? true);
        $this->allow_online_booking = (bool) ($settings['allow_online_booking'] ?? true);
        $savedLogo   = $settings['logo_path']   ?? null;
        $savedBanner = $settings['banner_path'] ?? null;
        $this->logo_path   = $savedLogo   ? [$savedLogo]   : [];
        $this->banner_path = $savedBanner ? [$savedBanner] : [];
        $this->confirmation_message = $settings['confirmation_message']
            ?? \App\Services\WhatsAppNotificationService::DEFAULT_TEMPLATE;
        $this->evolution_enabled = (bool) ($settings['evolution_enabled'] ?? false);
        $this->reminder_1        = (int)  ($settings['reminder_1']        ?? $settings['reminder_hours'] ?? 0);
        $this->reminder_2        = (int)  ($settings['reminder_2']        ?? 0);
        $this->birthday_enabled  = (bool) ($settings['birthday_enabled']  ?? false);
        $this->birthday_message  = $settings['birthday_message']
            ?? 'Olá, {nome}! 🎂 A equipe da {empresa} deseja um feliz aniversário! Esperamos te ver em breve.';

        if ($user->isSuperAdmin() || ($this->isPaidPlan() && $this->evolution_enabled)) {
            $this->refreshWhatsAppStatus();
        }
    }

    public function content(Schema $schema): Schema
    {
        $tenantId    = auth()->user()?->tenant_id ?? 0;
        $isPaid      = $this->isPaidPlan();
        $paidHint    = ! $isPaid
            ? new \Illuminate\Support\HtmlString('<span style="color:' . e($this->primary_color) . '">Disponível nos planos Starter, Pro e Ultra.</span>')
            : null;

        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([

                // ── Coluna principal (2/3) ──────────────────────────────
                Grid::make()
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->columns(1)
                    ->schema([

                        Section::make('Identidade Visual')
                            ->description('Logo e banner exibidos na sua página pública.')
                            ->columns(2)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label('Logo')
                                    ->image()
                                    ->disk('panel')
                                    ->directory("tenants/{$tenantId}")
                                    ->visibility('public')
                                    ->imagePreviewHeight('100')
                                    ->helperText('Ideal: 400 × 400 px. PNG recomendado. Max 2 MB.')
                                    ->maxSize(2048),

                                FileUpload::make('banner_path')
                                    ->label('Banner (hero da página pública)')
                                    ->image()
                                    ->disk('panel')
                                    ->directory("tenants/{$tenantId}")
                                    ->visibility('public')
                                    ->imagePreviewHeight('100')
                                    ->helperText('Ideal: 1920 × 600 px. Max 5 MB.')
                                    ->maxSize(5120),

                                ColorPicker::make('primary_color')
                                    ->label('Cor principal da página')
                                    ->helperText('Usada em botões, destaques e bordas.')
                                    ->columnSpanFull(),

                                TextInput::make('hero_title')
                                    ->label('Título personalizado do Hero Banner')
                                    ->placeholder('Ex: Saúde integrativa com atendimento humanizado')
                                    ->helperText('Se ficar vazio, será usado o nome da empresa.')
                                    ->maxLength(90)
                                    ->columnSpanFull(),

                                Textarea::make('hero_subtitle')
                                    ->label('Descrição curta do Hero Banner')
                                    ->placeholder('Texto curto exibido logo abaixo do título principal.')
                                    ->helperText('Recomendado: até 180 caracteres.')
                                    ->rows(3)
                                    ->maxLength(220)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Sobre a Empresa')
                            ->schema([
                                Select::make('business_niche_ids')
                                    ->label('Segmentos do negócio')
                                    ->options(function (Get $get) {
                                        $fromGet  = array_map('intval', array_filter((array) ($get('business_niche_ids') ?? [])));
                                        $selected = ! empty($fromGet) ? $fromGet : array_map('intval', $this->business_niche_ids);

                                        $categoryId = null;
                                        if (! empty($selected)) {
                                            $categoryId = \App\Models\BusinessNiche::whereIn('id', $selected)
                                                ->value('niche_category_id');
                                        }

                                        return \App\Models\NicheCategory::with(['niches' => fn ($q) => $q->where('active', true)->orderBy('name')])
                                            ->active()
                                            ->when($categoryId, fn ($q) => $q->where('id', $categoryId))
                                            ->get()
                                            ->mapWithKeys(fn ($cat) => [
                                                $cat->name => $cat->niches->pluck('name', 'id'),
                                            ])
                                            ->toArray();
                                    })
                                    ->multiple()
                                    ->live()
                                    ->searchable()
                                    ->placeholder('Selecione um ou mais segmentos')
                                    ->helperText(fn (Get $get) => empty(array_filter((array) ($get('business_niche_ids') ?? [])))
                                        ? 'Selecione o segmento principal e adicione outros do mesmo grupo.'
                                        : 'Apenas segmentos da mesma categoria estão disponíveis.')
                                    ->afterStateUpdated(function (?array $state, callable $set) {
                                        $ids = array_values(array_map('intval', array_filter((array) ($state ?? []))));
                                        if (count($ids) <= 1) return;

                                        $items = \App\Models\BusinessNiche::whereIn('id', $ids)->get(['id', 'niche_category_id']);
                                        if ($items->pluck('niche_category_id')->unique()->count() <= 1) return;

                                        $primaryCat = $items->firstWhere('id', $ids[0])?->niche_category_id;
                                        $valid = $items->filter(fn ($r) => $r->niche_category_id === $primaryCat)->pluck('id')->values()->toArray();
                                        $set('business_niche_ids', $valid);
                                    }),

                                RichEditor::make('description')
                                    ->label('Descrição / Sobre nós')
                                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link'])
                                    ->hintActions([
                                        Action::make('generateDescription')
                                            ->label('Descrição por IA')
                                            ->icon(Heroicon::OutlinedSparkles)
                                            ->color('violet')
                                            ->action(fn () => $this->generateDescription()),
                                    ]),
                            ]),

                        Section::make('Contato Público')
                            ->columns(2)
                            ->schema([
                                TextInput::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->placeholder('5511999999999')
                                    ->helperText('Somente números com DDD e código do país.')
                                    ->tel(),

                                TextInput::make('address')
                                    ->label('Endereço')
                                    ->placeholder('Rua das Flores, 123 — Centro')
                                    ->maxLength(255),

                                TextInput::make('instagram')
                                    ->label('Instagram')
                                    ->placeholder('https://instagram.com/suaempresa')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('facebook')
                                    ->label('Facebook')
                                    ->placeholder('https://facebook.com/suaempresa')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('youtube')
                                    ->label('YouTube')
                                    ->placeholder('https://youtube.com/@seucanal')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('tiktok')
                                    ->label('TikTok')
                                    ->placeholder('https://tiktok.com/@suaempresa')
                                    ->url()
                                    ->maxLength(255),

                                TextInput::make('website')
                                    ->label('Website')
                                    ->placeholder('https://seusite.com.br')
                                    ->url()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),

                    ]),

                // ── Sidebar (1/3) ───────────────────────────────────────
                Grid::make()
                    ->columnSpan(['default' => 1, 'lg' => 1])
                    ->columns(1)
                    ->schema([

                        Section::make('Visibilidade da página')
                            ->schema([
                                Toggle::make('allow_online_booking')
                                    ->label('Agendamento online')
                                    ->helperText('Exibe o formulário de agendamento na página pública.')
                                    ->default(true),

                                Toggle::make('show_prices')
                                    ->label('Mostrar preços')
                                    ->default(true),

                                Toggle::make('show_team')
                                    ->label('Mostrar equipe')
                                    ->default(true),
                            ]),

                        Section::make('Notificações WhatsApp')
                            ->description('Mensagens automáticas enviadas ao cliente via WhatsApp.')
                            ->schema([
                                Toggle::make('evolution_enabled')
                                    ->label('Confirmação automática')
                                    ->helperText('Envia confirmação a cada novo agendamento.')
                                    ->disabled(! $isPaid),

                                Select::make('reminder_1')
                                    ->label('1º lembrete')
                                    ->options([
                                        0  => 'Desativado',
                                        3  => '3 horas antes',
                                        6  => '6 horas antes',
                                        8  => '8 horas antes',
                                        12 => '12 horas antes',
                                        24 => '24 horas antes',
                                    ])
                                    ->default(0)
                                    ->disabled(! $isPaid)
                                    ->helperText($paidHint),

                                Select::make('reminder_2')
                                    ->label('2º lembrete')
                                    ->options([
                                        0  => 'Desativado',
                                        3  => '3 horas antes',
                                        6  => '6 horas antes',
                                        8  => '8 horas antes',
                                        12 => '12 horas antes',
                                        24 => '24 horas antes',
                                    ])
                                    ->default(0)
                                    ->disabled(! $isPaid)
                                    ->helperText($paidHint),
                            ]),

                        Section::make('Mensagem de Confirmação Por WhatsApp')
                            ->description('Enviada automaticamente ao cliente quando um agendamento é criado.')
                            ->schema([
                                Textarea::make('confirmation_message')
                                    ->label('Mensagem')
                                    ->rows(8)
                                    ->maxLength(1000)
                                    ->helperText('Variáveis: {nome}, {empresa}, {data}, {horario}, {servico}, {profissional}, {endereco}, {link}'),
                            ]),

                        Section::make('Mensagem de Aniversário')
                            ->description('Enviada automaticamente no dia do aniversário do cliente.')
                            ->schema([
                                Toggle::make('birthday_enabled')
                                    ->label('Enviar mensagem de aniversário')
                                    ->disabled(! $isPaid),

                                Textarea::make('birthday_message')
                                    ->label('Mensagem')
                                    ->rows(4)
                                    ->maxLength(500)
                                    ->disabled(! $isPaid)
                                    ->helperText(! $isPaid
                                        ? $paidHint
                                        : 'Variáveis disponíveis: {nome}, {empresa}'),
                            ]),

                    ]),

            ]);
    }

    // ── WhatsApp QR / Status ──────────────────────────────────────────────────

    public function waInstanceName(): string
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return config('services.evolution.admin_instance') ?: 'gc_admin';
        }

        $slug = $user->tenant?->slug ?? 'tenant';
        return 'gc_' . $slug . '_' . $user->tenant_id;
    }

    public function connectWhatsApp(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $phone = preg_replace('/\D/', '', $this->waPhoneNumber);

        if (strlen($phone) < 10) {
            Notification::make()
                ->title('Informe o número do WhatsApp que será conectado.')
                ->warning()
                ->send();
            return;
        }

        // Garante DDI 55 se não informado
        if (strlen($phone) <= 11 && ! str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }

        $baseUrl  = config('services.evolution.base_url');
        $token    = config('services.evolution.token');
        $instance = $this->waInstanceName();

        if (! $baseUrl || ! $token) {
            Notification::make()->title('Evolution API não configurada no servidor.')->danger()->send();
            return;
        }

        // Reset total do estado antes de iniciar
        $this->waPairingCode = null;
        $this->waQrCode      = null;
        $this->waQrAt        = null;
        $this->waStatus      = null;

        try {
            $evolution     = app(EvolutionService::class);
            $currentStatus = $evolution->connectionStatus($baseUrl, $token, $instance);

            if ($currentStatus === 'open') {
                $this->waStatus = 'open';
                Notification::make()->title('WhatsApp já está conectado! ✅')->success()->send();
                return;
            }

            // Sempre recria a instância para garantir QR limpo e válido
            $evolution->deleteInstance($baseUrl, $token, $instance);
            sleep(1);

            $created = $evolution->createInstance($baseUrl, $token, $instance, $phone);

            // O create já devolve o QR em qrcode.base64 — captura imediatamente
            $qr = $created['qrcode']['base64'] ?? null;

            if (! $qr) {
                // Fallback: busca via /instance/connect
                $qr = $evolution->fetchQrCode($baseUrl, $token, $instance);
            }

            $this->waStatus = 'connecting';

            if ($qr) {
                $this->waQrCode = $qr;
                $this->waQrAt   = now()->timestamp;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WA connect failed', [
                'instance' => $instance,
                'error'    => $e->getMessage(),
            ]);
            Notification::make()->title('Erro ao conectar: ' . $e->getMessage())->danger()->send();
        }
    }

    public function refreshWhatsAppStatus(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $baseUrl  = config('services.evolution.base_url');
        $token    = config('services.evolution.token');
        $instance = $this->waInstanceName();

        if (! $baseUrl || ! $token) {
            return;
        }

        try {
            $evolution  = app(EvolutionService::class);
            $prevStatus = $this->waStatus;
            $newStatus  = $evolution->connectionStatus($baseUrl, $token, $instance);

            $this->waStatus = $newStatus;

            if ($newStatus === 'open') {
                // Conectou: limpa o QR. O modal fecha sozinho (visibilidade derivada do estado).
                $this->waQrCode      = null;
                $this->waPairingCode = null;
                $this->waQrAt        = null;

                if ($prevStatus !== 'open') {
                    Notification::make()->title('WhatsApp conectado! ✅')->success()->send();
                }
            } elseif ($newStatus === 'connecting') {
                // Atualiza o QR se não houver nenhum ou se o atual já expirou (~20 s)
                $expired = $this->waQrAt === null || (now()->timestamp - $this->waQrAt) > 20;

                if (! $this->waQrCode || $expired) {
                    $qr = $evolution->fetchQrCode($baseUrl, $token, $instance);
                    if ($qr && $qr !== $this->waQrCode) {
                        $this->waQrCode = $qr;
                        $this->waQrAt   = now()->timestamp;
                    }
                }
            } else {
                // close / unknown: sem QR válido
                $this->waQrCode = null;
                $this->waQrAt   = null;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WA status check failed', [
                'instance' => $instance,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function disconnectWhatsApp(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $baseUrl  = config('services.evolution.base_url');
        $token    = config('services.evolution.token');
        $instance = $this->waInstanceName();

        try {
            $evolution = app(EvolutionService::class);
            $evolution->logout($baseUrl, $token, $instance);
            $this->waStatus      = 'close';
            $this->waPairingCode = null;
            $this->waQrCode      = null;
            $this->waQrAt        = null;
            Notification::make()->title('WhatsApp desconectado.')->warning()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Erro ao desconectar: ' . $e->getMessage())->danger()->send();
        }
    }

    // ── Header actions ────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_page')
                ->label('Ver página pública')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn () => $this->getLandingUrl())
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->getLandingUrl()),

            Action::make('save')
                ->label('Salvar configurações')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->action('save'),
        ];
    }

    private function storeUpload(array $files, string $directory): ?string
    {
        $file = reset($files) ?: null;
        if (! $file) {
            return null;
        }

        if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            return $file->storeAs($directory, $file->hashName(), 'panel') ?: null;
        }

        return is_string($file) ? $file : null;
    }

    public function save(): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            Notification::make()->title('Configurações do sistema salvas!')->success()->send();
            return;
        }

        $tenantId = $user->tenant_id;
        $record   = TenantSetting::firstOrNew(['tenant_id' => $tenantId]);
        $existing = $record->settings ?? [];

        $logoPath   = $this->storeUpload($this->logo_path,   "tenants/{$tenantId}");
        $bannerPath = $this->storeUpload($this->banner_path, "tenants/{$tenantId}");

        $record->settings = array_merge($existing, [
            'description'          => $this->description,
            'whatsapp'             => preg_replace('/\D/', '', $this->whatsapp),
            'address'              => $this->address,
            'instagram'            => $this->instagram,
            'facebook'             => $this->facebook,
            'youtube'              => $this->youtube,
            'tiktok'               => $this->tiktok,
            'website'              => $this->website,
            'hero_title'           => trim($this->hero_title),
            'hero_subtitle'        => trim($this->hero_subtitle),
            'primary_color'        => $this->primary_color,
            'show_prices'          => $this->show_prices,
            'show_team'            => $this->show_team,
            'allow_online_booking' => $this->allow_online_booking,
            'logo_path'            => $logoPath   ?? ($existing['logo_path']   ?? null),
            'banner_path'          => $bannerPath ?? ($existing['banner_path'] ?? null),
            'confirmation_message'  => trim($this->confirmation_message),
            'evolution_enabled'    => $this->isPaidPlan() ? $this->evolution_enabled : false,
            'reminder_1'           => $this->isPaidPlan() ? (int) $this->reminder_1 : 0,
            'reminder_2'           => $this->isPaidPlan() ? (int) $this->reminder_2 : 0,
            'birthday_enabled'     => $this->isPaidPlan() ? $this->birthday_enabled : false,
            'birthday_message'     => $this->isPaidPlan() ? trim($this->birthday_message) : '',
        ]);

        $record->save();

        $nicheIds = array_values(array_filter($this->business_niche_ids));
        if (! empty($nicheIds) && $user->tenant_id) {
            \App\Models\Tenant::where('id', $user->tenant_id)->update([
                'business_niche_id'  => $nicheIds[0],
                'business_niche_ids' => json_encode($nicheIds),
            ]);
        }

        Notification::make()->title('Configurações salvas!')->success()->send();
    }

    public function generateDescription(): void
    {
        $apiKey = config('services.cerebras.api_key');

        if (! $apiKey) {
            Notification::make()->title('Chave da API Cerebras não configurada.')->danger()->send();
            return;
        }

        $ids = array_map('intval', array_filter($this->business_niche_ids ?? []));

        if (empty($ids)) {
            Notification::make()
                ->title('Selecione ao menos um segmento antes de gerar a descrição.')
                ->warning()
                ->send();
            return;
        }

        $niches = \App\Models\BusinessNiche::whereIn('id', $ids)->pluck('name')->join(', ');
        $tenant = auth()->user()?->tenant;
        $company = $tenant?->name ?? 'nossa empresa';

        $prompt = "Escreva uma descrição profissional e acolhedora para a seção 'Sobre nós' de uma empresa brasileira chamada \"{$company}\". "
            . "A empresa atua na área de: {$niches}. "
            . "IMPORTANTE: use essas informações apenas como contexto para entender o tipo de negócio. NÃO cite os nomes dos segmentos literalmente no texto. "
            . "Escreva como se descrevesse naturalmente o que a empresa faz e o valor que entrega aos clientes. "
            . "Entre 2 e 4 frases. Tom humano, próximo e profissional. Português do Brasil. Apenas texto corrido, sem bullet points. Máximo 450 caracteres.";

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.cerebras.ai/v1/chat/completions', [
                    'model'       => config('services.cerebras.model', 'gpt-oss-120b'),
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens'  => 2048,
                    'temperature' => 0.75,
                ]);

            if (! $response->successful()) {
                $error = $response->json('message') ?? $response->body();
                throw new \RuntimeException("API retornou {$response->status()}: {$error}");
            }

            $data = $response->json();
            $msg  = $data['choices'][0]['message'] ?? [];
            $text = $msg['content'] ?? $msg['reasoning'] ?? null;

            if (! $text) {
                \Illuminate\Support\Facades\Log::error('Cerebras empty response', ['data' => $data]);
                throw new \RuntimeException('Resposta vazia da API. finish_reason: ' . ($data['choices'][0]['finish_reason'] ?? 'unknown'));
            }

            $this->description = trim($text);

            Notification::make()->title('Descrição gerada com sucesso!')->success()->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erro ao gerar descrição')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getLandingUrl(): ?string
    {
        $user = auth()->user();
        if (! $user || $user->isSuperAdmin() || ! $user->tenant_id) {
            return null;
        }
        $slug = $user->tenant?->slug;
        return $slug ? route('tenant.landing', $slug) : null;
    }
}
