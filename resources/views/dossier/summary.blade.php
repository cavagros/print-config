<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Résumé du dossier</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- En-tête avec numéro de dossier et actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Dossier #{{ $configuration->id_dossier }}</h2>
                        <p class="text-gray-500">Créé le {{ $configuration->formatted_date }}</p>
                    </div>
                    @if(auth()->user()->is_admin)
                    <div class="flex space-x-4">
                        <a href="{{ route('dossier.show', $configuration) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-edit mr-2"></i> Modifier
                        </a>
                        <form action="{{ route('admin.configurations.destroy', $configuration) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce dossier ?')">
                                <i class="fas fa-trash mr-2"></i> Supprimer
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Barre de progression -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="mb-4">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Progression</span>
                            <span class="text-sm font-medium text-gray-700">{{ $configuration->getProgressPercentage() }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $configuration->getProgressPercentage() }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between overflow-x-auto">
                        @foreach($configuration->getProgress() as $data)
                            <div class="flex flex-col items-center min-w-[120px]">
                                <div class="mb-1">
                                    @if($data['completed'])
                                        <i class="fas fa-check-circle text-green-500 text-lg"></i>
                                    @elseif($data['current'])
                                        <i class="fas fa-arrow-right text-blue-500 text-lg"></i>
                                    @else
                                        <i class="far fa-circle text-gray-300 text-lg"></i>
                                    @endif
                                </div>
                                <p class="text-sm text-center font-medium {{ $data['completed'] ? 'text-green-600' : ($data['current'] ? 'text-blue-600' : 'text-gray-500') }}">
                                    {{ $data['name'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Informations générales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Informations générales</h3>
                        <dl class="space-y-4">
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Statut</dt>
                                <dd class="font-medium">{{ $configuration->status }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Prix total</dt>
                                <dd class="font-medium">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Paiement</dt>
                                <dd class="font-medium {{ $configuration->is_paid ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $configuration->payment_status }}
                                </dd>
                            </div>
                            @if(!$configuration->is_paid)
                            <div class="flex justify-between py-2">
                                <dt class="text-gray-600">Action</dt>
                                <dd>
                                    <a href="{{ route('products.configure', ['configuration_id' => $configuration->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                        <i class="fas fa-credit-card mr-2"></i> Payer le dossier
                                    </a>
                                </dd>
                            </div>
                            @endif
                            @if($configuration->expe_suivi)
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">N° de suivi</dt>
                                <dd class="font-medium">{{ $configuration->expe_suivi }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Actions disponibles -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Actions disponibles</h3>
                        <div class="space-y-4">
                            @if($configuration->is_paid || auth()->user()->is_admin)
                                <a href="{{ route('dossier.files', $configuration) }}" class="inline-flex items-center w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    <i class="fas fa-file-upload mr-2"></i> Gérer les fichiers
                                </a>
                                <a href="{{ route('dossier.cabinet', $configuration) }}" class="inline-flex items-center w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    <i class="fas fa-building mr-2"></i> Modifier le cabinet
                                </a>
                                <a href="{{ route('dossier.tribunal', $configuration) }}" class="inline-flex items-center w-full px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                                    <i class="fas fa-balance-scale mr-2"></i> Modifier le tribunal
                                </a>
                            @else
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                Pour accéder aux fonctionnalités d'édition, veuillez d'abord effectuer le paiement du dossier.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('dossier.show', $configuration) }}" class="inline-flex items-center w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    <i class="fas fa-credit-card mr-2"></i> Payer le dossier
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du cabinet -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informations du cabinet</h3>
                    @if($configuration->cabinetInfo)
                        <dl class="space-y-4">
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Nom du cabinet</dt>
                                <dd class="font-medium">{{ $configuration->cabinetInfo->cabinet_name }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Adresse</dt>
                                <dd class="font-medium">{{ $configuration->cabinetInfo->cabinet_address }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Téléphone</dt>
                                <dd class="font-medium">{{ $configuration->cabinetInfo->cabinet_phone }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-gray-500">Aucune information de cabinet disponible</p>
                    @endif
                </div>
            </div>

            <!-- Informations du tribunal -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informations du tribunal</h3>
                    @if($configuration->tribunalInfo)
                        <dl class="space-y-4">
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Nom du tribunal</dt>
                                <dd class="font-medium">{{ $configuration->tribunalInfo->tribunal_name }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Adresse</dt>
                                <dd class="font-medium">{{ $configuration->tribunalInfo->tribunal_address }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b">
                                <dt class="text-gray-600">Téléphone</dt>
                                <dd class="font-medium">{{ $configuration->tribunalInfo->tribunal_phone }}</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-gray-500">Aucune information de tribunal disponible</p>
                    @endif
                </div>
            </div>

            <!-- Fichiers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Fichiers</h3>
                        @if(!$configuration->is_locked && $configuration->is_paid)
                            <a href="{{ route('dossier.files', $configuration) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                <i class="fas fa-upload mr-2"></i> Gérer les fichiers
                            </a>
                        @endif
                    </div>
                    @if($configuration->files->count() > 0)
                        <div class="space-y-4">
                            @foreach($configuration->files as $file)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <i class="fas fa-file text-gray-400"></i>
                                        <span class="font-medium">{{ $file->original_name }}</span>
                                        <span class="text-sm text-gray-500">{{ $file->getSizeForHumans() }}</span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('dossier.files.preview', ['configuration' => $configuration, 'file' => $file]) }}" 
                                           class="text-blue-600 hover:text-blue-800" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$configuration->is_locked)
                                            <form action="{{ route('dossier.files.destroy', ['configuration' => $configuration, 'file' => $file]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">Aucun fichier n'a été uploadé</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 