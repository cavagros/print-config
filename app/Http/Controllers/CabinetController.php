<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function create(PrintConfiguration $configuration)
    {
        // Récupérer les informations existantes du cabinet
        $cabinetInfo = $configuration->cabinetInfo;

        return view('cabinet.create', [
            'configuration' => $configuration,
            'cabinetInfo' => $cabinetInfo
        ]);
    }

    public function store(Request $request, PrintConfiguration $configuration)
    {
        // Valider les données
        $validated = $request->validate([
            'cabinet_name' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
        ]);

        // Sauvegarder les informations du cabinet
        $configuration->cabinetInfo()->updateOrCreate(
            ['print_configuration_id' => $configuration->id],
            $validated
        );

        // Rediriger vers le formulaire du tribunal
        return redirect()->route('dossier.tribunal', $configuration)
            ->with('success', 'Les informations du cabinet ont été enregistrées avec succès.');
    }
} 