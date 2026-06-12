<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Zap\Models\Concerns\HasSchedules;

class Professional extends Model
{
    use BelongsToTenant, HasSchedules, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'specialty', 'bio', 'social_links',
        'email', 'phone', 'color', 'avatar_path', 'schedule_config', 'active',
    ];

    protected $casts = [
        'active'          => 'boolean',
        'schedule_config' => 'array',
        'social_links'    => 'array',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class);
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
