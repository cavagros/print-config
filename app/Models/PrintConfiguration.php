<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use App\Enums\PrintConfigurationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrintConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'print_type_id',
        'page_count',
        'copy_count',
        'binding_type_id',
        'has_cover',
        'cover_type',
        'paper_type',
        'paper_size',
        'paper_weight',
        'jurisdiction_type_id',
        'pleading_type_id',
        'representation_zone_id',
        'case_number',
        'court_name',
        'court_address',
        'hearing_date',
        'is_urgent',
        'urgency_level',
        'delivery_date',
        'print_price',
        'binding_price',
        'cover_price',
        'jurisdiction_price',
        'pleading_price',
        'representation_price',
        'urgency_price',
        'total_price',
        'status',
        'is_active',
        'subscription_id',
        'subscription_status'
    ];

    protected $casts = [
        'has_cover' => 'boolean',
        'is_urgent' => 'boolean',
        'is_active' => 'boolean',
        'delivery_date' => 'datetime',
        'hearing_date' => 'date',
        'print_price' => 'decimal:2',
        'binding_price' => 'decimal:2',
        'cover_price' => 'decimal:2',
        'jurisdiction_price' => 'decimal:2',
        'pleading_price' => 'decimal:2',
        'representation_price' => 'decimal:2',
        'urgency_price' => 'decimal:2',
        'total_price' => 'decimal:2'
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

    public function files()
    {
        return $this->hasMany(ConfigurationFile::class);
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

    public function canModifyFiles()
    {
        return $this->user_id === auth()->id() && 
               $this->status !== 'file_sent' && 
               !$this->is_locked;
    }

    public function getFilesDirectory()
    {
        return 'users/' . $this->user_id . '/config_' . $this->id;
    }

    public function getFilesCount()
    {
        return $this->files()->count();
    }

    public function hasReachedMaxFiles()
    {
        return $this->getFilesCount() >= 5;
    }

    public function getProgress()
    {
        return [
            [
                'name' => 'Envoi des fichiers',
                'completed' => $this->files()->exists(),
                'current' => $this->step === 1,
                'admin_only' => false
            ],
            [
                'name' => 'Validation des fichiers',
                'completed' => $this->status === 'file_sent',
                'current' => $this->status === 'file_sent' && !$this->cabinetInfo,
                'admin_only' => false
            ],
            [
                'name' => 'Informations du cabinet',
                'completed' => $this->cabinetInfo()->exists(),
                'current' => $this->cabinetInfo()->exists() && !$this->tribunalInfo()->exists(),
                'admin_only' => false
            ],
            [
                'name' => 'Informations du tribunal',
                'completed' => $this->tribunalInfo()->exists(),
                'current' => $this->tribunalInfo()->exists() && $this->status !== 'validated',
                'admin_only' => false
            ],
            [
                'name' => 'Validation du dossier',
                'completed' => $this->status === 'validated',
                'current' => $this->status === 'validated' && !$this->is_paid,
                'admin_only' => false
            ],
            [
                'name' => 'Dossier expédié',
                'completed' => $this->expe_suivi !== null,
                'current' => $this->expe_suivi !== null && !$this->livre,
                'admin_only' => true
            ],
            [
                'name' => 'Dossier remis au tribunal',
                'completed' => $this->livre,
                'current' => $this->livre,
                'admin_only' => true
            ]
        ];
    }

    public function getCurrentStep()
    {
        $steps = $this->getProgress();
        foreach ($steps as $step => $data) {
            if ($data['current']) {
                return $step;
            }
        }
        return 1;
    }

    public function getProgressPercentage()
    {
        $totalSteps = count($this->getProgress());
        $completedSteps = collect($this->getProgress())->where('completed', true)->count();
        return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
    }

    public function printType()
    {
        return $this->belongsTo(PrintType::class);
    }

    public function bindingType()
    {
        return $this->belongsTo(BindingType::class);
    }

    public function jurisdictionType()
    {
        return $this->belongsTo(JurisdictionType::class);
    }

    public function pleadingType()
    {
        return $this->belongsTo(PleadingType::class);
    }

    public function representationZone()
    {
        return $this->belongsTo(RepresentationZone::class);
    }

    public function calculatePrintPrice()
    {
        $printType = $this->printType;
        $basePrice = $printType->price_ht;
        
        $this->print_price = $basePrice * $this->page_count * $this->copy_count;
        
        return $this->print_price;
    }

    public function calculateBindingPrice()
    {
        if (!$this->binding_type_id) {
            $this->binding_price = 0;
            return 0;
        }

        $bindingType = $this->bindingType;
        $this->binding_price = $bindingType->price_ht;
        
        return $this->binding_price;
    }

    public function calculateCoverPrice()
    {
        if (!$this->has_cover) {
            $this->cover_price = 0;
            return 0;
        }

        $this->cover_price = 2.00;
        if ($this->cover_type === 'hard') {
            $this->cover_price += 3.00;
        }
        
        return $this->cover_price;
    }

    public function calculateJurisdictionPrice()
    {
        $jurisdictionType = $this->jurisdictionType;
        $this->jurisdiction_price = $jurisdictionType->base_price ?? 0;
        
        return $this->jurisdiction_price;
    }

    public function calculatePleadingPrice()
    {
        $pleadingType = $this->pleadingType;
        $this->pleading_price = $pleadingType->base_price;
        
        return $this->pleading_price;
    }

    public function calculateRepresentationPrice()
    {
        $representationZone = $this->representationZone;
        $this->representation_price = $representationZone->base_price;
        
        return $this->representation_price;
    }

    public function calculateUrgencyPrice()
    {
        if (!$this->is_urgent) {
            $this->urgency_price = 0;
            return 0;
        }

        $basePrice = $this->print_price + 
                    $this->binding_price + 
                    $this->cover_price + 
                    $this->jurisdiction_price + 
                    $this->pleading_price + 
                    $this->representation_price;
        
        switch ($this->urgency_level) {
            case 'express':
                $this->urgency_price = $basePrice * 0.3;
                break;
            case 'urgent':
                $this->urgency_price = $basePrice * 0.5;
                break;
            default:
                $this->urgency_price = 0;
        }
        
        return $this->urgency_price;
    }

    public function calculateTotalPrice()
    {
        $this->calculatePrintPrice();
        $this->calculateBindingPrice();
        $this->calculateCoverPrice();
        $this->calculateJurisdictionPrice();
        $this->calculatePleadingPrice();
        $this->calculateRepresentationPrice();
        $this->calculateUrgencyPrice();

        $this->total_price = $this->print_price + 
                            $this->binding_price + 
                            $this->cover_price + 
                            $this->jurisdiction_price + 
                            $this->pleading_price + 
                            $this->representation_price + 
                            $this->urgency_price;

        if ($this->subscription && $this->subscription->isActive()) {
            $this->total_price = $this->total_price * 0.85;
        }

        return $this->total_price;
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
