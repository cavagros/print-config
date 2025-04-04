<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationFile extends Model
{
    protected $fillable = [
        'print_configuration_id',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'order'
    ];

    public function printConfiguration(): BelongsTo
    {
        return $this->belongsTo(PrintConfiguration::class);
    }

    public function getSizeForHumans(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
