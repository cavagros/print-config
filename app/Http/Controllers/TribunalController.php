<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class TribunalController extends Controller
{
    public function create(PrintConfiguration $configuration)
    {
        // Vérifier si les informations du cabinet existent
        if (!$configuration->cabinetInfo) {
            return redirect()->route('dossier.cabinet', $configuration)
                ->with('error', 'Vous devez d\'abord renseigner les informations du cabinet avant de pouvoir accéder aux informations du tribunal.');
        }

        // Récupérer les informations existantes du tribunal
        $tribunalInfo = $configuration->tribunalInfo;

        return view('tribunal.create', [
            'configuration' => $configuration,
            'tribunalInfo' => $tribunalInfo
        ]);
    }

    public function store(Request $request, PrintConfiguration $configuration)
    {
        // Valider les données
        $validated = $request->validate([
            'tribunal_name' => 'required|string|max:255',
            'address' => 'required|string',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
        ]);

        // Sauvegarder les informations du tribunal
        $configuration->tribunalInfo()->updateOrCreate(
            ['print_configuration_id' => $configuration->id],
            $validated
        );

        // Rediriger vers la page de résumé
        return redirect()->route('dossier.summary', $configuration)
            ->with('success', 'Les informations du tribunal ont été enregistrées avec succès.');
    }
} 