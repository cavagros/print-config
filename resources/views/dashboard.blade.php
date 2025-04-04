<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Message de bienvenue -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-2">👋 Bienvenue dans votre espace d'impression !</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Ici, vous pouvez gérer vos configurations d'impression et suivre vos commandes.
                    </p>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Messages</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Notifications</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Tâches</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Projets</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Actions rapides</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('products.configure') }}" class="flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nouveau projet
                        </a>
                        <button class="flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nouvelle tâche
                        </button>
                        <button class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Nouveau message
                        </button>
                    </div>
                </div>
            </div>

            <!-- Configurations sauvegardées -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Mes configurations d'impression</h3>
                    <div class="space-y-4">
                        @foreach($configurations as $config)
                        <div class="border dark:border-gray-700 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-medium">{{ $config->name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Créé le {{ $config->created_at->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold">{{ number_format($config->total_price, 2, ',', ' ') }} €</span>
                                    <div class="mt-1">
                                        @if($config->is_paid)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Payé
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                En attente
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Barre de progression -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex space-x-8">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $config->is_paid ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }} text-white">
                                                1
                                            </div>
                                            <span class="ml-2 text-sm">Paiement</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $config->is_paid && $config->files->count() > 0 ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }} text-white">
                                                2
                                            </div>
                                            <span class="ml-2 text-sm">Fichiers</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $config->is_paid && $config->files->count() > 0 && $config->status === 'validated' ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }} text-white">
                                                3
                                            </div>
                                            <span class="ml-2 text-sm">Validation</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $config->status === 'completed' ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }} text-white">
                                                4
                                            </div>
                                            <span class="ml-2 text-sm">Production</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full">
                                    @php
                                        $progress = 0;
                                        if ($config->is_paid) $progress += 25;
                                        if ($config->files->count() > 0) $progress += 25;
                                        if ($config->status === 'validated') $progress += 25;
                                        if ($config->status === 'completed') $progress += 25;
                                    @endphp
                                    <div class="h-2 bg-green-500 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-4">
                                @if(!$config->is_paid)
                                    <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        Payer
                                    </a>
                                @endif
                                
                                @if($config->is_paid)
                                    <a href="{{ route('dossier.files', $config) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Gérer les fichiers ({{ $config->files->count() }})
                                    </a>
                                @endif

                                @if(!$config->is_paid)
                                    <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Modifier
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Modal de confirmation de suppression -->
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 50;">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mt-4">Confirmation de suppression</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Êtes-vous sûr de vouloir supprimer la configuration "<span id="configName"></span>" ?
                            </p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <form id="deleteForm" method="POST" class="mt-2 space-x-4">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="hideDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Annuler
                                </button>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activité récente -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Activité récente</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Bienvenue sur votre tableau de bord !</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Commencez à utiliser votre espace membre</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">À l'instant</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showDeleteModal = function(name, id) {
                document.getElementById('configName').textContent = name;
                document.getElementById('deleteForm').action = `/products/configurations/${id}`;
                document.getElementById('deleteModal').classList.remove('hidden');
            }

            window.hideDeleteModal = function() {
                document.getElementById('deleteModal').classList.add('hidden');
            }

            // Fermer la modale si on clique en dehors
            window.onclick = function(event) {
                const modal = document.getElementById('deleteModal');
                if (event.target == modal) {
                    hideDeleteModal();
                }
            }

            // Fermer la modale avec la touche Echap
            document.addEventListener('keydown', function(event) {
                if (event.key === "Escape") {
                    hideDeleteModal();
                }
            });
        });
    </script>
</x-app-layout>
