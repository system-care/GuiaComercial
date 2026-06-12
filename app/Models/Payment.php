<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id', 'subscription_id', 'asaas_payment_id',
        'value', 'status', 'billing_type',
        'due_date', 'paid_at', 'invoice_url',
    ];

    protected $casts = [
        'value'    => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['RECEIVED', 'CONFIRMED']);
    }
}
