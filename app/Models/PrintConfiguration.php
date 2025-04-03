<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PrintConfiguration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'pages',
        'print_type',
        'paper_type',
        'format',
        'binding_type',
        'delivery_type',
        'total_price',
        'is_paid',
    ];

    protected $casts = [
        'pages' => 'integer',
        'total_price' => 'decimal:2',
        'is_paid' => 'boolean',
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
        return $this->hasMany(ConfigurationFile::class);
    }
}
