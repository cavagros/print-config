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

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($configuration->status === 'validated')
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium">Dossier validé</p>
                            <p class="text-sm">Ce dossier a été validé et ne peut plus être modifié.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-4">
                    <div class="text-gray-900">
                        <h3 class="font-medium mb-2">Instructions pour l'upload de fichiers</h3>
                        <ul class="list-disc list-inside text-sm text-gray-600">
                            <li>Formats acceptés : PDF, DOC, DOCX, JPG, JPEG, PNG</li>
                            <li>Taille maximale par fichier : 10 MB</li>
                            <li>Glissez-déposez vos fichiers ou utilisez le bouton ci-dessous</li>
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <div x-data="fileUpload({{ $configuration->id }})" 
                         x-init="init()"
                         class="space-y-4">
                        
                        @if($configuration->status !== 'validated')
                            <!-- Zone de dépôt -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6"
                                 x-on:drop.prevent="handleDrop($event)"
                                 x-on:dragover.prevent="dragover = true"
                                 x-on:dragleave.prevent="dragover = false">
                                
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Glissez vos fichiers ici ou
                                        <label class="text-blue-500 hover:text-blue-600 cursor-pointer">
                                            <span>parcourez</span>
                                            <input type="file" class="hidden" multiple
                                                   x-on:change="handleFileSelect($event)">
                                        </label>
                                    </p>
                                </div>
                            </div>

                            <!-- Barre de progression -->
                            <div x-show="uploading" class="relative pt-1">
                                <div class="flex mb-2 items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                            Upload en cours
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold inline-block text-blue-600">
                                            <span x-text="uploadProgress"></span>%
                                        </span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                                    <div x-bind:style="'width: ' + uploadProgress + '%'"
                                         class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-300">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Liste des fichiers -->
                        <div class="space-y-2">
                            <template x-for="file in files" :key="file.id">
                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                        <span x-text="file.original_name" class="text-sm"></span>
                                        <span class="text-xs text-gray-500" x-text="file.size_human"></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a :href="file.preview_url" target="_blank" 
                                           class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($configuration->status !== 'validated')
                                            <button @click="deleteFile(file.id)" 
                                                    class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Message d'erreur -->
                        <div x-show="error" class="text-red-500 text-sm" x-text="error"></div>

                        @if($configuration->status !== 'validated' && $files->count() > 0)
                            <!-- Bouton de validation -->
                            <div class="mt-6 flex justify-end">
                                <form action="{{ route('dossier.validate_files', $configuration) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Valider les fichiers
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function fileUpload(configurationId) {
            return {
                files: @json($files),
                error: null,
                uploading: false,
                uploadProgress: 0,
                dragover: false,

                init() {
                    // Déjà initialisé avec les fichiers passés depuis le contrôleur
                },

                handleDrop(event) {
                    this.dragover = false;
                    const files = event.dataTransfer.files;
                    this.uploadFiles(files);
                },

                handleFileSelect(event) {
                    const files = event.target.files;
                    this.uploadFiles(files);
                },

                async uploadFiles(files) {
                    this.error = null;
                    this.uploading = true;
                    this.uploadProgress = 0;

                    for (const file of files) {
                        if (file.size > 10 * 1024 * 1024) {
                            this.error = `Le fichier ${file.name} est trop volumineux (max 10MB)`;
                            continue;
                        }

                        const formData = new FormData();
                        formData.append('file', file);

                        try {
                            const xhr = new XMLHttpRequest();
                            
                            xhr.upload.addEventListener('progress', (e) => {
                                if (e.lengthComputable) {
                                    this.uploadProgress = Math.round((e.loaded * 100) / e.total);
                                }
                            });

                            const uploadPromise = new Promise((resolve, reject) => {
                                xhr.onload = () => {
                                    if (xhr.status === 200) {
                                        resolve(JSON.parse(xhr.response));
                                    } else {
                                        reject(new Error(`Erreur HTTP: ${xhr.status}`));
                                    }
                                };
                                xhr.onerror = () => reject(new Error('Erreur réseau'));
                            });

                            xhr.open('POST', `/dossier/${configurationId}/send_file`);
                            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                            xhr.send(formData);

                            const response = await uploadPromise;
                            this.files.push(response.file);
                        } catch (error) {
                            this.error = `Erreur lors de l'upload de ${file.name}`;
                            console.error('Erreur:', error);
                        }
                    }

                    this.uploading = false;
                    this.uploadProgress = 0;
                },

                async deleteFile(fileId) {
                    if (!confirm('Voulez-vous vraiment supprimer ce fichier ?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`/dossier/${configurationId}/delete_file/${fileId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Erreur lors de la suppression');
                        }

                        this.files = this.files.filter(f => f.id !== fileId);
                    } catch (error) {
                        this.error = 'Erreur lors de la suppression du fichier';
                        console.error('Erreur:', error);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout> 