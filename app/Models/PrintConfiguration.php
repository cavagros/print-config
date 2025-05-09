<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use App\Enums\PrintConfigurationStatus;

class PrintConfiguration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'total_price',
        'status',
        'step',
        'is_paid',
        'is_subscription',
        'subscription_id',
        'subscription_status',
        'id_dossier',
        'pages'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_subscription' => 'boolean',
        'step' => 'integer',
        'paid_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'pending',
        'step' => 1,
        'is_paid' => false,
        'is_subscription' => false,
    ];

    protected $appends = ['formatted_date', 'formatted_updated_date', 'payment_status'];

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->created_at)->locale('fr')->isoFormat('DD MMMM YYYY, HH:mm');
    }

    public function getFormattedUpdatedDateAttribute()
    {
        return $this->updated_at->ne($this->created_at)
            ? Carbon::parse($this->updated_at)->locale('fr')->isoFormat('DD MMMM YYYY, HH:mm')
            : 'Pas de modification';
    }

    public function getPaymentStatusAttribute()
    {
        return $this->is_paid ? 'Payé' : 'Non payé';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ConfigurationFile::class, 'print_configuration_id');
    }

    public function cabinetInfo()
    {
        return $this->hasOne(CabinetInfo::class);
    }

    public function tribunalInfo()
    {
        return $this->hasOne(TribunalInfo::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->is_subscription && 
               $this->subscription && 
               $this->subscription->isActive();
    }
}
