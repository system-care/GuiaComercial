<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use App\Support\Permission;
use BackedEnum;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SystemSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog8Tooth;

    protected static ?string $navigationLabel = 'Config. do Sistema';

    protected static \UnitEnum|string|null $navigationGroup = 'Super Admin';

    protected static ?int $navigationSort = 99;

    protected static ?string $title = 'Configurações do Sistema';

    protected string $view = 'filament.pages.system-settings';

    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'google_oauth_enabled' => (bool) SystemSetting::get('google_oauth_enabled', false),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Autenticação Google (OAuth)')
                    ->description('Permite que usuários façam login ou cadastro usando conta Google.')
                    ->schema([
                        Toggle::make('google_oauth_enabled')
                            ->label('Ativar login com Google')
                            ->helperText('Quando ativo, exibe o botão "Entrar com Google" na tela de login.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SystemSetting::set('google_oauth_enabled', (bool) ($data['google_oauth_enabled'] ?? false));

        Notification::make()
            ->title('Configurações salvas!')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Salvar configurações')
                ->action('save'),
        ];
    }
}
