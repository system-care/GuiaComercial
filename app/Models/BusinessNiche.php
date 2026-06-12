<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessNiche extends Model
{
    protected $fillable = ['niche_category_id', 'key', 'name', 'icon', 'active'];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NicheCategory::class, 'niche_category_id');
    }

    public function template(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NicheTemplate::class);
    }

    public function tenants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
