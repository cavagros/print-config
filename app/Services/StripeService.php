<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Price;
use Stripe\Product;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Subscription;
use App\Models\PrintConfiguration;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Illuminate\Support\Facades\DB;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(PrintConfiguration $configuration, bool $isSubscription = false)
    {
        try {
            $amount = $configuration->total_price; // Le montant est déjà en centimes
            $description = "Paiement pour le dossier {$configuration->name}";

            if ($isSubscription) {
                // Créer un produit et un prix pour l'abonnement
                $product = Product::create([
                    'name' => "Abonnement - {$configuration->name}",
                    'type' => 'service',
                ]);

                $price = Price::create([
                    'product' => $product->id,
                    'unit_amount' => $amount,
                    'currency' => 'eur',
                    'recurring' => ['interval' => 'month'],
                ]);

                return [
                    'price_id' => $price->id,
                    'product_id' => $product->id,
                ];
            } else {
                // Paiement unique
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $configuration->name,
                            ],
                            'unit_amount' => $amount,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => route('payment.success', ['configuration' => $configuration->id]),
                    'cancel_url' => route('payment.cancel', ['configuration' => $configuration->id]),
                    'metadata' => [
                        'configuration_id' => $configuration->id,
                        'is_subscription' => false,
                    ],
                ]);

                return [
                    'session_id' => $session->id,
                    'public_key' => config('services.stripe.key'),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur Stripe: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleWebhook($payload, $signature)
    {
        try {
            \Log::info('Webhook reçu - Début du traitement', [
                'signature' => $signature,
                'payload_length' => strlen($payload)
            ]);

            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

            \Log::info('Webhook validé', [
                'type' => $event->type,
                'id' => $event->id,
                'object' => get_class($event->data->object)
            ]);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                default:
                    \Log::info('Type d\'événement non géré', ['type' => $event->type]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur webhook Stripe', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        \Log::info('Début handlePaymentIntentSucceeded', [
            'payment_intent_id' => $paymentIntent->id,
            'status' => $paymentIntent->status,
            'metadata' => $paymentIntent->metadata
        ]);

        // Récupérer l'ID de la configuration depuis les métadonnées
        $configurationId = $paymentIntent->metadata->configuration_id ?? null;

        if (!$configurationId) {
            \Log::error('Configuration ID non trouvé dans les métadonnées', [
                'payment_intent_id' => $paymentIntent->id,
                'metadata' => $paymentIntent->metadata
            ]);
            return;
        }

        \Log::info('Configuration ID trouvé', ['configuration_id' => $configurationId]);

        // Récupérer la configuration
        $configuration = PrintConfiguration::find($configurationId);

        if (!$configuration) {
            \Log::error('Configuration non trouvée', [
                'configuration_id' => $configurationId,
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        \Log::info('Configuration trouvée avant mise à jour', [
            'configuration_id' => $configuration->id,
            'is_paid' => $configuration->is_paid,
            'payment_intent_id' => $configuration->payment_intent_id
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour le statut de paiement
            $configuration->is_paid = 1;
            $configuration->paid_at = now();
            $configuration->payment_intent_id = $paymentIntent->id;
            $configuration->save();

            \Log::info('Configuration mise à jour', [
                'configuration_id' => $configuration->id,
                'is_paid' => $configuration->is_paid,
                'paid_at' => $configuration->paid_at,
                'payment_intent_id' => $configuration->payment_intent_id
            ]);

            DB::commit();

            // Vérifier que la mise à jour a bien été effectuée
            $configuration->refresh();
            \Log::info('État final de la configuration', [
                'configuration_id' => $configuration->id,
                'is_paid' => $configuration->is_paid,
                'paid_at' => $configuration->paid_at,
                'payment_intent_id' => $configuration->payment_intent_id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la mise à jour de la configuration', [
                'configuration_id' => $configuration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $configuration = PrintConfiguration::where('payment_intent_id', $paymentIntent->id)->first();

        if ($configuration) {
            Log::error('Paiement échoué pour la configuration', [
                'configuration_id' => $configuration->id,
                'payment_intent_id' => $paymentIntent->id,
                'error' => $paymentIntent->last_payment_error
            ]);
        }
    }

    protected function createSubscription($configuration, $paymentIntent)
    {
        try {
            $subscription = \Stripe\Subscription::create([
                'customer' => $paymentIntent->customer,
                'items' => [[
                    'price' => $this->getPriceId($configuration)
                ]],
                'metadata' => [
                    'configuration_id' => $configuration->id
                ]
            ]);

            $configuration->update([
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status
            ]);

            Log::info('Abonnement créé avec succès', [
                'configuration_id' => $configuration->id,
                'subscription_id' => $subscription->id
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'abonnement', [
                'configuration_id' => $configuration->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getPriceId($configuration)
    {
        // Créer ou récupérer le price ID pour l'abonnement
        $price = \Stripe\Price::create([
            'product' => config('services.stripe.product_id'),
            'unit_amount' => (int)($configuration->total_price * 0.85), // 15% de réduction
            'currency' => 'eur',
            'recurring' => [
                'interval' => 'month'
            ]
        ]);

        return $price->id;
    }
} 