<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CabinetInfo extends Model
{
    protected $fillable = [
        'print_configuration_id',
        'cabinet_name',
        'address',
        'postal_code',
        'city',
        'phone',
        'contact_email'
    ];

    public function printConfiguration(): BelongsTo
    {
        return $this->belongsTo(PrintConfiguration::class);
    }
} 