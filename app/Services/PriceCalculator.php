<?php

namespace App\Services;

class PriceCalculator
{
    private const BASE_PRICE_PER_PAGE = 0.10;
    private const COLOR_PRICE_MULTIPLIER = 1.5;
    private const BINDING_PRICES = [
        'spiral' => 5.00,
        'glue' => 3.00,
        'staples' => 1.00
    ];
    private const DELIVERY_PRICES = [
        'standard' => 5.00,
        'express' => 10.00
    ];
    private const PAPER_TYPE_PRICES = [
        'standard' => 0.00,
        'premium' => 2.00,
        'recycled' => 1.00
    ];
    private const FORMAT_PRICES = [
        'a4' => 0.00,
        'a3' => 1.50
    ];

    public function calculateTotalPrice(
        int $pages,
        string $printType,
        string $bindingType,
        string $deliveryType,
        string $paperType,
        string $format
    ): float {
        // Prix de base par page
        $basePrice = $pages * self::BASE_PRICE_PER_PAGE;

        // Multiplicateur pour l'impression couleur
        if ($printType === 'color') {
            $basePrice *= self::COLOR_PRICE_MULTIPLIER;
        }

        // Ajout des coûts de reliure
        $bindingPrice = self::BINDING_PRICES[$bindingType] ?? 0;

        // Ajout des coûts de livraison
        $deliveryPrice = self::DELIVERY_PRICES[$deliveryType] ?? 0;

        // Ajout des coûts du type de papier
        $paperPrice = self::PAPER_TYPE_PRICES[$paperType] ?? 0;

        // Ajout des coûts du format
        $formatPrice = self::FORMAT_PRICES[$format] ?? 0;

        // Calcul du prix total
        $totalPrice = $basePrice + $bindingPrice + $deliveryPrice + $paperPrice + $formatPrice;

        // Arrondir à 2 décimales
        return round($totalPrice, 2);
    }
} 