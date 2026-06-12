<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    const STATUS_TRIAL           = 'trial';
    const STATUS_ACTIVE          = 'active';
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_OVERDUE         = 'overdue';
    const STATUS_CANCELED        = 'canceled';
    const STATUS_SUSPENDED       = 'suspended';

    const GRACE_DAYS = 3;

    protected $fillable = [
        'tenant_id', 'plan_id', 'asaas_subscription_id',
        'status', 'billing_type',
        'trial_ends_at', 'current_period_end', 'overdue_since',
    ];

    protected $casts = [
        'trial_ends_at'      => 'datetime',
        'current_period_end' => 'datetime',
        'overdue_since'      => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_TRIAL,
            self::STATUS_ACTIVE,
            self::STATUS_PENDING_PAYMENT,
        ]);
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function isGracePeriodExpired(): bool
    {
        if ($this->status !== self::STATUS_OVERDUE || ! $this->overdue_since) {
            return false;
        }

        return $this->overdue_since->diffInDays(now()) >= self::GRACE_DAYS;
    }

    public function graceDaysRemaining(): int
    {
        if ($this->status !== self::STATUS_OVERDUE || ! $this->overdue_since) {
            return self::GRACE_DAYS;
        }

        return max(0, self::GRACE_DAYS - (int) $this->overdue_since->diffInDays(now()));
    }

    public function isAccessBlocked(): bool
    {
        if (in_array($this->status, [self::STATUS_CANCELED, self::STATUS_SUSPENDED])) {
            return true;
        }

        if ($this->status === self::STATUS_OVERDUE && $this->isGracePeriodExpired()) {
            return true;
        }

        return false;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_TRIAL           => 'Trial',
            self::STATUS_ACTIVE          => 'Ativo',
            self::STATUS_PENDING_PAYMENT => 'Aguardando pagamento',
            self::STATUS_OVERDUE         => 'Inadimplente',
            self::STATUS_CANCELED        => 'Cancelado',
            self::STATUS_SUSPENDED       => 'Suspenso',
            default                      => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_TRIAL           => 'info',
            self::STATUS_ACTIVE          => 'success',
            self::STATUS_PENDING_PAYMENT => 'warning',
            self::STATUS_OVERDUE         => 'danger',
            self::STATUS_CANCELED        => 'gray',
            self::STATUS_SUSPENDED       => 'danger',
            default                      => 'gray',
        };
    }
}
