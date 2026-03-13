class StorageSDK {
    constructor(config = {}) {
        this.baseUrl = config.baseUrl || '/api/storage';
        this.chunkSize = config.chunkSize || 2 * 1024 * 1024; // 2MB
        this.defaultDisk = config.disk || 'default';
    }

    async upload(file, options = {}) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('disk', options.disk || this.defaultDisk);
        if (options.optimize !== undefined) {
            formData.append('optimize', options.optimize ? '1' : '0');
        }

        const response = await fetch(`${this.baseUrl}/upload`, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Upload failed: ${response.statusText}`);
        }

        return await response.json();
    }

    async uploadChunked(file, options = {}) {
        const totalChunks = Math.ceil(file.size / this.chunkSize);
        const sessionId = this._generateUUID();
        const disk = options.disk || this.defaultDisk;

        // Upload chunks sequentially (or parallel if enhanced)
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * this.chunkSize;
            const end = Math.min(start + this.chunkSize, file.size);
            const chunk = file.slice(start, end);

            const formData = new FormData();
            formData.append('session_id', sessionId);
            formData.append('chunk_index', chunkIndex);
            formData.append('total_chunks', totalChunks);
            formData.append('filename', file.name);
            formData.append('file', chunk);
            // formData.append('hash', ...); // Optional: calculate hash

            const response = await fetch(`${this.baseUrl}/upload/chunk`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Chunk upload failed at index ${chunkIndex}`);
            }
            
            if (options.onProgress) {
                options.onProgress((chunkIndex + 1) / totalChunks);
            }
        }

        // Complete
        const formData = new FormData();
        formData.append('session_id', sessionId);
        formData.append('disk', disk);
        if (options.optimize !== undefined) {
            formData.append('optimize', options.optimize ? '1' : '0');
        }

        const response = await fetch(`${this.baseUrl}/upload/complete`, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Upload completion failed: ${response.statusText}`);
        }

        return await response.json();
    }

    async getFile(id) {
        const response = await fetch(`${this.baseUrl}/file/${id}`);
        if (!response.ok) {
            throw new Error(`Get file failed: ${response.statusText}`);
        }
        return await response.json();
    }

    async deleteFile(id) {
        const response = await fetch(`${this.baseUrl}/file/${id}`, {
            method: 'DELETE'
        });
        if (!response.ok) {
            throw new Error(`Delete file failed: ${response.statusText}`);
        }
        return await response.json();
    }

    _generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
}

// Export for module systems or global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StorageSDK;
} else {
    window.StorageSDK = StorageSDK;
}
