<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestion des fichiers - {{ $configuration->name }}
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Gestion des fichiers</h1>
                    @if(!$isValidated)
                        <form action="{{ route('dossier.validate_files', $configuration) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                                Valider les fichiers
                            </button>
                        </form>
                    @endif
                </div>

                @if($isValidated)
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                        <p>Vos fichiers ont été validés et ne peuvent plus être modifiés.</p>
                    </div>
                @endif

                <div class="space-y-4 mb-8">
                    @forelse($files as $file)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="text-gray-600">
                                    <p class="font-medium">{{ $file['original_name'] }}</p>
                                    <p class="text-sm">{{ $file['size_human'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ $file['preview_url'] }}" target="_blank" class="text-blue-500 hover:text-blue-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @if(!$isValidated)
                                    <form action="{{ route('dossier.files.destroy', ['configuration' => $configuration, 'file' => $file['id']]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">Aucun fichier n'a été uploadé.</p>
                    @endforelse
                </div>

                @if(!$isValidated)
                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-4">Uploader des fichiers</h2>
                        <form 
                            action="{{ route('dossier.files.store', $configuration) }}" 
                            method="POST" 
                            enctype="multipart/form-data"
                            class="relative"
                        >
                            @csrf
                            <div 
                                class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors"
                            >
                                <div class="space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-gray-600">
                                        Glissez-déposez vos fichiers ici<br>
                                        ou cliquez pour sélectionner
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Formats acceptés : PDF, DOC, DOCX, JPG, JPEG, PNG<br>
                                        Taille maximale : 10MB par fichier
                                    </p>
                                </div>
                                <input 
                                    type="file" 
                                    name="files[]"
                                    multiple 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                    onchange="this.form.submit()"
                                >
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            @if($isValidated)
                <div class="mt-8 text-center">
                    <a href="{{ route('dossier.cabinet', $configuration) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg text-lg">
                        Passer à l'étape suivante
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 