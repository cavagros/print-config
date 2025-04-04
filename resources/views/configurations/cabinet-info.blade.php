<x-app-layout>
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Informations du Cabinet - {{ $configuration->name }}
            </h2>
            <a href="{{ route('configurations.files', $configuration) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                Retour aux fichiers
            </a>
        </div>
    </x-slot:header>

    <!-- Barre de progression -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-center" aria-label="Progress">
                <ol role="list" class="flex items-center space-x-5 md:space-x-8">
                    <!-- Étape 1 : Upload fichiers -->
                    <li>
                        <div class="flex items-center">
                            @if($configuration->step > 1)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                    <span class="text-sm text-gray-500">1</span>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium {{ $configuration->step > 1 ? 'text-green-600' : 'text-gray-500' }}">Upload fichiers</span>
                        </div>
                    </li>

                    <!-- Étape 2 : Informations cabinet -->
                    <li>
                        <div class="flex items-center">
                            @if($configuration->step > 2)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @elseif($configuration->step == 2)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                                </span>
                            @else
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                    <span class="text-sm text-gray-500">2</span>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium {{ $configuration->step == 2 ? 'text-indigo-600' : ($configuration->step > 2 ? 'text-green-600' : 'text-gray-500') }}">Informations cabinet</span>
                        </div>
                    </li>

                    <!-- Étape 3 : Options d'impression -->
                    <li>
                        <div class="flex items-center">
                            @if($configuration->step > 3)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @elseif($configuration->step == 3)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                                </span>
                            @else
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                    <span class="text-sm text-gray-500">3</span>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium {{ $configuration->step == 3 ? 'text-indigo-600' : ($configuration->step > 3 ? 'text-green-600' : 'text-gray-500') }}">Options d'impression</span>
                        </div>
                    </li>

                    <!-- Étape 4 : Livraison -->
                    <li>
                        <div class="flex items-center">
                            @if($configuration->step > 4)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @elseif($configuration->step == 4)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                                </span>
                            @else
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                    <span class="text-sm text-gray-500">4</span>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium {{ $configuration->step == 4 ? 'text-indigo-600' : ($configuration->step > 4 ? 'text-green-600' : 'text-gray-500') }}">Livraison</span>
                        </div>
                    </li>

                    <!-- Étape 5 : Paiement -->
                    <li>
                        <div class="flex items-center">
                            @if($configuration->step > 5)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @elseif($configuration->step == 5)
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white">
                                    <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                                </span>
                            @else
                                <span class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                    <span class="text-sm text-gray-500">5</span>
                                </span>
                            @endif
                            <span class="ml-2 text-sm font-medium {{ $configuration->step == 5 ? 'text-indigo-600' : ($configuration->step > 5 ? 'text-green-600' : 'text-gray-500') }}">Paiement</span>
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
                    <h2 class="text-2xl font-semibold mb-6">Informations du Cabinet</h2>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('configurations.save-cabinet-info', $configuration) }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="cabinet_name" class="block text-sm font-medium text-gray-700">Nom du Cabinet</label>
                            <input type="text" name="cabinet_name" id="cabinet_name" 
                                value="{{ old('cabinet_name', $cabinetInfo->cabinet_name ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
                            <input type="text" name="address" id="address" 
                                value="{{ old('address', $cabinetInfo->address ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700">Code Postal</label>
                                <input type="text" name="postal_code" id="postal_code" 
                                    value="{{ old('postal_code', $cabinetInfo->postal_code ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">Ville</label>
                                <input type="text" name="city" id="city" 
                                    value="{{ old('city', $cabinetInfo->city ?? '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="tel" name="phone" id="phone" 
                                value="{{ old('phone', $cabinetInfo->phone ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email de contact dossier</label>
                            <input type="email" name="contact_email" id="contact_email" 
                                value="{{ old('contact_email', $cabinetInfo->contact_email ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $configuration->status === 'info_completed' ? 'disabled' : '' }}>
                        </div>

                        <div class="flex justify-end space-x-4">
                            @if($configuration->status !== 'info_completed')
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Enregistrer et continuer
                                </button>
                            @else
                                <a href="{{ route('configurations.print-options', $configuration) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Continuer vers les options d'impression
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 