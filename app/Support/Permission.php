<?php

namespace App\Support;

use App\Models\User;

class Permission
{
    public static function isSuperAdmin(): bool
    {
        return (bool) optional(auth()->user())->isSuperAdmin();
    }

    public static function isGestorOrAbove(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->isGestor());
    }

    public static function isTenantMember(): bool
    {
        $user = auth()->user();
        return $user && ! $user->isCliente();
    }

    public static function has(string $permission): bool
    {
        $user = auth()->user();
        return $user && $user->hasPermission($permission);
    }

    public static function canManageAppointments(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_CRIAR_AGENDAMENTO);
    }

    public static function canEditAppointments(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_EDITAR_AGENDAMENTO);
    }

    public static function canCancelAppointments(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_CANCELAR_AGENDAMENTO);
    }

    public static function canViewAllCustomers(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_VER_TODOS_CLIENTES);
    }

    public static function canViewAllAppointments(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_VER_AGENDA_GERAL);
    }

    public static function canManageServices(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_CADASTRAR_SERVICOS);
    }

    public static function canViewReports(): bool
    {
        return self::isGestorOrAbove() || self::has(User::PERM_VER_RELATORIOS);
    }
}
