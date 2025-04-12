<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'print_configuration_id',
        'name',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'amount',
        'currency',
        'metadata',
        'current_period_start',
        'current_period_end'
    ];

    protected $casts = [
        'metadata' => 'array',
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printConfiguration(): BelongsTo
    {
        return $this->belongsTo(PrintConfiguration::class);
    }

    public function isActive(): bool
    {
        return $this->stripe_status === 'active' && 
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function cancel(): void
    {
        if ($this->stripe_id) {
            $stripe = app('Laravel\Cashier\Cashier')->stripe();
            $stripe->subscriptions->cancel($this->stripe_id);
        }

        $this->update([
            'stripe_status' => 'canceled',
            'ends_at' => now()
        ]);
    }
} 