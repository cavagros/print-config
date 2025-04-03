<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Détails de la configuration') }} : {{ $configuration->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium mb-4">Informations générales</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom</dt>
                                    <dd class="mt-1">{{ $configuration->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Utilisateur</dt>
                                    <dd class="mt-1">{{ $configuration->user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de création</dt>
                                    <dd class="mt-1">{{ $configuration->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prix total</dt>
                                    <dd class="mt-1 text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                        {{ number_format($configuration->total_price, 2) }} €
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium mb-4">Spécifications techniques</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre de pages</dt>
                                    <dd class="mt-1">{{ $configuration->pages }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type d'impression</dt>
                                    <dd class="mt-1">{{ $configuration->print_type === 'noir_blanc' ? 'Noir et blanc' : 'Couleur' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type de papier</dt>
                                    <dd class="mt-1">
                                        @switch($configuration->paper_type)
                                            @case('standard')
                                                Standard
                                                @break
                                            @case('recycle')
                                                Recyclé
                                                @break
                                            @case('premium')
                                                Premium
                                                @break
                                            @case('photo')
                                                Photo
                                                @break
                                        @endswitch
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Format</dt>
                                    <dd class="mt-1">{{ $configuration->format }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type de reliure</dt>
                                    <dd class="mt-1">
                                        @switch($configuration->binding_type)
                                            @case('sans_reliure')
                                                Sans reliure
                                                @break
                                            @case('agrafage')
                                                Agrafage
                                                @break
                                            @case('spirale')
                                                Spirale
                                                @break
                                            @case('dos_colle')
                                                Dos collé
                                                @break
                                        @endswitch
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type de livraison</dt>
                                    <dd class="mt-1">
                                        @switch($configuration->delivery_type)
                                            @case('retrait_magasin')
                                                Retrait en magasin
                                                @break
                                            @case('livraison_standard')
                                                Livraison standard
                                                @break
                                            @case('livraison_express')
                                                Livraison express
                                                @break
                                        @endswitch
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('admin.configurations.index') }}" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Retour à la liste
                        </a>
                        <form action="{{ route('admin.configurations.destroy', $configuration) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette configuration ?')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 