<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function create(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        // Vérifier si les fichiers sont validés
        if ($configuration->status !== 'file_sent') {
            return redirect()->route('dossier.files', $configuration)
                ->with('error', 'Vous devez d\'abord valider vos fichiers.');
        }

        // Récupérer les informations existantes du cabinet
        $cabinetInfo = $configuration->cabinetInfo;

        return view('cabinet.create', compact('configuration', 'cabinetInfo'));
    }

    public function store(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

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

        // Rediriger vers la page du tribunal
        return redirect()->route('dossier.tribunal', $configuration)
            ->with('success', 'Les informations du cabinet ont été enregistrées avec succès.');
    }
} 