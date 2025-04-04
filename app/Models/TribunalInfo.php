<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TribunalInfo extends Model
{
    protected $fillable = [
        'print_configuration_id',
        'tribunal_name',
        'chamber',
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