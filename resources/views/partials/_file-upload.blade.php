@props(['configId', 'files', 'isValidated'])

@push('scripts')
<!-- Sortable.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function fileUpload(configId, initialFiles, initialValidated) {
    return {
        files: initialFiles,
        error: null,
        uploading: false,
        progress: 0,
        currentFile: '',
        sortable: null,
        isValidated: initialValidated,
        showConfirmation: false,
        isUploading: false,
        uploadProgress: 0,
        success: null,

        init() {
            if (!this.isValidated) {
                this.initSortable();
            }
        },

        initSortable() {
            if (this.isValidated) return;
            
            const el = document.getElementById('fileList');
            if (!el) return;
            
            this.sortable = new Sortable(el, {
                animation: 150,
                handle: '.handle',
                onSort: (evt) => {
                    if (this.isValidated) return;

                    const items = el.querySelectorAll('[data-file-id]');
                    this.files = Array.from(items).map((item, index) => {
                        const id = parseInt(item.getAttribute('data-file-id'));
                        const file = this.files.find(f => f.id === id);
                        return { ...file, order: index + 1 };
                    });

                    fetch(`/dossier/${configId}/files/order`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            files: this.files.map(f => ({ id: f.id, order: f.order }))
                        })
                    }).catch(() => this.error = "Erreur lors de la mise à jour de l'ordre");
                }
            });
        },

        handleDrop(e) {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (file) {
                this.uploadFile(file);
            }
            e.target.classList.remove('border-blue-500');
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        },

        async uploadFile(file) {
            if (this.isValidated) {
                this.error = "Les fichiers ont été validés. Vous ne pouvez plus faire de modifications.";
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch(`/dossier/${configId}/send_file`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.files.push(data.file);
                    this.files.sort((a, b) => a.order - b.order);
                    this.uploadProgress = 0;
                    this.error = null;
                } else {
                    this.error = data.error || 'Une erreur est survenue lors de l\'upload du fichier.';
                }
            } catch (error) {
                console.error('Erreur lors de l\'upload:', error);
                this.error = 'Une erreur est survenue lors de l\'upload du fichier.';
            } finally {
                this.uploadProgress = 0;
            }
        },

        async deleteFile(fileId) {
            if (this.isValidated) {
                this.error = "Les fichiers ont été validés. Vous ne pouvez plus faire de modifications.";
                return;
            }

            if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
                return;
            }

            try {
                const response = await fetch(`/dossier/${configId}/delete_file/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.files = this.files.filter(f => f.id !== fileId);
                    this.error = null;
                } else {
                    this.error = data.error || 'Une erreur est survenue lors de la suppression du fichier.';
                }
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                this.error = 'Une erreur est survenue lors de la suppression du fichier.';
            }
        },

        async validateFiles() {
            if (this.files.length === 0) {
                this.error = 'Vous devez ajouter au moins un fichier avant de valider.';
                return;
            }

            try {
                const response = await fetch(`/dossier/${configId}/validate_files`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.success = data.message;
                    this.error = null;
                    this.isValidated = true;
                    
                    // Redirection après un court délai
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    this.error = data.error;
                    this.success = null;
                }
            } catch (error) {
                console.error('Erreur lors de la validation:', error);
                this.error = 'Une erreur est survenue lors de la validation des fichiers.';
                this.success = null;
            }
        }
    }
}
</script>
@endpush

<div class="mt-4">
    @if(!isset($configuration))
        <div class="p-4 bg-red-100 text-red-700 rounded-lg">Configuration non trouvée.</div>
    @elseif(!$configuration->is_paid)
        <div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg">Le devis doit être payé avant de pouvoir uploader des fichiers.</div>
    @else
        <div x-data="fileUpload({{ $configuration->id }}, {{ Js::from($files) }}, {{ Js::from($isValidated) }})" class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Fichiers à imprimer</h2>
                <template x-if="!isValidated">
                    <button @click="validateFiles" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Valider les fichiers
                    </button>
                </template>
            </div>

            <template x-if="error">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <p x-text="error"></p>
                </div>
            </template>

            <template x-if="success">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <p x-text="success"></p>
                </div>
            </template>

            <template x-if="isValidated">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <p>Vos fichiers ont été validés avec succès. Vous ne pouvez plus les modifier.</p>
                </div>
            </template>

            <template x-if="!isValidated">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center" 
                     @dragover.prevent="$el.classList.add('border-blue-500')"
                     @dragleave.prevent="$el.classList.remove('border-blue-500')"
                     @drop.prevent="handleDrop($event)">
                    <input type="file" class="hidden" @change="handleFileSelect($event)" x-ref="fileInput">
                    <button @click="$refs.fileInput.click()" class="text-blue-600 hover:text-blue-800">
                        Cliquez pour sélectionner
                    </button>
                    <span class="text-gray-500">ou glissez vos fichiers ici</span>
                </div>
            </template>

            <div id="fileList" x-show="files.length > 0" class="space-y-2">
                <template x-for="file in files" :key="file.id">
                    <div :data-file-id="file.id" class="flex items-center justify-between p-3 bg-white rounded shadow">
                        <div class="flex items-center space-x-3">
                            <template x-if="!isValidated">
                                <div class="handle cursor-move">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                    </svg>
                                </div>
                            </template>
                            <span x-text="file.original_name" class="font-medium"></span>
                            <span x-text="file.size_human" class="text-sm text-gray-500"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a :href="file.preview_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <template x-if="!isValidated">
                                <button @click="deleteFile(file.id)" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="uploadProgress > 0" class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                            Upload en cours
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-blue-600" x-text="`${uploadProgress}%`"></span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                    <div :style="`width: ${uploadProgress}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-300"></div>
                </div>
            </div>
        </div>
    @endif
</div> 