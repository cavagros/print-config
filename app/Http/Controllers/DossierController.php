<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Models\User;
use App\Notifications\DossierValidated;
use Illuminate\Http\Request;

class DossierController extends Controller
{
    public function summary(PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        // Vérifier si toutes les informations nécessaires sont présentes
        if (!$configuration->cabinetInfo || !$configuration->tribunalInfo) {
            return redirect()->route('dossier.cabinet', $configuration)
                ->with('error', 'Vous devez d\'abord compléter toutes les informations.');
        }

        return view('dossier.summary', compact('configuration'));
    }

    public function validate(Request $request, PrintConfiguration $configuration)
    {
        // Vérifier si l'utilisateur est autorisé
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette page.');
        }

        // Vérifier si le dossier n'est pas déjà validé
        if ($configuration->status === 'validated') {
            return redirect()->route('dossier.summary', $configuration)
                ->with('error', 'Ce dossier a déjà été validé.');
        }

        // Vérifier si toutes les informations nécessaires sont présentes
        if (!$configuration->cabinetInfo || !$configuration->tribunalInfo || $configuration->files->count() === 0) {
            return redirect()->route('dossier.summary', $configuration)
                ->with('error', 'Le dossier est incomplet. Veuillez vérifier toutes les informations.');
        }

        // Mettre à jour le statut
        $configuration->update([
            'status' => 'validated',
            'validated_at' => now()
        ]);

        // Notifier les administrateurs
        $admins = User::where('is_admin', true)->get();
        foreach ($admins as $admin) {
            $admin->notify(new DossierValidated($configuration));
        }

        return redirect()->route('dossier.summary', $configuration)
            ->with('success', 'Votre dossier a été validé avec succès et sera traité par nos services.');
    }
} 