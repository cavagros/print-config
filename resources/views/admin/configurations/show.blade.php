<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dossier {{ $configuration->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
                @if($configuration->is_paid)
                    <form action="{{ route('admin.configurations.refund', $configuration) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                onclick="return confirm('Êtes-vous sûr de vouloir rembourser ce dossier ?')">
                            <i class="fas fa-undo mr-2"></i>Rembourser
                        </button>
                    </form>
                @endif
                @if($configuration->status === 'file_sent')
                    <form action="{{ route('admin.configurations.reset', $configuration) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                                onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser ce dossier ?')">
                            <i class="fas fa-sync mr-2"></i>Réinitialiser
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Informations générales -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informations générales</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Client</p>
                            <p class="font-medium">{{ $configuration->user->name }}</p>
                            <p class="text-sm text-gray-600">{{ $configuration->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Statut</p>
                            <p class="font-medium">
                                @if($configuration->status === 'validated')
                                    <span class="text-green-600">Validé</span>
                                @else
                                    <span class="text-yellow-600">En attente</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-600">
                                @if($configuration->is_paid)
                                    <span class="text-green-600">Payé</span>
                                @else
                                    <span class="text-red-600">Non payé</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Prix total TTC</p>
                            <p class="font-medium text-blue-600">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date de création</p>
                            <p class="font-medium">{{ $configuration->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cabinet et Tribunal -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <!-- Cabinet -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Cabinet</h3>
                        @if($configuration->cabinetInfo)
                            <div class="space-y-2">
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
                        @else
                            <p class="text-gray-600">Aucune information de cabinet n'a été renseignée.</p>
                        @endif
                    </div>
                </div>

                <!-- Tribunal -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tribunal</h3>
                        @if($configuration->tribunalInfo)
                            <div class="space-y-2">
                                <p class="font-medium">{{ $configuration->tribunalInfo->tribunal_name }}</p>
                                <p class="text-gray-600"><strong>Chambre :</strong> {{ $configuration->tribunalInfo->chamber }}</p>
                                <p class="text-gray-600">
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
                        @else
                            <p class="text-gray-600">Aucune information de tribunal n'a été renseignée.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Fichiers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Fichiers ({{ $configuration->files->count() }})</h3>
                        <p class="text-sm text-gray-600">{{ $configuration->pages ?? 0 }} pages au total</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($configuration->files as $file)
                            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-file-pdf text-red-500 mr-3 text-xl"></i>
                                    <div>
                                        <p class="font-medium">{{ $file->original_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $file->size_human }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('dossier.files.preview', ['configuration' => $configuration, 'file' => $file]) }}" 
                                   target="_blank"
                                   class="text-blue-500 hover:text-blue-700">
                                    <i class="fas fa-eye text-xl"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 