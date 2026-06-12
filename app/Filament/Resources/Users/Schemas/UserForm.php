<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Professional;
use App\Models\User;
use App\Support\Permission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados de Acesso')
                ->columns(['default' => 1, 'sm' => 2])
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(table: 'users', column: 'email', ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->required(fn (string $context) => $context === 'create')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->minLength(8)
                        ->columnSpan(1),

                    Select::make('role')
                        ->label('Função')
                        ->options(function () {
                            if (Permission::isSuperAdmin()) {
                                return [
                                    User::ROLE_SUPER_ADMIN  => 'Super Admin',
                                    User::ROLE_GESTOR       => 'Gestor',
                                    User::ROLE_PROFISSIONAL => 'Profissional / Colaborador',
                                ];
                            }
                            return [User::ROLE_PROFISSIONAL => 'Profissional / Colaborador'];
                        })
                        ->required()
                        ->live()
                        ->default(User::ROLE_PROFISSIONAL),
                ]),

            Section::make('Perfil Profissional')
                ->description('Vincule este usuário ao perfil profissional para que ele acesse apenas sua própria agenda.')
                ->visible(fn ($get) => $get('role') === User::ROLE_PROFISSIONAL)
                ->schema([
                    Select::make('professional_id')
                        ->label('Vincular ao profissional')
                        ->options(function () {
                            $user = auth()->user();
                            $query = Professional::query()->orderBy('name');
                            if (! $user->isSuperAdmin()) {
                                $query->where('tenant_id', $user->tenant_id);
                            }
                            return $query->pluck('name', 'id');
                        })
                        ->nullable()
                        ->searchable()
                        ->helperText('Sem vínculo, o profissional não consegue filtrar sua própria agenda.'),
                ]),

            Section::make('Permissões Opcionais')
                ->description('Defina o que este profissional pode fazer além das permissões base.')
                ->visible(fn ($get) => $get('role') === User::ROLE_PROFISSIONAL)
                ->schema([
                    CheckboxList::make('permissions')
                        ->label('')
                        ->options([
                            User::PERM_CRIAR_AGENDAMENTO    => 'Criar agendamentos',
                            User::PERM_EDITAR_AGENDAMENTO   => 'Editar agendamentos',
                            User::PERM_CANCELAR_AGENDAMENTO => 'Cancelar agendamentos',
                            User::PERM_VER_AGENDA_GERAL     => 'Ver agenda geral (todos os profissionais)',
                            User::PERM_VER_TODOS_CLIENTES   => 'Ver todos os clientes',
                            User::PERM_CADASTRAR_SERVICOS   => 'Cadastrar serviços',
                            User::PERM_VER_RELATORIOS       => 'Ver relatórios',
                        ])
                        ->columns(2)
                        ->gridDirection('row'),
                ]),
        ]);
    }
}
