<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'customer_id', 'service_id', 'professional_id',
        'date', 'start_time', 'end_time', 'status', 'notes', 'custom_data',
        'confirmation_token', 'confirmation_status',
        'recurrence_group_id', 'recurrence_index',
    ];

    public function scopeInGroup($query, string $groupId)
    {
        return $query->where('recurrence_group_id', $groupId);
    }

    protected $casts = [
        'date'        => 'date',
        'custom_data' => 'array',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function professional(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Professional::class);
    }
}
