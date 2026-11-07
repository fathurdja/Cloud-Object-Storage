@extends('layouts.app')

@section('title', 'Unggah Dokumen Baru')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Header -->
        <div class="page-header">
            <div>
                <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: 0.75rem;">
                    ← Kembali ke Arsip
                </a>
                <h1 class="page-title">Unggah Dokumen Kantor</h1>
                <p class="page-subtitle">File akan diunggah dan disimpan ke RustFS S3-compatible Object Storage.</p>
            </div>
        </div>

        <!-- Storage Info Notice -->
        <div class="storage-banner" style="margin-bottom: 1.5rem;">
            <div class="storage-status">
                <span class="pulse-dot {{ $storageHealth['healthy'] ? 'pulse-green' : 'pulse-amber' }}"></span>
                <div>
                    <strong>Penyimpanan Tujuan:</strong>
                    <span style="color: #475569;">{{ $storageHealth['message'] }}</span>
                </div>
            </div>
            <span class="storage-tag">Bucket: {{ config('filesystems.disks.rustfs.bucket', 'office-documents') }}</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Formulir Metadata &amp; Berkas</h2>
            </div>

            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <!-- Judul Dokumen -->
                    <div class="form-group">
                        <label for="title" class="form-label">Judul Dokumen <span style="color: var(--danger);">*</span></label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            class="form-control" 
                            placeholder="Contoh: Surat Perjanjian Kerjasama Vendor 2026" 
                            value="{{ old('title') }}" 
                            required
                        >
                        <div class="form-hint">Berikan judul ringkas yang mudah dicari oleh staf dan pimpinan.</div>
                        @error('title')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori Dokumen -->
                    <div class="form-group">
                        <label for="category" class="form-label">Kategori Dokumen <span style="color: var(--danger);">*</span></label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">-- Pilih Kategori Berkas --</option>
                            <option value="surat_masuk" {{ old('category') === 'surat_masuk' ? 'selected' : '' }}>Surat Masuk (Korespondensi Masuk)</option>
                            <option value="surat_keluar" {{ old('category') === 'surat_keluar' ? 'selected' : '' }}>Surat Keluar (Korespondensi Keluar)</option>
                            <option value="nota_dinas" {{ old('category') === 'nota_dinas' ? 'selected' : '' }}>Nota Dinas (Memo Internal Kantor)</option>
                            <option value="laporan" {{ old('category') === 'laporan' ? 'selected' : '' }}>Laporan (Laporan Kerja / Progres)</option>
                            <option value="invoice" {{ old('category') === 'invoice' ? 'selected' : '' }}>Invoice / Keuangan (Bukti Transaksi)</option>
                            <option value="lainnya" {{ old('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        <div class="form-hint">Folder penyimpanan RustFS: <code>uploads/{kategori}/</code></div>
                        @error('category')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Dropzone -->
                    <div class="form-group">
                        <label class="form-label">Berkas Dokumen <span style="color: var(--danger);">*</span></label>
                        
                        <div class="file-dropzone" id="dropzoneBox">
                            <input 
                                type="file" 
                                name="file" 
                                id="fileInput" 
                                class="file-input-hidden" 
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png"
                                required
                            >
                            <div class="file-dropzone-icon">📄</div>
                            <div style="font-weight: 600; font-size: 1rem; color: #1e293b;" id="dropzonePrompt">
                                Tarik berkas ke sini atau klik untuk memilih file
                            </div>
                            <div style="font-size: 0.82rem; color: #64748b; margin-top: 0.35rem;" id="dropzoneInfo">
                                Format: PDF, DOCX, XLSX, XLS, CSV, JPG, PNG &bull; Maksimum 10 MB
                            </div>

                            <!-- Selected File Badge (hidden by default) -->
                            <div id="fileSelectedBox" style="display: none; margin-top: 1rem; padding: 0.75rem 1rem; background: white; border: 1px solid var(--border-color); border-radius: var(--radius-md); text-align: left;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.25rem;">📎</span>
                                        <div>
                                            <div id="selectedFileName" style="font-weight: 600; font-size: 0.9rem; color: #0f172a;"></div>
                                            <div id="selectedFileSize" style="font-size: 0.78rem; color: #64748b;"></div>
                                        </div>
                                    </div>
                                    <span class="badge badge-green">Siap Diunggah</span>
                                </div>
                            </div>
                        </div>

                        @error('file')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi / Catatan -->
                    <div class="form-group">
                        <label for="description" class="form-label">Deskripsi / Catatan Tambahan (Opsional)</label>
                        <textarea 
                            name="description" 
                            id="description" 
                            rows="3" 
                            class="form-control" 
                            placeholder="Catatan perihal ringkas atau nomor surat terkait..."
                        >{{ old('description') }}</textarea>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            🚀 Mulai Unggah ke Storage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const fileInput = document.getElementById('fileInput');
        const dropzoneBox = document.getElementById('dropzoneBox');
        const fileSelectedBox = document.getElementById('fileSelectedBox');
        const selectedFileName = document.getElementById('selectedFileName');
        const selectedFileSize = document.getElementById('selectedFileSize');

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                selectedFileName.textContent = file.name;
                selectedFileSize.textContent = formatBytes(file.size) + ' (' + file.type + ')';
                fileSelectedBox.style.display = 'block';
                dropzoneBox.style.borderColor = 'var(--primary)';
            }
        });

        // Drag and drop visual cues
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, () => dropzoneBox.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, () => dropzoneBox.classList.remove('dragover'), false);
        });

        document.getElementById('uploadForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '⏳ Mengunggah ke RustFS...';
        });
    </script>
@endsection

