<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Résumé du dossier</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-2 text-md">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Informations principales -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="grid md:grid-cols-3 gap-4">
                    <!-- Statut et prix -->
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900 flex items-center text-md">
                            <i class="fas fa-info-circle mr-2"></i>Informations générales
                        </h3>
                        <div class="grid grid-cols-2 gap-2 text-md">
                            <div>
                                <p class="text-gray-600 text-xs">Statut</p>
                                <p class="font-medium">
                                    @if($configuration->status === 'validated')
                                        <span class="text-green-600">Validé</span>
                                    @else
                                        <span class="text-yellow-600">En attente</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Prix total TTC</p>
                                <p class="font-medium text-blue-600">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</p>
                            </div>
                        </div>
                    </div>

                    <!-- Caractéristiques du dossier -->
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900 flex items-center text-md">
                            <i class="fas fa-cog mr-2"></i>Caractéristiques
                        </h3>
                        <div class="grid grid-cols-2 gap-2 text-md">
                            <div>
                                <p class="text-gray-600 text-xs">Type de procédure</p>
                                <p class="font-medium">{{ $configuration->procedure_type }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Référence</p>
                                <p class="font-medium">{{ $configuration->reference }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Options d'impression -->
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900 flex items-center text-md">
                            <i class="fas fa-print mr-2"></i>Options d'impression
                        </h3>
                        <div class="grid grid-cols-2 gap-2 text-md">
                            <div>
                                <p class="text-gray-600 text-xs">Format</p>
                                <p class="font-medium">{{ $configuration->format ?? 'Standard' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Reliure</p>
                                <p class="font-medium">{{ $configuration->binding_type ?? 'Standard' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Impression</p>
                                <p class="font-medium">{{ $configuration->recto_verso ? 'Recto-verso' : 'Recto' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Couleur</p>
                                <p class="font-medium">{{ $configuration->color ? 'Oui' : 'Non' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cabinet et Tribunal -->
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 flex items-center text-md mb-3">
                        <i class="fas fa-building mr-2"></i>Cabinet
                    </h3>
                    <div class="space-y-2 text-md">
                        <p class="font-medium">{{ $configuration->cabinetInfo->cabinet_name }}</p>
                        <p class="text-gray-600">
                            {{ $configuration->cabinetInfo->address }}, 
                            {{ $configuration->cabinetInfo->postal_code }} {{ $configuration->cabinetInfo->city }}
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <p class="text-gray-600">
                                <i class="fas fa-phone mr-1"></i>{{ $configuration->cabinetInfo->phone }}
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-envelope mr-1"></i>{{ $configuration->cabinetInfo->contact_email }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 flex items-center text-md mb-3">
                        <i class="fas fa-balance-scale mr-2"></i>Tribunal
                    </h3>
                    <div class="space-y-2 text-md">
                        <p class="font-medium">{{ $configuration->tribunalInfo->tribunal_name }}</p>
                        <p class="text-gray-600"><strong>Chambre :</strong> {{ $configuration->tribunalInfo->chamber }}</p>
                        <p class="text-gray-600"><strong>Adresse :</strong> 
                            {{ $configuration->tribunalInfo->address }}, 
                            {{ $configuration->tribunalInfo->postal_code }} {{ $configuration->tribunalInfo->city }}
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <p class="text-gray-600">
                                <i class="fas fa-phone mr-1"></i>{{ $configuration->tribunalInfo->phone }}
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-envelope mr-1"></i>{{ $configuration->tribunalInfo->contact_email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fichiers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900 flex items-center text-md">
                        <i class="fas fa-file-alt mr-2"></i>Fichiers ({{ $configuration->files->count() }})
                    </h3>
                    <p class="text-md text-gray-600">{{ $configuration->total_pages ?? 0 }} pages au total</p>
                </div>
                <div class="grid md:grid-cols-2 gap-2">
                    @foreach($configuration->files as $file)
                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded text-md">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                <span class="truncate">{{ $file->original_name }}</span>
                            </div>
                            <span class="text-gray-500 text-xs ml-2">{{ $file->size_human }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Validation -->
            @if($configuration->status !== 'validated')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <form action="{{ route('dossier.validate', $configuration) }}" method="POST">
                        @csrf
                        <div class="space-y-3">
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 text-md">
                                <div class="flex">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 mt-0.5"></i>
                                    <p class="ml-2 text-yellow-700">
                                        En validant ce dossier, vous confirmez que toutes les informations sont correctes.
                                        Cette action est irréversible.
                                    </p>
                                </div>
                            </div>
                            <div class="flex justify-center">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-md font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Valider définitivement le dossier
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 text-md">
                    <p class="font-medium">Dossier validé</p>
                    <p>Votre dossier a été validé et est en cours de traitement par nos services.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 