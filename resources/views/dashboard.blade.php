<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!auth()->user()->is_admin)
                <div class="mb-6">
                    <a href="{{ route('products.configure') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle configuration
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Mes configurations</h3>
                        @if(isset($configurations) && $configurations->count() > 0)
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($configurations as $configuration)
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium text-lg">{{ $configuration->name }}</h4>
                                                <p class="text-sm text-gray-500">Créé le : {{ $configuration->created_at->format('d/m/Y') }}</p>
                                                <p class="text-sm">Statut : 
                                                    <span class="{{ $configuration->is_paid ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $configuration->is_paid ? 'Payé' : 'Non payé' }}
                                                    </span>
                                                </p>
                                                @if($configuration->is_subscription)
                                                    <p class="text-sm">Type : Abonnement</p>
                                                    <p class="text-sm">Statut abonnement : {{ $configuration->subscription_status ?? 'Non défini' }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-medium">{{ number_format($configuration->total_price, 2, ',', ' ') }} €</p>
                                                <a href="{{ route('dossier.files', $configuration) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Voir les fichiers</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">Aucune configuration trouvée.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Vue administrateur</h3>
                        @if(isset($totalConfigurations))
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">Total des configurations</p>
                                    <p class="text-2xl font-medium">{{ $totalConfigurations }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">Configurations payées</p>
                                    <p class="text-2xl font-medium text-green-600">{{ $paidConfigurations->count() }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">Configurations non payées</p>
                                    <p class="text-2xl font-medium text-red-600">{{ $unpaidConfigurations->count() }}</p>
                                </div>
                            </div>

                            <div class="mt-8">
                                <h4 class="text-lg font-medium mb-4">Dernières configurations</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($paidConfigurations->take(5) as $config)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                                    <span class="text-indigo-600 font-medium">{{ substr($config->user->name, 0, 1) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-900">{{ $config->user->name }}</div>
                                                                <div class="text-sm text-gray-500">{{ $config->user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $config->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($config->total_price, 2, ',', ' ') }} €</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Payé
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $config->updated_at->format('d/m/Y H:i') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
