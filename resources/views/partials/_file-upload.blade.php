<div x-data="fileUpload({{ $configuration->id }})" class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Fichiers du projet</h3>

    @if(!$configuration->is_paid)
        <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 p-4 rounded-lg mb-4">
            Le devis doit être payé avant de pouvoir uploader des fichiers.
        </div>
    @else
        <div class="space-y-4">
            <!-- Zone de drop -->
            <div
                @drop.prevent="handleDrop($event)"
                @dragover.prevent="dragover = true"
                @dragleave.prevent="dragover = false"
                :class="{'bg-blue-50 dark:bg-blue-900': dragover}"
                class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center transition-colors duration-200"
            >
                <input
                    type="file"
                    class="hidden"
                    x-ref="fileInput"
                    @change="handleFileSelect"
                    accept=".png,.jpg,.jpeg,.pdf"
                    multiple
                >
                
                <div class="space-y-2">
                    <button
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                    >
                        Sélectionner des fichiers
                    </button>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        ou déposez vos fichiers ici
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        PNG, JPG, PDF (max. 10 MB)
                    </p>
                </div>
            </div>

            <!-- Barre de progression -->
            <div x-show="uploading" class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full" :class="{'text-green-600 bg-green-200': uploadProgress === 100, 'text-blue-600 bg-blue-200': uploadProgress < 100}">
                            Upload en cours
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block" :class="{'text-green-600': uploadProgress === 100, 'text-blue-600': uploadProgress < 100}">
                            <span x-text="uploadProgress"></span>%
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                    <div
                        :style="'width: ' + uploadProgress + '%'"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center transition-all duration-500"
                        :class="{'bg-green-500': uploadProgress === 100, 'bg-blue-500': uploadProgress < 100}"
                    ></div>
                </div>
            </div>

            <!-- Liste des fichiers -->
            <div x-show="files.length > 0" class="space-y-2">
                <template x-for="file in files" :key="file.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium" x-text="file.original_name"></p>
                                <p class="text-xs text-gray-500" x-text="file.size_human"></p>
                            </div>
                        </div>
                        <button
                            @click="deleteFile(file.id)"
                            class="text-red-600 hover:text-red-800 transition-colors duration-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function fileUpload(configurationId) {
    return {
        dragover: false,
        uploading: false,
        uploadProgress: 0,
        files: [],
        
        async init() {
            // Charger les fichiers existants
            try {
                const response = await fetch(`/configurations/${configurationId}/files`);
                const data = await response.json();
                this.files = data.files;
            } catch (error) {
                console.error('Erreur lors du chargement des fichiers:', error);
            }
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
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    alert(`Le fichier ${file.name} est trop volumineux. La taille maximum est de 10MB.`);
                    continue;
                }

                if (!['image/png', 'image/jpeg', 'application/pdf'].includes(file.type)) {
                    alert(`Le type de fichier ${file.type} n'est pas autorisé. Utilisez PNG, JPG ou PDF.`);
                    continue;
                }

                this.uploading = true;
                this.uploadProgress = 0;

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch(`/configurations/${configurationId}/files`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        onUploadProgress: (progressEvent) => {
                            this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    this.files.push(data.file);
                } catch (error) {
                    console.error('Erreur lors de l\'upload:', error);
                    alert('Une erreur est survenue lors de l\'upload du fichier.');
                } finally {
                    this.uploading = false;
                }
            }
        },

        async deleteFile(fileId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
                return;
            }

            try {
                const response = await fetch(`/configurations/${configurationId}/files/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                this.files = this.files.filter(file => file.id !== fileId);
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                alert('Une erreur est survenue lors de la suppression du fichier.');
            }
        }
    }
}</script>
@endpush 