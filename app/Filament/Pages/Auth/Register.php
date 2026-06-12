<?php

namespace App\Filament\Pages\Auth;

use App\Models\BusinessNiche;
use App\Models\RegistrationOtp;
use App\Services\RegistrationOtpService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\RenderHook;
use Filament\Schemas\Schema;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;

class Register extends BaseRegister
{
    // ── Estado Livewire ───────────────────────────────────────────────────────

    public int $step = 1;

    /** E-mail preservado do passo 1 para uso no passo 2 */
    public string $pendingEmail = '';

    /** @var array<string, mixed>|null */
    public ?array $otpData = ['code' => ''];

    // ── Ciclo de vida ─────────────────────────────────────────────────────────

    public function mount(): void
    {
        parent::mount();
        $this->otpForm->fill();
    }

    // ── Títulos ───────────────────────────────────────────────────────────────

    public function getTitle(): string|Htmlable
    {
        return $this->step === 1 ? 'Cadastrar empresa' : 'Verificar identidade';
    }

    public function getHeading(): string|Htmlable|null
    {
        return $this->step === 1 ? 'Cadastre sua empresa' : 'Confirme seu código';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->step === 2) {
            return new HtmlString(
                'Enviamos um código de 6 dígitos para <strong>' . e($this->pendingEmail) . '</strong>'
                . ' e para o WhatsApp cadastrado.'
            );
        }

        if (! filament()->hasLogin()) {
            return null;
        }

        return new HtmlString(
            __('filament-panels::auth/pages/register.actions.login.before')
            . ' '
            . $this->loginAction->toHtml()
        );
    }

    // ── Formulário passo 1 — dados de cadastro ────────────────────────────────

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                TextInput::make('company_name')
                    ->label('Nome da empresa')
                    ->placeholder('Ex: Clínica Bem Estar')
                    ->required()
                    ->maxLength(100)
                    ->autofocus(),

                TextInput::make('gestor_name')
                    ->label('Seu nome (responsável)')
                    ->placeholder('Nome completo')
                    ->required()
                    ->maxLength(100),

                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique('users', 'email')
                    ->placeholder('gestor@suaempresa.com.br'),

                TextInput::make('phone')
                    ->label('WhatsApp')
                    ->tel()
                    ->required()
                    ->placeholder('(11) 99999-9999'),

                Select::make('business_niche_ids')
                    ->label('Segmentos do negócio')
                    ->options(function (Get $get) {
                        $selected = array_map('intval', array_filter((array) ($get('business_niche_ids') ?? [])));

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
                    ->required()
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

                        // Mantém apenas os itens da categoria do primeiro selecionado
                        $primaryCat = $items->firstWhere('id', $ids[0])?->niche_category_id;
                        $valid = $items->filter(fn ($r) => $r->niche_category_id === $primaryCat)->pluck('id')->values()->toArray();
                        $set('business_niche_ids', $valid);
                    })
                    ->columnSpanFull(),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->rule(Password::default())
                    ->showAllValidationMessages()
                    ->same('passwordConfirmation')
                    ->validationAttribute('senha'),

                TextInput::make('passwordConfirmation')
                    ->label('Confirmar senha')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    // ── Formulário passo 2 — código OTP ──────────────────────────────────────

    public function otpForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('otpData')
            ->components([
                TextInput::make('code')
                    ->label('Código de verificação')
                    ->placeholder('000000')
                    ->required()
                    ->length(6)
                    ->numeric()
                    ->autofocus()
                    ->extraInputAttributes([
                        'style' => 'font-size:2rem;letter-spacing:.5rem;text-align:center;font-weight:900;',
                    ])
                    ->helperText('Digite o código de 6 dígitos enviado para seu e-mail e WhatsApp.'),
            ]);
    }

    // ── Layout da página — alterna entre passo 1 e passo 2 ───────────────────

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE),

            // Passo 1
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('register')
                ->footer([
                    Actions::make($this->getFormActions())
                        ->fullWidth(true)
                        ->key('form-actions'),
                ])
                ->visible(fn () => $this->step === 1),

            // Passo 2
            Form::make([EmbeddedSchema::make('otpForm')])
                ->id('otp-form')
                ->livewireSubmitHandler('verifyOtp')
                ->footer([
                    Actions::make([
                        Action::make('verifyOtp')
                            ->label('Confirmar código')
                            ->submit('verifyOtp')
                            ->extraAttributes(['style' => 'white-space:nowrap']),
                        Action::make('resendOtp')
                            ->label('Reenviar código')
                            ->link()
                            ->extraAttributes(['style' => 'white-space:nowrap'])
                            ->action(fn () => $this->resendOtp()),
                        Action::make('backToStep1')
                            ->label('← Corrigir dados')
                            ->link()
                            ->color('gray')
                            ->extraAttributes(['style' => 'white-space:nowrap'])
                            ->action(fn () => $this->step = 1),
                    ])->key('otp-actions'),
                ])
                ->visible(fn () => $this->step === 2),

            RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_AFTER),
        ]);
    }

    // ── Handlers ──────────────────────────────────────────────────────────────

    /**
     * Passo 1: valida dados, gera e envia OTP para email + WhatsApp.
     */
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $e) {
            Notification::make()
                ->title('Muitas tentativas')
                ->body("Aguarde {$e->secondsUntilAvailable}s antes de tentar novamente.")
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        try {
            app(RegistrationOtpService::class)->createAndSend($data, request()->ip());
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erro ao enviar código')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }

        $this->pendingEmail = $data['email'];
        $this->step         = 2;
        $this->otpForm->fill(['code' => '']);

        Notification::make()
            ->title('Código enviado!')
            ->body('Verifique seu e-mail e WhatsApp.')
            ->success()
            ->send();

        return null;
    }

    /**
     * Passo 2: verifica OTP, finaliza cadastro, autentica e redireciona.
     */
    public function verifyOtp(): void
    {
        $data = $this->otpForm->getState();

        try {
            $service = app(RegistrationOtpService::class);
            $otp     = $service->verify($this->pendingEmail, $data['code']);
            $user    = $service->complete($otp);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Verificação falhou')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        filament()->auth()->login($user);
        session()->regenerate();

        $this->redirect(route('filament.admin.pages.empresa-settings'), navigate: true);
    }

    /**
     * Reenvia o OTP (gera novo código) preservando os dados originais do passo 1.
     */
    public function resendOtp(): void
    {
        $pending = RegistrationOtp::where('email', $this->pendingEmail)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $pending) {
            Notification::make()
                ->title('Sessão expirada')
                ->body('Preencha o formulário novamente.')
                ->warning()
                ->send();

            $this->step = 1;

            return;
        }

        // Reutiliza os dados do passo 1 para gerar novo OTP.
        // O campo password já está hashed; `createAndSend` chamará bcrypt novamente,
        // mas o valor final (após complete()) sempre usará o hash gerado no createAndSend.
        // Para preservar corretamente, salvamos o hash original e repassamos ao novo OTP via
        // update direto após createAndSend.
        try {
            $originalHashedPassword = $pending->password;

            $newOtp = app(RegistrationOtpService::class)->createAndSend([
                'company_name'       => $pending->company_name,
                'gestor_name'        => $pending->gestor_name,
                'email'              => $pending->email,
                'phone'              => $pending->phone,
                'password'           => 'placeholder', // será sobrescrito logo abaixo
                'business_niche_ids' => $pending->business_niche_ids ?: [$pending->business_niche_id],
            ], request()->ip());

            // Preserva o hash original para que complete() use a senha correta
            $newOtp->update(['password' => $originalHashedPassword]);

        } catch (\Throwable) {
            // falha silenciosa — mensagem de sucesso não é enviada abaixo
            Notification::make()
                ->title('Não foi possível reenviar')
                ->body('Tente novamente em instantes.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Código reenviado!')
            ->body('Verifique seu e-mail e WhatsApp.')
            ->success()
            ->send();
    }

    // ── Botões do formulário passo 1 ──────────────────────────────────────────

    /**
     * @return array<Action|\Filament\Actions\ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('register')
                ->label('Cadastrar e receber código')
                ->submit('register'),
        ];
    }
}
