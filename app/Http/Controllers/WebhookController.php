<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'customer.subscription.created':
                $subscription = $event->data->object;
                $this->handleSubscriptionCreated($subscription);
                break;

            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdated($subscription);
                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionDeleted($subscription);
                break;

            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $this->handleInvoicePaymentSucceeded($invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handleInvoicePaymentFailed($invoice);
                break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handleSubscriptionCreated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();
        if ($subscription) {
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);
        }
    }

    private function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();
        if ($subscription) {
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);
        }
    }

    private function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_id', $stripeSubscription->id)->first();
        if ($subscription) {
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);
        }
    }

    private function handleInvoicePaymentSucceeded($invoice)
    {
        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_id', $invoice->subscription)->first();
            if ($subscription) {
                $subscription->update([
                    'stripe_status' => 'active',
                ]);
            }
        }
    }

    private function handleInvoicePaymentFailed($invoice)
    {
        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_id', $invoice->subscription)->first();
            if ($subscription) {
                $subscription->update([
                    'stripe_status' => 'past_due',
                ]);
            }
        }
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        \Log::info('Début handlePaymentIntentSucceeded', [
            'payment_intent_id' => $paymentIntent->id,
            'metadata' => $paymentIntent->metadata
        ]);

        // Récupérer l'ID de la configuration depuis les métadonnées
        $configurationId = $paymentIntent->metadata->print_configuration_id ?? null;

        if (!$configurationId) {
            \Log::error('Configuration ID non trouvé dans les métadonnées', [
                'payment_intent_id' => $paymentIntent->id,
                'metadata' => $paymentIntent->metadata
            ]);
            return;
        }

        \Log::info('Configuration ID trouvé', ['configuration_id' => $configurationId]);

        // Récupérer la configuration
        $configuration = \App\Models\PrintConfiguration::find($configurationId);

        if (!$configuration) {
            \Log::error('Configuration non trouvée', [
                'configuration_id' => $configurationId,
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        \Log::info('Configuration trouvée', [
            'configuration_id' => $configuration->id,
            'current_payment_intent_id' => $configuration->payment_intent_id
        ]);

        try {
            // Mettre à jour le statut de paiement
            $configuration->update([
                'is_paid' => true,
                'paid_at' => now(),
                'payment_intent_id' => $paymentIntent->id,
                'status' => 'paid'
            ]);

            \Log::info('Configuration mise à jour via webhook', [
                'configuration_id' => $configuration->id,
                'payment_intent_id' => $paymentIntent->id,
                'is_paid' => true,
                'status' => 'paid'
            ]);

            // Mettre à jour le statut du paiement dans la table payments
            $payment = \App\Models\Payment::where('print_configuration_id', $configuration->id)
                ->where('stripe_id', $paymentIntent->id)
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'succeeded'
                ]);
                \Log::info('Paiement mis à jour', ['payment_id' => $payment->id]);
            } else {
                \Log::warning('Paiement non trouvé dans la table payments', [
                    'configuration_id' => $configuration->id,
                    'payment_intent_id' => $paymentIntent->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 