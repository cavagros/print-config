<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gestion des fichiers - {{ $configuration->name }}
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                Retour au tableau de bord
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($isValidated)
                        <div class="mb-6 flex justify-end">
                            <a href="{{ route('dossier.cabinet', $configuration) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Saisir les informations du cabinet →
                            </a>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-2">Instructions</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Vous pouvez uploader vos fichiers ici. Une fois que tous vos fichiers sont uploadés, cliquez sur le bouton "Valider les fichiers" pour continuer.
                            <br>Formats acceptés : PDF, DOC, DOCX, JPG, JPEG, PNG (max. 10 MB)
                        </p>
                    </div>

                    @if($configuration->is_paid)
                        @include('partials._file-upload', ['configId' => $configuration->id])
                    @else
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                            <p>Vous devez d'abord payer le devis avant de pouvoir uploader des fichiers.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 