<x-app-layout>
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Récapitulatif de la commande - {{ $configuration->name }}
            </h2>
            <a href="{{ route('configurations.tribunal-info', $configuration) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                Retour aux informations du tribunal
            </a>
        </div>
    </x-slot:header>

    <!-- Barre de progression -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-center" aria-label="Progress">
                <ol role="list" class="flex items-center space-x-5 md:space-x-8">
                    <!-- Toutes les étapes précédentes sont complétées -->
                    @foreach(['Upload fichiers', 'Informations cabinet', 'Options d\'impression', 'Livraison'] as $step)
                        <li>
                            <div class="flex items-center">
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="ml-2 text-sm font-medium text-green-600">{{ $step }}</span>
                            </div>
                        </li>
                    @endforeach

                    <!-- Étape 5 : Paiement (active) -->
                    <li>
                        <div class="flex items-center">
                            <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                            </span>
                            <span class="ml-2 text-sm font-medium text-indigo-600">Paiement</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-6">Récapitulatif de votre commande</h2>

                    <!-- Informations sur les fichiers -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Fichiers du dossier</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-600">Nombre total de fichiers : <span class="font-semibold">{{ $configuration->files->count() }}</span></p>
                            <ul class="mt-2 space-y-1">
                                @foreach($configuration->files->sortBy('order') as $file)
                                    <li class="text-sm text-gray-600">{{ $file->original_name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Adresses -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Adresse du cabinet -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Adresse du cabinet</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="font-medium">{{ $configuration->cabinetInfo->cabinet_name }}</p>
                                <p>{{ $configuration->cabinetInfo->address }}</p>
                                <p>{{ $configuration->cabinetInfo->postal_code }} {{ $configuration->cabinetInfo->city }}</p>
                                <p>Tél : {{ $configuration->cabinetInfo->phone }}</p>
                                <p>Email : {{ $configuration->cabinetInfo->contact_email }}</p>
                            </div>
                        </div>

                        <!-- Adresse du tribunal -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Adresse du tribunal</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="font-medium">{{ $configuration->tribunalInfo->tribunal_name }}</p>
                                @if($configuration->tribunalInfo->chamber)
                                    <p>Chambre : {{ $configuration->tribunalInfo->chamber }}</p>
                                @endif
                                <p>{{ $configuration->tribunalInfo->address }}</p>
                                <p>{{ $configuration->tribunalInfo->postal_code }} {{ $configuration->tribunalInfo->city }}</p>
                                @if($configuration->tribunalInfo->phone)
                                    <p>Tél : {{ $configuration->tribunalInfo->phone }}</p>
                                @endif
                                @if($configuration->tribunalInfo->contact_email)
                                    <p>Email : {{ $configuration->tribunalInfo->contact_email }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Options d'impression -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Options d'impression</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <p class="text-sm text-gray-600">Type d'impression : <span class="font-semibold">{{ $configuration->print_type === 'noir_blanc' ? 'Noir et blanc' : 'Couleur' }}</span></p>
                                <p class="text-sm text-gray-600">Format : <span class="font-semibold">{{ $configuration->format }}</span></p>
                                <p class="text-sm text-gray-600">Type de papier : <span class="font-semibold">{{ $configuration->paper_type }}</span></p>
                                <p class="text-sm text-gray-600">Reliure : <span class="font-semibold">{{ $configuration->binding_type }}</span></p>
                                <p class="text-sm text-gray-600">Livraison : <span class="font-semibold">{{ $configuration->delivery_type }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Prix total -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Prix total</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</p>
                        </div>
                    </div>

                    <!-- Bouton de validation -->
                    <div class="flex justify-end">
                        <form method="POST" action="{{ route('configurations.validate-order', $configuration) }}">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Valider et procéder au paiement
                                <svg class="ml-2 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 