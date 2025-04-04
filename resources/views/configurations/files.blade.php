<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestion des fichiers - {{ $configuration->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                Retour au tableau de bord
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Message si les fichiers sont validés -->
            @if($isValidated)
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg">
                    <div class="flex items-center justify-between">
                        <p>Les fichiers ont été validés. Vous ne pouvez plus les modifier.</p>
                        <a href="{{ route('configurations.cabinet-info', $configuration) }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <span>Continuer vers les informations du cabinet</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endif
            
            <!-- Informations sur la configuration -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Informations du devis</h3>
                            <dl class="grid grid-cols-1 gap-2">
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Nombre de pages</dt>
                                    <dd class="font-medium">{{ $configuration->pages }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Type d'impression</dt>
                                    <dd class="font-medium">{{ $configuration->print_type === 'noir_blanc' ? 'Noir et blanc' : 'Couleur' }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Format</dt>
                                    <dd class="font-medium">{{ $configuration->format }}</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Statut des fichiers</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $configuration->status === 'validated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $configuration->status === 'validated' ? 'Validés' : 'En attente de validation' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-4">État du devis</h3>
                            <dl class="grid grid-cols-1 gap-2">
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Prix total</dt>
                                    <dd class="font-medium">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</dd>
                                </div>
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Statut du paiement</dt>
                                    <dd>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configuration->is_paid ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                            {{ $configuration->payment_status }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between py-2 border-b dark:border-gray-700">
                                    <dt class="text-gray-600 dark:text-gray-400">Dernière modification</dt>
                                    <dd class="font-medium">{{ $configuration->formatted_updated_date }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section d'upload -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(!$configuration->is_paid)
                        <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 p-4 rounded-lg mb-4">
                            Le devis doit être payé avant de pouvoir uploader des fichiers.
                        </div>
                    @else
                        @include('partials._file-upload', ['configuration' => $configuration, 'isValidated' => $isValidated])
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 