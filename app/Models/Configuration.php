<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'total_price',
        'is_paid',
        'is_subscription',
        'subscription_id',
        'subscription_status',
        'status',
        'step',
        'total_pages',
        'format',
        'binding_type',
        'recto_verso',
        'color',
        'procedure_type',
        'reference',
        'id_dossier',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_subscription' => 'boolean',
        'total_price' => 'decimal:2',
        'total_pages' => 'integer',
        'recto_verso' => 'boolean',
        'color' => 'boolean',
    ];
} 