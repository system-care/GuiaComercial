<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();

            if ($user->isSuperAdmin()) {
                return;
            }

            $query->where(
                $query->getModel()->getTable() . '.tenant_id',
                $user->tenant_id
            );
        });

        // Always enforce the authenticated user's tenant on create — ignore any submitted tenant_id.
        static::creating(function ($model) {
            if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Prevent tenant_id from being changed on update by non-superadmins.
        static::updating(function ($model) {
            if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public static function forTenant(int $tenantId): Builder
    {
        return static::withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }
}
