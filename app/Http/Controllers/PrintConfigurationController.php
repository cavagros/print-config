<?php

namespace App\Http\Controllers;

use App\Services\PriceCalculator;
use Illuminate\Http\Request;

class PrintConfigurationController extends Controller
{
    public function update(Request $request, PrintConfiguration $configuration)
    {
        $validated = $request->validate([
            'pages' => 'required|integer|min:1',
            'print_type' => 'required|in:color,black_white',
            'binding_type' => 'required|in:spiral,glue,staples',
            'delivery_type' => 'required|in:standard,express',
            'paper_type' => 'required|in:standard,premium,recycled',
            'format' => 'required|in:a4,a3'
        ]);

        // Calculer le nouveau prix total
        $priceCalculator = new PriceCalculator();
        $totalPrice = $priceCalculator->calculateTotalPrice(
            $validated['pages'],
            $validated['print_type'],
            $validated['binding_type'],
            $validated['delivery_type'],
            $validated['paper_type'],
            $validated['format']
        );

        // Mettre à jour la configuration avec le nouveau prix
        $configuration->update(array_merge($validated, ['total_price' => $totalPrice]));

        // Recharger la configuration pour s'assurer que les données sont à jour
        $configuration->refresh();

        return redirect()->route('dossier.files', $configuration)
            ->with('success', 'Configuration mise à jour avec succès.');
    }
} 