<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationOtp extends Model
{
    protected $fillable = [
        'company_name',
        'gestor_name',
        'email',
        'phone',
        'password',
        'business_niche_id',
        'business_niche_ids',
        'code',
        'ip',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'         => 'datetime',
            'verified_at'        => 'datetime',
            'business_niche_ids' => 'array',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function businessNiche(): BelongsTo
    {
        return $this->belongsTo(BusinessNiche::class);
    }
}
