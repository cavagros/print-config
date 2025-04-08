<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function showPaymentForm(PrintConfiguration $configuration)
    {
        if ($configuration->is_paid) {
            return redirect()->route('dashboard')->with('error', 'Ce dossier a déjà été payé.');
        }

        $originalPrice = $configuration->total_price;
        $subscriptionPrice = $originalPrice * 0.85; // 15% de réduction

        return view('payment.form', compact('configuration', 'originalPrice', 'subscriptionPrice'));
    }

    public function createPaymentIntent(PrintConfiguration $configuration)
    {
        try {
            \Log::info('Création du payment intent', [
                'configuration_id' => $configuration->id,
                'total_price' => $configuration->total_price
            ]);

            // Vérifier si la configuration existe
            if (!$configuration) {
                \Log::error('Configuration non trouvée', ['configuration_id' => $configuration->id]);
                return response()->json(['error' => 'Configuration non trouvée'], 404);
            }

            // Vérifier si le paiement a déjà été effectué
            if ($configuration->is_paid) {
                \Log::error('Paiement déjà effectué', ['configuration_id' => $configuration->id]);
                return response()->json(['error' => 'Le paiement a déjà été effectué'], 400);
            }

            // Déterminer le montant en fonction du type de paiement
            $amount = $configuration->total_price;
            if (request('payment_type') === 'subscription') {
                $amount = $amount * 0.85; // 15% de réduction pour l'abonnement
            }

            // Créer le payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Convertir en centimes
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'configuration_id' => $configuration->id,
                    'payment_type' => request('payment_type', 'one_time'),
                    'user_id' => auth()->id()
                ]
            ]);

            // Mettre à jour la configuration avec le payment intent ID
            $configuration->update([
                'payment_intent_id' => $paymentIntent->id,
                'is_subscription' => request('payment_type') === 'subscription'
            ]);

            \Log::info('Payment intent créé avec succès', [
                'configuration_id' => $configuration->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du payment intent', [
                'configuration_id' => $configuration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        \Log::info('Webhook reçu dans le contrôleur - RAW', [
            'headers' => $request->headers->all(),
            'content' => $request->getContent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);

        // Vérifier que c'est bien une requête POST
        if ($request->method() !== 'POST') {
            \Log::error('Mauvaise méthode HTTP', ['method' => $request->method()]);
            return response()->json(['error' => 'Method not allowed'], 405);
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (!$signature) {
            \Log::error('Signature Stripe manquante dans les headers');
            return response()->json(['error' => 'Signature manquante'], 400);
        }

        try {
            $this->stripeService->handleWebhook($payload, $signature);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Erreur webhook dans le contrôleur', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook error'], 400);
        }
    }

    public function success(PrintConfiguration $configuration)
    {
        return redirect()->route('dashboard')
            ->with('success', 'Votre paiement a été effectué avec succès. Vous pouvez maintenant gérer vos fichiers.');
    }

    public function cancel(PrintConfiguration $configuration)
    {
        return redirect()->route('dashboard')
            ->with('error', 'Le paiement a été annulé. Vous pouvez réessayer plus tard.');
    }
} 