<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description',
        'duration_minutes', 'buffer_minutes', 'price', 'color', 'active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'buffer_minutes'   => 'integer',
        'price'            => 'decimal:2',
        'active'           => 'boolean',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
