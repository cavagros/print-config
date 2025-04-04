<x-app-layout>
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Informations du Tribunal - {{ $configuration->name }}
            </h2>
            <a href="{{ route('configurations.print-options', $configuration) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                Retour aux options d'impression
            </a>
        </div>
    </x-slot:header>

    <!-- Barre de progression -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-center" aria-label="Progress">
                <ol role="list" class="flex items-center space-x-5 md:space-x-8">
                    <!-- Étapes 1-3 complétées -->
                    @foreach(['Upload fichiers', 'Informations cabinet', 'Options d\'impression'] as $index => $step)
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

                    <!-- Étape 4 : Livraison (active) -->
                    <li>
                        <div class="flex items-center">
                            <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                            </span>
                            <span class="ml-2 text-sm font-medium text-indigo-600">Livraison</span>
                        </div>
                    </li>

                    <!-- Étape 5 : Paiement -->
                    <li>
                        <div class="flex items-center">
                            <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                <span class="text-sm text-gray-500">5</span>
                            </span>
                            <span class="ml-2 text-sm font-medium text-gray-500">Paiement</span>
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
                    <h2 class="text-2xl font-semibold mb-6">Informations du Tribunal</h2>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('configurations.save-tribunal-info', $configuration) }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="tribunal_name" class="block text-sm font-medium text-gray-700">Nom du Tribunal</label>
                            <input type="text" name="tribunal_name" id="tribunal_name" 
                                value="{{ old('tribunal_name', $tribunalInfo->tribunal_name ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div>
                            <label for="chamber" class="block text-sm font-medium text-gray-700">Chambre (optionnel)</label>
                            <input type="text" name="chamber" id="chamber" 
                                value="{{ old('chamber', $tribunalInfo->chamber ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
                            <input type="text" name="address" id="address" 
                                value="{{ old('address', $tribunalInfo->address ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">Code Postal</label>
                                <input type="text" name="postal_code" id="postal_code" 
                                    value="{{ old('postal_code', $tribunalInfo->postal_code ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">Ville</label>
                                <input type="text" name="city" id="city" 
                                    value="{{ old('city', $tribunalInfo->city ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone contact (optionnel)</label>
                            <input type="tel" name="phone" id="phone" 
                                value="{{ old('phone', $tribunalInfo->phone ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Contact (optionnel)</label>
                            <input type="email" name="contact_email" id="contact_email" 
                                value="{{ old('contact_email', $tribunalInfo->contact_email ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Continuer vers le récapitulatif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 