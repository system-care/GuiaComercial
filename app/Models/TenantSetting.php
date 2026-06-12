<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'labels', 'custom_fields', 'statuses', 'working_hours', 'settings',
        'latitude', 'longitude', 'city_normalized', 'neighborhood_normalized',
        'service_radius_km', 'service_mode',
    ];

    protected $casts = [
        'labels'             => 'array',
        'custom_fields'      => 'array',
        'statuses'           => 'array',
        'working_hours'      => 'array',
        'settings'           => 'array',
        'latitude'           => 'float',
        'longitude'          => 'float',
        'service_radius_km'  => 'integer',
    ];
}
