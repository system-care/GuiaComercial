<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'billing_cycle',
        'trial_days', 'max_appointments_month', 'max_professionals',
        'max_services', 'features', 'active', 'sort_order',
    ];

    protected $casts = [
        'price'                   => 'decimal:2',
        'trial_days'              => 'integer',
        'max_appointments_month'  => 'integer',
        'max_professionals'       => 'integer',
        'max_services'            => 'integer',
        'features'                => 'array',
        'active'                  => 'boolean',
        'sort_order'              => 'integer',
    ];

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    public function formattedPrice(): string
    {
        return $this->isFree()
            ? 'Grátis'
            : 'R$ ' . number_format($this->price, 2, ',', '.');
    }
}
