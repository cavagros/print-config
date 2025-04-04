<!-- Zone d'upload -->
<div
    class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors"
    :class="{ 'border-blue-500 bg-blue-50': dragover }"
    @dragover.prevent="dragover = true"
    @dragleave.prevent="dragover = false"
    @drop.prevent="handleDrop($event)"
>
    <input
        type="file"
        class="hidden"
        x-ref="fileInput"
        @change="handleFileSelect"
        accept=".png,.jpg,.jpeg,.pdf"
        multiple
    >
    
    <div class="space-y-4">
        <div class="text-gray-500">
            <svg class="mx-auto h-12 w-12" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m0 0v4a4 4 0 004 4h24a4 4 0 004-4v-4m-4-16l-8-8m0 0v16m0-16l-8 8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <button
            type="button"
            @click="$refs.fileInput.click()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            :disabled="uploading"
        >
            Sélectionner des fichiers
        </button>
        
        <p class="text-sm text-gray-600">ou déposez vos fichiers ici</p>
        <p class="text-xs text-gray-500">PNG, JPG, PDF (max. 10 MB)</p>
    </div>
</div>

<!-- Barre de progression -->
<div x-show="uploading" class="mt-4">
    <div class="flex justify-between mb-1">
        <span class="text-sm font-medium text-blue-700">Upload en cours...</span>
        <span class="text-sm font-medium text-blue-700" x-text="uploadProgress + '%'"></span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
             :style="'width: ' + uploadProgress + '%'"
             :class="{'bg-green-600': uploadProgress === 100}"></div>
    </div>
</div> 