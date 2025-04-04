<!-- Liste des fichiers -->
<div x-show="files.length > 0" class="mt-4 space-y-2">
    <template x-for="file in files" :key="file.id">
        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <!-- Icône selon le type de fichier -->
            <div class="flex-shrink-0">
                <template x-if="file.mime_type.startsWith('image/')">
                    <div class="w-10 h-10 rounded bg-white shadow-sm flex items-center justify-center">
                        <img :src="file.thumbnail_url || file.preview_url" 
                             :alt="file.original_name"
                             class="max-w-full max-h-full object-contain rounded"
                             @error="$el.src = '/images/placeholder.png'"
                        >
                    </div>
                </template>
                <template x-if="file.mime_type === 'application/pdf'">
                    <div class="w-10 h-10 rounded bg-red-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Informations du fichier -->
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate" x-text="file.original_name"></p>
                <p class="text-xs text-gray-500" x-text="file.size_human"></p>
            </div>

            <!-- Actions -->
            <div class="flex-shrink-0 space-x-2">
                <button
                    type="button"
                    @click="$event.preventDefault(); deleteFile(file.id)"
                    class="text-red-600 hover:text-red-800 p-1"
                    title="Supprimer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div> 