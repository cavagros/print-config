function fileUpload(configId) {
    return {
        files: [],
        error: null,
        uploading: false,
        progress: 0,
        currentFileName: '',
        init() {
            this.loadFiles();
        },
        async loadFiles() {
            try {
                const response = await fetch(`/api/configurations/${configId}/files/list`);
                const data = await response.json();
                this.files = data.files.map(file => ({
                    ...file,
                    url: '/storage/' + file.path
                }));
            } catch (error) {
                this.error = 'Erreur de chargement des fichiers';
            }
        },
        async uploadFiles(event) {
            const files = event.target.files;
            if (!files.length) return;
            this.uploading = true;
            this.error = null;
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) {
                    this.error = `Le fichier ${file.name} est trop volumineux (max 10MB)`;
                    continue;
                }
                this.currentFileName = file.name;
                this.progress = 0;
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await fetch(`/api/configurations/${configId}/files`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                    const data = await response.json();
                    this.files.push({
                        ...data.file,
                        url: '/storage/' + data.file.path
                    });
                    this.progress = 100;
                } catch (error) {
                    this.error = `Erreur lors de l'upload de ${file.name}`;
                }
            }
            this.uploading = false;
            this.currentFileName = '';
            this.progress = 0;
            event.target.value = '';
        },
        async deleteFile(fileId) {
            if (!confirm('Supprimer ce fichier ?')) return;
            try {
                await fetch(`/api/configurations/${configId}/files/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });
                this.files = this.files.filter(f => f.id !== fileId);
            } catch (error) {
                this.error = 'Erreur lors de la suppression';
            }
        }
    }
} 