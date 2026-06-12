<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NicheCategory extends Model
{
    protected $fillable = ['key', 'name', 'icon', 'sort_order', 'active'];

    public function niches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BusinessNiche::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order');
    }
}
