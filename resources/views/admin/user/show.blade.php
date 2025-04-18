<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Détails de l'utilisateur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Informations de l'utilisateur</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-medium">Nom :</p>
                            <p>{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="font-medium">Email :</p>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="font-medium">Date d'inscription :</p>
                            <p>{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="font-medium">Nombre de dossiers :</p>
                            <p>{{ $configurations->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Dossiers de l'utilisateur</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du dossier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paiement</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($configurations as $config)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('admin.configurations.show', $config) }}" class="text-blue-600 hover:text-blue-900">
                                                {{ $config->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $config->status === 'file_sent' ? 'bg-green-100 text-green-800' : 
                                                   ($config->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ $config->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $config->is_paid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $config->is_paid ? 'Payé' : 'Non payé' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $config->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.configurations.show', $config) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                                Voir
                                            </a>
                                            <a href="{{ route('dossier.summary', $config) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                Éditer
                                            </a>
                                            @if($config->is_paid)
                                                <form action="{{ route('admin.configurations.refund', $config) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                                            onclick="return confirm('Êtes-vous sûr de vouloir rembourser ce dossier ?')">
                                                        Rembourser
                                                    </button>
                                                </form>
                                            @endif
                                            @if($config->status === 'file_sent')
                                                <form action="{{ route('admin.configurations.reset', $config) }}" method="POST" class="inline ml-3">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser ce dossier ?')">
                                                        Réinitialiser
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 