<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\PrintConfiguration;
use Illuminate\Http\Request;

class PrintConfigurationController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth');
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        
        // Log de construction du contrôleur
        file_put_contents(storage_path('logs/debug.log'), 
            date('Y-m-d H:i:s') . " - Constructeur PrintConfigurationController appelé\n",
            FILE_APPEND
        );

        // Log de la route actuelle
        if (request()->route()) {
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Route actuelle: " . request()->route()->getName() . "\n",
                FILE_APPEND
            );
        } else {
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Aucune route trouvée\n",
                FILE_APPEND
            );
        }
    }

    public function index()
    {
        $configurations = PrintConfiguration::with('user')
            ->latest()
            ->paginate(10);

        return view('admin.configurations.index', compact('configurations'));
    }

    public function show(PrintConfiguration $configuration)
    {
        return view('admin.configurations.show', compact('configuration'));
    }

    public function destroy(PrintConfiguration $configuration)
    {
        $configuration->delete();
        return redirect()->route('admin.configurations.index')
            ->with('success', 'Configuration supprimée avec succès');
    }

    public function refund(PrintConfiguration $configuration)
    {
        try {
            // Log de début de méthode
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Méthode refund appelée pour la configuration " . $configuration->id . "\n",
                FILE_APPEND
            );

            // Vérification de base
            if (!$configuration) {
                file_put_contents(storage_path('logs/debug.log'), 
                    date('Y-m-d H:i:s') . " - ERREUR: Configuration non trouvée\n",
                    FILE_APPEND
                );
                return redirect()->back()->with('error', 'Configuration non trouvée');
            }

            // Vérifier si la configuration est payée
            if (!$configuration->is_paid) {
                file_put_contents(storage_path('logs/debug.log'), 
                    date('Y-m-d H:i:s') . " - Configuration non payée: " . $configuration->id . "\n",
                    FILE_APPEND
                );
                return redirect()->back()
                    ->with('error', 'Cette configuration n\'est pas payée.');
            }

            // Récupérer le paiement associé
            $payment = \App\Models\Payment::where('print_configuration_id', $configuration->id)
                ->where('status', 'succeeded')
                ->first();

            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Paiement trouvé: " . ($payment ? 'OUI' : 'NON') . "\n",
                FILE_APPEND
            );

            if (!$payment || !$payment->stripe_id) {
                file_put_contents(storage_path('logs/debug.log'), 
                    date('Y-m-d H:i:s') . " - ERREUR: Paiement non trouvé ou stripe_id manquant\n",
                    FILE_APPEND
                );
                return redirect()->back()
                    ->with('error', 'ID de paiement Stripe non trouvé.');
            }

            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Tentative de remboursement avec stripe_id: " . $payment->stripe_id . "\n",
                FILE_APPEND
            );

            // Créer le remboursement Stripe
            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->stripe_id,
                'reason' => 'requested_by_customer'
            ]);

            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Remboursement créé avec succès: " . $refund->id . "\n",
                FILE_APPEND
            );

            // Mettre à jour le statut de paiement
            $configuration->update([
                'is_paid' => false,
                'status' => 'refunded'
            ]);

            // Mettre à jour le statut du paiement
            $payment->update([
                'status' => 'refunded'
            ]);

            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - Statuts mis à jour avec succès\n",
                FILE_APPEND
            );

            return redirect()->back()
                ->with('success', 'Le remboursement a été effectué avec succès.');
        } catch (\Exception $e) {
            file_put_contents(storage_path('logs/debug.log'), 
                date('Y-m-d H:i:s') . " - ERREUR: " . $e->getMessage() . "\n",
                FILE_APPEND
            );
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du remboursement: ' . $e->getMessage());
        }
    }

    public function updatePaymentStatus(PrintConfiguration $configuration)
    {
        $configuration->update([
            'is_paid' => !$configuration->is_paid,
            'status' => !$configuration->is_paid ? 'paid' : 'pending'
        ]);

        return back()->with('success', 'Statut de paiement mis à jour avec succès');
    }
}
