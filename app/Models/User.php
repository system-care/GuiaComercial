<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // ── Roles ──────────────────────────────────────────────────────────────
    const ROLE_SUPER_ADMIN  = 'super_admin';
    const ROLE_GESTOR       = 'gestor';
    const ROLE_PROFISSIONAL = 'profissional';
    const ROLE_CLIENTE      = 'cliente';

    // ── Permissões opcionais (configuráveis pelo Gestor por profissional) ──
    const PERM_CRIAR_AGENDAMENTO    = 'criar_agendamento';
    const PERM_EDITAR_AGENDAMENTO   = 'editar_agendamento';
    const PERM_CANCELAR_AGENDAMENTO = 'cancelar_agendamento';
    const PERM_VER_AGENDA_GERAL     = 'ver_agenda_geral';
    const PERM_VER_TODOS_CLIENTES   = 'ver_todos_clientes';
    const PERM_CADASTRAR_SERVICOS   = 'cadastrar_servicos';
    const PERM_VER_RELATORIOS       = 'ver_relatorios';

    protected $fillable = [
        'name', 'email', 'password',
        'tenant_id', 'role',
        'permissions', 'professional_id',
        'google_id', 'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'permissions'       => 'array',
        ];
    }

    // ── Helpers de role ────────────────────────────────────────────────────
    public function isSuperAdmin(): bool  { return $this->role === self::ROLE_SUPER_ADMIN; }
    public function isGestor(): bool      { return $this->role === self::ROLE_GESTOR; }
    public function isProfissional(): bool { return $this->role === self::ROLE_PROFISSIONAL; }
    public function isCliente(): bool     { return $this->role === self::ROLE_CLIENTE; }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isGestor()) {
            return true;
        }
        return in_array($permission, $this->permissions ?? []);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return ! $this->isCliente();
    }

    // ── Relacionamentos ────────────────────────────────────────────────────
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
