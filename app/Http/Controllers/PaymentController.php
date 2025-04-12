<?php

namespace App\Http\Controllers;

use App\Models\PrintConfiguration;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use Laravel\Cashier\Cashier;
use App\Models\Payment;

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

        $user = auth()->user();
        $hasActiveSubscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->exists();

        $originalPrice = $configuration->total_price;
        $subscriptionPrice = $originalPrice * 0.85; // 15% de réduction

        return view('payment.form', compact('configuration', 'originalPrice', 'subscriptionPrice', 'hasActiveSubscription'));
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            \Log::info('Début createPaymentIntent', [
                'request_data' => $request->all()
            ]);

            $request->validate([
                'print_configuration_id' => 'required|exists:print_configurations,id',
                'payment_method_id' => 'required|string'
            ]);

            $user = $request->user();
            $printConfiguration = PrintConfiguration::findOrFail($request->print_configuration_id);

            // Vérifier si l'utilisateur a déjà un abonnement actif
            $hasActiveSubscription = $user->subscriptions()
                ->where('stripe_status', 'active')
                ->exists();

            // Calculer le montant avec la réduction de 15% si l'utilisateur a un abonnement actif
            $amount = $hasActiveSubscription ? 
                $printConfiguration->total_price * 0.85 * 100 : 
                $printConfiguration->total_price * 100;

            // Le montant est déjà en centimes, pas besoin de multiplier par 100
            $amountInCents = round($amount);

            // Créer le paiement dans la base de données
            $payment = Payment::create([
                'user_id' => $user->id,
                'print_configuration_id' => $printConfiguration->id,
                'amount' => $amount / 100, // Convertir en euros pour la base de données
                'currency' => 'eur',
                'status' => 'pending',
                'metadata' => [
                    'print_configuration_id' => $printConfiguration->id,
                    'print_configuration_name' => $printConfiguration->name,
                    'has_active_subscription' => $hasActiveSubscription
                ],
            ]);

            // Créer ou récupérer le client Stripe
            if (!$user->stripe_id) {
                $stripeCustomer = $user->createAsStripeCustomer([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
            } else {
                $stripeCustomer = $user->asStripeCustomer();
            }

            // Attacher la méthode de paiement au client
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $stripeCustomer->id]);

            // Créer le PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'eur',
                'customer' => $stripeCustomer->id,
                'payment_method' => $request->payment_method_id,
                'confirm' => false,
                'payment_method_types' => ['card'],
                'metadata' => [
                    'payment_id' => $payment->id,
                    'print_configuration_id' => $printConfiguration->id,
                    'user_id' => $user->id
                ]
            ]);

            // Mettre à jour le paiement avec l'ID Stripe
            $payment->update([
                'stripe_id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ]);

            // Mettre à jour la configuration
            $printConfiguration->update([
                'is_paid' => false,
                'status' => 'pending',
                'payment_intent_id' => $paymentIntent->id
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'payment' => $payment,
                'paymentIntent' => [
                    'id' => $paymentIntent->id,
                    'status' => $paymentIntent->status
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation', [
                'error' => $e->getMessage(),
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du PaymentIntent', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // En cas d'erreur, supprimer le paiement de la base de données
            if (isset($payment)) {
                $payment->delete();
            }
            return response()->json([
                'success' => false,
                'error' => 'Payment error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
       /*
        \Log::info('Webhook reçu dans le contrôleur - RAW', [
            'headers' => $request->headers->all(),
            'content' => $request->getContent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip()
        ]);
*/
        try {
            $payload = $request->getContent();
            $signature = $request->header('Stripe-Signature');

            // Vérifier la signature Stripe
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('cashier.webhook.secret')
            );

            \Log::info('Webhook event reçu');
            
            /*
            \Log::info('Webhook event reçu', [
                'type' => $event->type,
                'data' => $event->data->object
            ]);
            */

            // Gérer les événements de paiement
            if ($event->type === 'invoice.payment_succeeded') {
                $invoice = $event->data->object;
                $subscriptionId = $invoice->subscription;
                
                if ($subscriptionId) {
                    $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
                    
                    if ($subscription) {
                        // Mettre à jour le statut de l'abonnement
                        $subscription->update([
                            'stripe_status' => 'active'
                        ]);

                        // Mettre à jour la configuration
                        $configuration = PrintConfiguration::find($subscription->print_configuration_id);
                        if ($configuration) {
                            $configuration->update([
                                'is_paid' => true,
                                'paid_at' => now(),
                                'status' => 'paid',
                                'subscription_status' => 'active'
                            ]);
                        }

                        \Log::info('Paiement d\'abonnement réussi', [
                            'subscription_id' => $subscription->id,
                            'configuration_id' => $subscription->print_configuration_id
                        ]);
                    }
                }
            }

            // Gérer les événements d'abonnement
            if (in_array($event->type, [
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted'
            ])) {
                $stripeSubscription = $event->data->object;
                
                // Récupérer l'abonnement depuis la base de données
                $subscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();

                if ($subscription) {
                    $subscription->update([
                        'stripe_status' => $stripeSubscription->status,
                        'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                        'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                        'canceled_at' => $stripeSubscription->canceled_at ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at) : null
                    ]);

                    // Mettre à jour la configuration associée
                    $configuration = PrintConfiguration::find($subscription->print_configuration_id);
                    if ($configuration) {
                        $configuration->update([
                            'subscription_status' => $stripeSubscription->status
                        ]);
                    }

                    \Log::info('Abonnement mis à jour', [
                        'subscription_id' => $subscription->id,
                        'status' => $subscription->stripe_status
                    ]);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Erreur webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function success(PrintConfiguration $configuration)
    {
        try {
            // Vérifier si c'est un abonnement
            if ($configuration->is_subscription) {
                $subscription = Subscription::where('print_configuration_id', $configuration->id)
                    ->latest()
                    ->first();

                if ($subscription) {
                    try {
                        $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
                        $subscription->update([
                            'stripe_status' => $stripeSubscription->status,
                            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                            'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                        ]);

                        $configuration->update([
                            'subscription_status' => $stripeSubscription->status,
                            'is_paid' => true,
                            'status' => 'paid',
                            'step' => 2
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors de la vérification de l\'abonnement', [
                            'configuration_id' => $configuration->id,
                            'subscription_id' => $subscription->stripe_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                // Pour les paiements uniques
                $configuration->update([
                    'is_paid' => true,
                    'status' => 'paid',
                    'step' => 2
                ]);
            }

            // Rafraîchir la configuration pour avoir les données à jour
            $configuration->refresh();

            return view('payment.success', [
                'configuration' => $configuration,
                'message' => 'Votre paiement a été traité avec succès !'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la confirmation du paiement', [
                'configuration_id' => $configuration->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Une erreur est survenue lors de la confirmation de votre paiement. Veuillez rafraîchir la page.');
        }
    }

    public function cancel(PrintConfiguration $configuration)
    {
        return redirect()->route('dashboard')
            ->with('error', 'Le paiement a été annulé. Vous pouvez réessayer plus tard.');
    }

    public function checkSubscriptionStatus(PrintConfiguration $configuration)
    {
        try {
            // Vérifier si c'est un abonnement
            if (!$configuration->is_subscription) {
                return response()->json([
                    'is_subscribed' => false,
                    'message' => 'Ce n\'est pas un abonnement'
                ]);
            }

            $subscription = Subscription::where('print_configuration_id', $configuration->id)
                ->latest()
                ->first();

            if (!$subscription) {
                return response()->json([
                    'is_subscribed' => false,
                    'message' => 'Aucun abonnement trouvé'
                ]);
            }

            // Vérifier si l'abonnement est actif dans Stripe
            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
            
            $status = $stripeSubscription->status;
            $currentPeriodEnd = date('Y-m-d H:i:s', $stripeSubscription->current_period_end);
            
            \Log::info('État de l\'abonnement vérifié', [
                'configuration_id' => $configuration->id,
                'subscription_id' => $subscription->stripe_id,
                'status' => $status,
                'current_period_end' => $currentPeriodEnd
            ]);

            // Mettre à jour le statut dans la base de données
            $subscription->update([
                'stripe_status' => $status,
                'current_period_end' => $currentPeriodEnd
            ]);

            $configuration->update([
                'subscription_status' => $status
            ]);

            return response()->json([
                'is_subscribed' => $status === 'active',
                'status' => $status,
                'current_period_end' => $currentPeriodEnd,
                'message' => $status === 'active' ? 'Abonnement actif' : 'Abonnement inactif'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification de l\'abonnement', [
                'configuration_id' => $configuration->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'is_subscribed' => false,
                'message' => 'Erreur lors de la vérification de l\'abonnement'
            ], 500);
        }
    }

    public function createSubscription(Request $request)
    {
        try {
            \Log::info('Début createSubscription', [
                'request_data' => $request->all()
            ]);

            $request->validate([
                'print_configuration_id' => 'required|exists:print_configurations,id',
                'payment_method_id' => 'required|string'
            ]);

            $user = $request->user();
            $printConfiguration = PrintConfiguration::findOrFail($request->print_configuration_id);

            // Vérifier si l'utilisateur a déjà un abonnement actif
            $hasActiveSubscription = $user->subscriptions()
                ->where('stripe_status', 'active')
                ->exists();

            if ($hasActiveSubscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Subscription error',
                    'message' => 'Vous avez déjà un abonnement actif'
                ], 400);
            }

            // Calculer le montant initial avec la réduction de 15%
            $initialAmount = $printConfiguration->total_price * 0.85;
            $initialAmountInCents = round($initialAmount * 100); // Convertir en centimes pour Stripe

            // Créer ou récupérer le client Stripe
            if (!$user->stripe_id) {
                $stripeCustomer = $user->createAsStripeCustomer([
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
            } else {
                $stripeCustomer = $user->asStripeCustomer();
            }

            // Attacher la méthode de paiement au client
            $paymentMethod = \Stripe\PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $stripeCustomer->id]);

            // Créer l'abonnement dans la base de données
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'print_configuration_id' => $printConfiguration->id,
                'name' => 'default',
                'stripe_status' => 'incomplete',
                'amount' => $initialAmount, // Convertir en euros pour la base de données
                'currency' => 'eur',
                'metadata' => [
                    'print_configuration_id' => $printConfiguration->id,
                    'print_configuration_name' => $printConfiguration->name,
                ],
            ]);

            // Créer le paiement initial
            $initialPayment = \Stripe\PaymentIntent::create([
                'amount' => $initialAmountInCents,
                'currency' => 'eur',
                'customer' => $stripeCustomer->id,
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'payment_method_types' => ['card'],
                'metadata' => [
                    'print_configuration_id' => $printConfiguration->id,
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'type' => 'initial_payment'
                ]
            ]);

            // Créer l'abonnement Stripe
            $stripeSubscription = \Stripe\Subscription::create([
                'customer' => $stripeCustomer->id,
                'items' => [
                    [
                        'price' => 'price_1HkD8ECgUDmDw9052DvhximE' // Prix de l'abonnement mensuel
                    ]
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'payment_method_types' => ['card'],
                    'save_default_payment_method' => 'on_subscription'
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'print_configuration_id' => $printConfiguration->id
                ]
            ]);

            // Mettre à jour l'abonnement avec les informations Stripe
            $subscription->update([
                'stripe_id' => $stripeSubscription->id,
                'stripe_price' => 'price_1HkD8ECgUDmDw9052DvhximE',
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);

            // Mettre à jour la configuration
            $printConfiguration->update([
                'is_paid' => true,
                'status' => 'paid',
                'payment_intent_id' => $initialPayment->id
            ]);

            return response()->json([
                'success' => true,
                'clientSecret' => $stripeSubscription->latest_invoice->payment_intent->client_secret,
                'subscription' => $subscription,
                'initialPayment' => [
                    'id' => $initialPayment->id,
                    'status' => $initialPayment->status,
                    'amount' => $initialAmountInCents
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation', [
                'error' => $e->getMessage(),
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Validation error',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de l\'abonnement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // En cas d'erreur, supprimer l'abonnement de la base de données
            if (isset($subscription)) {
                $subscription->delete();
            }
            return response()->json([
                'success' => false,
                'error' => 'Subscription error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelSubscription(PrintConfiguration $configuration)
    {
        try {
            $subscription = Subscription::where('print_configuration_id', $configuration->id)
                ->where('stripe_status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucun abonnement actif trouvé'
                ], 400);
            }

            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
            $stripeSubscription->cancel();

            $subscription->update([
                'stripe_status' => 'canceled',
                'canceled_at' => now()
            ]);

            $configuration->update([
                'subscription_status' => 'canceled'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Abonnement annulé avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation de l\'abonnement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 