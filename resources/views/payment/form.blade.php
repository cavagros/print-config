<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Paiement - {{ $configuration->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Options de paiement -->
                        <div class="mt-8" x-data="{ selectedPaymentType: 'one_time' }">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Choisissez votre mode de paiement</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Inputs radio cachés -->
                                <input type="radio" name="payment_type" value="one_time" x-model="selectedPaymentType" class="hidden" checked>
                                @if(!$hasActiveSubscription)
                                    <input type="radio" name="payment_type" value="subscription" x-model="selectedPaymentType" class="hidden">
                                @endif

                                <!-- Prix -->
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Prix initial</span>
                                        <span class="text-lg font-semibold">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</span>
                                    </div>
                                    @if(auth()->user()->hasActiveSubscription())
                                        <div class="mt-2 flex justify-between items-center text-green-600">
                                            <span>Réduction abonnement (15%)</span>
                                            <span class="text-lg font-semibold">-{{ number_format($configuration->total_price * 0.15, 2, ',', ' ') }} €</span>
                                        </div>
                                        <div class="mt-2 pt-2 border-t flex justify-between items-center">
                                            <span class="font-medium">Prix final</span>
                                            <span class="text-xl font-bold text-green-600">{{ number_format($configuration->total_price * 0.85, 2, ',', ' ') }} €</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Paiement unique -->
                                <div class="border rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer"
                                     @click="selectedPaymentType = 'one_time'"
                                     :class="{ 'border-blue-500 bg-blue-50': selectedPaymentType === 'one_time' }">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium">Paiement unique</h4>
                                            <p class="text-gray-600 text-sm mt-1">Payez une seule fois pour ce dossier</p>
                                        </div>
                                        <div class="text-right">
                                            @if($hasActiveSubscription)
                                                <p class="font-bold text-lg">{{ number_format($configuration->total_price * 0.85 , 2, ',', ' ') }} €</p>
                                            @else
                                                <p class="font-bold text-lg">{{ number_format($configuration->total_price , 2, ',', ' ') }} €</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Abonnement -->
                                @if(!$hasActiveSubscription)
                                    <div class="border rounded-lg p-4 hover:border-blue-500 transition-colors cursor-pointer"
                                         @click="selectedPaymentType = 'subscription'"
                                         :class="{ 'border-blue-500 bg-blue-50': selectedPaymentType === 'subscription' }">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center">
                                                    <h4 class="font-medium">Abonnement mensuel</h4>
                                                    <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                        -15%
                                                    </span>
                                                </div>
                                                <p class="text-gray-600 text-sm mt-1">Bénéficiez de 15% de réduction sur tous vos achats</p>
                                                <p class="text-gray-500 text-sm mt-1">29€/mois, résiliable à tout moment</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-lg text-green-600">{{ number_format($configuration->total_price * 0.85 , 2, ',', ' ') }} €</p>
                                                <p class="text-gray-500 text-sm">+ 29€/mois</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Formulaire de carte bancaire -->
                            <div class="mt-8">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informations de paiement</h3>
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                                    <form id="payment-form">
                                        @csrf
                                        <div class="space-y-4">
                                            <!-- Nom sur la carte -->
                                            <div>
                                                <label for="cardholder-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Nom sur la carte
                                                </label>
                                                <input type="text" id="cardholder-name" 
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                       required>
                                            </div>

                                            <!-- Élément de carte Stripe -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Informations de la carte
                                                </label>
                                                <div id="card-element" class="mt-1 p-3 border border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600">
                                                    <!-- Stripe injectera l'élément de carte ici -->
                                                </div>
                                                <div id="card-errors" class="mt-2 text-sm text-red-600" role="alert"></div>
                                            </div>
                                        </div>

                                        <!-- Bouton de paiement -->
                                        <div class="mt-6">
                                            <button type="submit" id="submit-button"
                                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <span id="button-text">Payer</span>
                                                <div id="spinner" class="hidden">
                                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Récapitulatif -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4">Récapitulatif</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Dossier</span>
                                    <span class="font-medium">{{ $configuration->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Prix initial</span>
                                    <span class="font-medium">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</span>
                                </div>
                                @if(!$hasActiveSubscription)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Prix avec abonnement</span>
                                        <span class="font-medium text-green-600">{{ number_format($configuration->total_price * 0.85, 2, ',', ' ') }} €</span>
                                    </div>
                                @endif
                                <div class="border-t pt-4">
                                    <div class="flex justify-between font-semibold">
                                        <span>Total à payer</span>
                                        <span x-text="selectedPaymentType === 'subscription' ? '{{ number_format($configuration->total_price * 0.85 , 2, ',', ' ') }} € + 29€/mois' : '{{ number_format($configuration->total_price, 2, ',', ' ') }} €'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            const elements = stripe.elements();
            const card = elements.create('card', {
                style: {
                    base: {
                        color: '#32325d',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                }
            });

            card.mount('#card-element');

            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            const buttonText = document.getElementById('button-text');
            const spinner = document.getElementById('spinner');

            card.addEventListener('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                // Désactiver le bouton et afficher le spinner
                submitButton.disabled = true;
                buttonText.textContent = 'Traitement...';
                spinner.classList.remove('hidden');

                try {
                    const { error: submitError } = await elements.submit();
                    if (submitError) {
                        throw new Error(submitError.message);
                    }

                    // Créer la méthode de paiement
                    const { paymentMethod, error: paymentMethodError } = await stripe.createPaymentMethod({
                        type: 'card',
                        card: card,
                        billing_details: {
                            name: document.getElementById('cardholder-name').value
                        }
                    });

                    if (paymentMethodError) {
                        throw new Error(paymentMethodError.message);
                    }

                    const isSubscription = document.querySelector('input[name="payment_type"]:checked').value === 'subscription';
                    const route = isSubscription ? '{{ route('payment.subscription', $configuration) }}' : '{{ route('payment.create', $configuration) }}';

                    const response = await fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            print_configuration_id: '{{ $configuration->id }}',
                            payment_method_id: paymentMethod.id,
                            is_subscription: isSubscription
                        })
                    });

                    const responseData = await response.json();

                    if (!response.ok || !responseData.success) {
                        throw new Error(responseData.message || 'Une erreur est survenue lors du traitement du paiement');
                    }

                    console.log('Réponse du serveur:', responseData);

                    if (isSubscription) {
                        // Gérer l'abonnement
                        if (responseData.initialPayment && responseData.initialPayment.status === 'succeeded') {
                            // Le paiement initial a réussi, confirmer l'abonnement
                            const { error: confirmError } = await stripe.confirmCardPayment(responseData.clientSecret, {
                                payment_method: paymentMethod.id
                            });

                            if (confirmError) {
                                throw new Error(confirmError.message || 'Une erreur est survenue lors de la confirmation de l\'abonnement');
                            }

                            // Rediriger vers la page de succès
                            window.location.href = '{{ route('payment.success', $configuration) }}';
                        } else {
                            throw new Error('Le paiement initial a échoué');
                        }
                    } else {
                        // Gérer le paiement unique
                        const { error: confirmError, paymentIntent } = await stripe.confirmCardPayment(responseData.clientSecret, {
                            payment_method: paymentMethod.id
                        });

                        if (confirmError) {
                            throw new Error(confirmError.message || 'Une erreur est survenue lors de la confirmation du paiement');
                        }

                        if (paymentIntent.status === 'succeeded') {
                            window.location.href = '{{ route('payment.success', $configuration) }}';
                        } else if (paymentIntent.status === 'requires_action') {
                            const { error: actionError } = await stripe.handleCardAction(responseData.clientSecret);
                            
                            if (actionError) {
                                throw new Error(actionError.message || 'Une erreur est survenue lors de l\'authentification 3D Secure');
                            }
                            
                            window.location.href = '{{ route('payment.success', $configuration) }}';
                        } else {
                            throw new Error('Le paiement n\'a pas été confirmé. Statut: ' + paymentIntent.status);
                        }
                    }
                } catch (error) {
                    console.error('Erreur complète:', error);
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = error.message || 'Une erreur est survenue lors du traitement du paiement';
                    errorElement.classList.remove('hidden');
                    
                    // Réactiver le bouton et cacher le spinner
                    submitButton.disabled = false;
                    buttonText.textContent = 'Payer';
                    spinner.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-app-layout> 