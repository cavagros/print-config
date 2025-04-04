<?php

namespace App\Enums;

enum PrintConfigurationStatus: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case INFO_COMPLETED = 'info_completed';
    case OPTIONS_COMPLETED = 'options_completed';
    case DELIVERY_COMPLETED = 'delivery_completed';
    case READY_FOR_PAYMENT = 'ready_for_payment';
    case COMPLETED = 'completed';
} 