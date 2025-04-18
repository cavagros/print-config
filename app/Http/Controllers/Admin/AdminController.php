<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintConfiguration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\ControllerMiddlewareOptions;

#[Middleware(['auth', 'admin'])]
class AdminController extends Controller
{
    public function index()
    {
        // Récupérer tous les dossiers avec leurs utilisateurs
        $configurations = PrintConfiguration::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Séparer les dossiers payés et non payés
        $paidConfigurations = $configurations->where('is_paid', true);
        $unpaidConfigurations = $configurations->where('is_paid', false);

        // Récupérer tous les utilisateurs
        $users = User::withCount('printConfigurations')
            ->orderBy('name')
            ->get();

        // Calculer le nombre total de configurations
        $totalConfigurations = $configurations->count();

        return view('admin.dashboard', compact(
            'totalConfigurations',
            'paidConfigurations',
            'unpaidConfigurations',
            'users'
        ));
    }

    public function showUser(User $user)
    {
        // Récupérer tous les dossiers de l'utilisateur
        $configurations = $user->printConfigurations()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.user.show', compact('user', 'configurations'));
    }

    public function resetConfiguration(PrintConfiguration $configuration)
    {
        // Vérifier si le dossier est validé
        if ($configuration->status === 'file_sent') {
            // Réinitialiser le statut et débloquer les fichiers
            $configuration->update([
                'status' => 'pending',
                'is_locked' => false
            ]);

            return redirect()->back()
                ->with('success', 'Le dossier a été réinitialisé avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Impossible de réinitialiser ce dossier.');
    }

    public function refundConfiguration(PrintConfiguration $configuration)
    {
        // Vérifier si le dossier est payé
        if ($configuration->is_paid) {
            // Mettre à jour le statut de paiement
            $configuration->update([
                'is_paid' => false,
                'status' => 'pending'
            ]);

            // TODO: Implémenter la logique de remboursement Stripe ici

            return redirect()->back()
                ->with('success', 'Le remboursement a été effectué avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Ce dossier n\'a pas été payé.');
    }
} 