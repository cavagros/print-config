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
        if ($configuration->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Vous n\'êtes pas autorisé à voir ce dossier.');
        }

        // Charger explicitement les relations
        $configuration->load(['cabinetInfo', 'tribunalInfo', 'files']);

        return view('dossier.summary', compact('configuration'));
    }

    public function validate(Request $request, PrintConfiguration $configuration)
    {
        if ($configuration->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à valider ce dossier.');
        }

        try {
            // Mettre à jour le statut
            $configuration->update([
                'status' => 'validated',
                'step' => 5
            ]);

            // Notifier les administrateurs
            User::where('is_admin', true)->get()->each(function ($admin) use ($configuration) {
                $admin->notify(new DossierValidated($configuration));
            });

            return redirect()->route('dossier.summary', $configuration)
                ->with('success', 'Votre dossier a été validé avec succès et est maintenant en cours de traitement.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la validation du dossier.');
        }
    }
} 