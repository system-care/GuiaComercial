<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NicheTemplate extends Model
{
    protected $fillable = ['business_niche_id', 'labels', 'custom_fields', 'default_statuses', 'default_services', 'automations'];

    protected $casts = [
        'labels'           => 'array',
        'custom_fields'    => 'array',
        'default_statuses' => 'array',
        'default_services' => 'array',
        'automations'      => 'array',
    ];

    public function niche(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BusinessNiche::class, 'business_niche_id');
    }
}
