<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Création de votre dossier
            </h2>
            <div class="flex space-x-4">
                <span class="text-green-600 font-medium">✓ Fichiers validés</span>
                <span class="text-blue-600 font-medium">→ Informations du cabinet</span>
                <span class="text-gray-400">Options d'impression</span>
                <span class="text-gray-400">Informations tribunal</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('configurations.save-cabinet-info', $configuration) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="cabinet_name">
                                Nom du cabinet
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('cabinet_name') border-red-500 @enderror"
                                   id="cabinet_name"
                                   type="text"
                                   name="cabinet_name"
                                   value="{{ old('cabinet_name') }}"
                                   required>
                            @error('cabinet_name')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                                Adresse
                            </label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror"
                                      id="address"
                                      name="address"
                                      rows="3"
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="postal_code">
                                    Code postal
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('postal_code') border-red-500 @enderror"
                                       id="postal_code"
                                       type="text"
                                       name="postal_code"
                                       value="{{ old('postal_code') }}"
                                       required>
                                @error('postal_code')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
                                    Ville
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('city') border-red-500 @enderror"
                                       id="city"
                                       type="text"
                                       name="city"
                                       value="{{ old('city') }}"
                                       required>
                                @error('city')
                                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                                Téléphone
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror"
                                   id="phone"
                                   type="tel"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   required>
                            @error('phone')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_email">
                                Email de contact
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('contact_email') border-red-500 @enderror"
                                   id="contact_email"
                                   type="email"
                                   name="contact_email"
                                   value="{{ old('contact_email') }}"
                                   required>
                            @error('contact_email')
                                <p class="text-red-500 text-xs italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                    type="submit">
                                Enregistrer et continuer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 