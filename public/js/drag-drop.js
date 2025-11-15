// Sistema de Drag & Drop para subida de archivos
class DragDrop {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) {
            console.error('Container not found:', containerSelector);
            return;
        }

        this.options = {
            maxFiles: options.maxFiles || 5,
            maxSize: options.maxSize || 10 * 1024 * 1024, // 10MB
            acceptedTypes: options.acceptedTypes || ['image/*', 'application/pdf', '.doc', '.docx'],
            onFilesAdded: options.onFilesAdded || (() => {}),
            onFileRemoved: options.onFileRemoved || (() => {}),
            onError: options.onError || ((msg) => alert(msg))
        };

        this.files = [];
        this.init();
    }

    init() {
        this.createDropzone();
        this.attachEventListeners();
    }

    createDropzone() {
        this.container.innerHTML = `
            <div class="drag-drop-area border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center transition hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-gray-700">
                <div class="drag-drop-content">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-4">
                        <label for="file-upload" class="cursor-pointer">
                            <span class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">Selecciona archivos</span>
                            <span class="text-gray-600 dark:text-gray-400"> o arrástralos aquí</span>
                            <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple accept="${this.options.acceptedTypes.join(',')}">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        ${this.getAcceptedTypesText()} hasta ${this.formatBytes(this.options.maxSize)} (máx ${this.options.maxFiles} archivos)
                    </p>
                </div>
                
                <!-- Vista previa de archivos -->
                <div class="file-preview-container mt-6 space-y-2 hidden"></div>
            </div>
        `;

        this.dropzone = this.container.querySelector('.drag-drop-area');
        this.fileInput = this.container.querySelector('#file-upload');
        this.previewContainer = this.container.querySelector('.file-preview-container');
    }

    attachEventListeners() {
        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropzone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropzone.addEventListener(eventName, () => this.highlight(), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.dropzone.addEventListener(eventName, () => this.unhighlight(), false);
        });

        this.dropzone.addEventListener('drop', (e) => this.handleDrop(e), false);

        // File input change
        this.fileInput.addEventListener('change', (e) => this.handleFiles(e.target.files));
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight() {
        this.dropzone.classList.add('border-indigo-500', 'bg-indigo-50', 'dark:bg-gray-700');
    }

    unhighlight() {
        this.dropzone.classList.remove('border-indigo-500', 'bg-indigo-50', 'dark:bg-gray-700');
    }

    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    }

    handleFiles(files) {
        const fileArray = Array.from(files);

        // Validar número de archivos
        if (this.files.length + fileArray.length > this.options.maxFiles) {
            this.options.onError(`Solo puedes subir un máximo de ${this.options.maxFiles} archivos`);
            return;
        }

        // Validar cada archivo
        const validFiles = [];
        for (const file of fileArray) {
            if (!this.validateFile(file)) continue;
            validFiles.push(file);
        }

        if (validFiles.length > 0) {
            this.files = [...this.files, ...validFiles];
            this.renderPreviews();
            this.options.onFilesAdded(validFiles);
        }
    }

    validateFile(file) {
        // Validar tamaño
        if (file.size > this.options.maxSize) {
            this.options.onError(`El archivo "${file.name}" excede el tamaño máximo de ${this.formatBytes(this.options.maxSize)}`);
            return false;
        }

        // Validar tipo
        const acceptedTypes = this.options.acceptedTypes;
        const fileType = file.type;
        const fileName = file.name.toLowerCase();

        const isAccepted = acceptedTypes.some(type => {
            if (type.startsWith('.')) {
                return fileName.endsWith(type);
            }
            if (type.endsWith('/*')) {
                return fileType.startsWith(type.split('/')[0]);
            }
            return fileType === type;
        });

        if (!isAccepted) {
            this.options.onError(`El tipo de archivo "${file.name}" no es válido`);
            return false;
        }

        return true;
    }

    renderPreviews() {
        if (this.files.length === 0) {
            this.previewContainer.classList.add('hidden');
            return;
        }

        this.previewContainer.classList.remove('hidden');
        this.previewContainer.innerHTML = this.files.map((file, index) => `
            <div class="flex items-center justify-between bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    ${this.getFileIcon(file)}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">${file.name}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${this.formatBytes(file.size)}</p>
                    </div>
                </div>
                <button onclick="window.dragDropInstance.removeFile(${index})" class="ml-3 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `).join('');
    }

    getFileIcon(file) {
        const type = file.type;
        
        if (type.startsWith('image/')) {
            return `
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            `;
        } else if (type === 'application/pdf') {
            return `
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900 rounded flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            `;
        } else {
            return `
                <div class="flex-shrink-0 w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            `;
        }
    }

    removeFile(index) {
        const removedFile = this.files[index];
        this.files.splice(index, 1);
        this.renderPreviews();
        this.options.onFileRemoved(removedFile, index);
    }

    getFiles() {
        return this.files;
    }

    clearFiles() {
        this.files = [];
        this.renderPreviews();
        this.fileInput.value = '';
    }

    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    getAcceptedTypesText() {
        const types = this.options.acceptedTypes;
        if (types.includes('image/*') && types.includes('application/pdf')) {
            return 'Imágenes y PDF';
        } else if (types.includes('image/*')) {
            return 'Imágenes';
        } else if (types.includes('application/pdf')) {
            return 'PDF';
        }
        return 'Archivos';
    }
}

// Uso: window.dragDropInstance = new DragDrop('#dropzone-container', { ... });
