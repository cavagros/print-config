<script>
function fileUpload(configurationId) {
    return {
        files: [],
        error: null,
        uploading: false,
        uploadProgress: 0,
        dragover: false,

        async init() {
            try {
                const response = await fetch(`/api/configurations/${configurationId}/files/list`);
                if (!response.ok) throw new Error('Erreur lors du chargement des fichiers');
                const data = await response.json();
                this.files = data.files;
            } catch (error) {
                this.error = 'Erreur lors du chargement des fichiers';
                console.error(error);
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
            this.error = null;
            this.uploading = true;
            this.uploadProgress = 0;

            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) {
                    this.error = `Le fichier ${file.name} est trop volumineux (max 10MB)`;
                    continue;
                }

                if (!['image/png', 'image/jpeg', 'application/pdf'].includes(file.type)) {
                    this.error = `Le type de fichier ${file.type} n'est pas autorisé. Utilisez PNG, JPG ou PDF.`;
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

                    xhr.open('POST', `/api/configurations/${configurationId}/files`);
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.send(formData);

                    const data = await uploadPromise;
                    this.files.push(data.file);
                } catch (error) {
                    this.error = 'Erreur lors de l\'upload';
                    console.error(error);
                }
            }

            setTimeout(() => {
                this.uploading = false;
                this.uploadProgress = 0;
            }, 500);
        },

        async deleteFile(fileId) {
            if (!confirm('Supprimer ce fichier ?')) return;

            try {
                const response = await fetch(`/api/configurations/${configurationId}/files/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
                this.files = this.files.filter(file => file.id !== fileId);
            } catch (error) {
                this.error = 'Erreur lors de la suppression';
                console.error(error);
            }
        }
    }
}</script> 