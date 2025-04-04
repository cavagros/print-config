<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Informations du cabinet
            </h2>
            <a href="{{ route('dossier.files', $configuration) }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Retour aux fichiers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8">
                    <!-- En-tête du formulaire -->
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Saisie des informations du cabinet</h3>
                        <p class="text-gray-600">Veuillez remplir les informations ci-dessous pour continuer.</p>
                    </div>

                    @if(session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p class="font-bold">Erreur</p>
                            <p>{{ session('error') }}</p>
                        </div>
                    @endif

                    <form action="{{ route('dossier.cabinet.store', $configuration) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Informations principales -->
                        <div class="bg-gray-50 rounded-lg p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="cabinet_name">
                                    <i class="fas fa-building mr-2"></i>Nom du cabinet
                                </label>
                                <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('cabinet_name') border-red-500 @enderror"
                                       id="cabinet_name"
                                       type="text"
                                       name="cabinet_name"
                                       value="{{ old('cabinet_name', $cabinetInfo->cabinet_name ?? '') }}"
                                       required>
                                @error('cabinet_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="address">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Adresse
                                </label>

                                <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('address') border-red-500 @enderror"
                                       id="address"
                                       type="text"
                                       name="address"
                                       value="{{ old('address', $cabinetInfo->address ?? '') }}"
                                       required>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="postal_code">
                                        <i class="fas fa-mail-bulk mr-2"></i>Code postal
                                    </label>
                                    <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('postal_code') border-red-500 @enderror"
                                           id="postal_code"
                                           type="text"
                                           name="postal_code"
                                           value="{{ old('postal_code', $cabinetInfo->postal_code ?? '') }}"
                                           required>
                                    @error('postal_code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="city">
                                        <i class="fas fa-city mr-2"></i>Ville
                                    </label>
                                    <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('city') border-red-500 @enderror"
                                           id="city"
                                           type="text"
                                           name="city"
                                           value="{{ old('city', $cabinetInfo->city ?? '') }}"
                                           required>
                                    @error('city')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Informations de contact -->
                        <div class="bg-gray-50 rounded-lg p-6 space-y-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Informations de contact</h4>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="phone">
                                    <i class="fas fa-phone mr-2"></i>Téléphone
                                </label>
                                <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('phone') border-red-500 @enderror"
                                       id="phone"
                                       type="tel"
                                       name="phone"
                                       value="{{ old('phone', $cabinetInfo->phone ?? '') }}"
                                       required>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2" for="contact_email">
                                    <i class="fas fa-envelope mr-2"></i>Email de contact
                                </label>
                                <input class="form-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contact_email') border-red-500 @enderror"
                                       id="contact_email"
                                       type="email"
                                       name="contact_email"
                                       value="{{ old('contact_email', $cabinetInfo->contact_email ?? '') }}"
                                       required>
                                @error('contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-save mr-2"></i>
                                Enregistrer et continuer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @endpush
</x-app-layout> 