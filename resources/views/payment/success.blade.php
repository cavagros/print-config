<x-app-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Paiement réussi !
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    {{ $message }}
                </p>
            </div>

            <div class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Détails de votre configuration</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Nom</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $configuration->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Prix total</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($configuration->user->hasActiveSubscription())
                                        {{ number_format($configuration->total_price * 0.85, 2, ',', ' ') }} €
                                        <span class="text-sm text-gray-500 line-through">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</span>
                                    @else
                                        {{ number_format($configuration->total_price, 2, ',', ' ') }} €
                                    @endif
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                                <dd class="mt-1 text-sm text-green-600">Payé</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div>
                    <a href="{{ route('dashboard') }}" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Retourner au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Redirection automatique après 5 secondes
        setTimeout(function() {
            window.location.href = '{{ route('dashboard') }}';
        }, 5000);
    </script>
</x-app-layout> 