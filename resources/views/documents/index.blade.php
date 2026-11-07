@extends('layouts.app')

@section('title', 'Daftar Dokumen Kantor')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Arsip Dokumen Kantor</h1>
            <p class="page-subtitle">Pusat berkas administrasi tersimpan di RustFS Object Storage (S3 API)</p>
        </div>
        <div>
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                <span>➕</span> Unggah Dokumen Baru
            </a>
        </div>
    </div>

    <!-- Storage Status Banner -->
    <div class="storage-banner">
        <div class="storage-status">
            <span class="pulse-dot {{ $storageHealth['healthy'] ? 'pulse-green' : 'pulse-amber' }}"></span>
            <strong>Status Storage:</strong>
            <span>{{ $storageHealth['message'] }}</span>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span class="storage-tag">Disk: {{ $storageHealth['disk'] }}</span>
            <span class="storage-tag">Bucket: {{ config('filesystems.disks.rustfs.bucket', 'office-documents') }}</span>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Arsip</span>
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-sub">Semua berkas kantor</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Surat Masuk</span>
            <span class="stat-value" style="color: #2563eb;">{{ $stats['surat_masuk'] }}</span>
            <span class="stat-sub">Dokumen korespondensi masuk</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Surat Keluar</span>
            <span class="stat-value" style="color: #059669;">{{ $stats['surat_keluar'] }}</span>
            <span class="stat-sub">Dokumen korespondensi keluar</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Nota Dinas & Lainnya</span>
            <span class="stat-value" style="color: #7c3aed;">{{ $stats['nota_dinas'] + $stats['lainnya'] }}</span>
            <span class="stat-sub">Berkas internal & operasional</span>
        </div>
    </div>

    <!-- Main Documents Card -->
    <div class="card">
        <div class="card-header">
            <form action="{{ route('documents.index') }}" method="GET" class="filter-toolbar">
                <div class="search-input-group">
                    <span class="search-icon">🔍</span>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Cari berdasarkan judul atau nama file..." 
                        class="search-input"
                    >
                </div>

                <div style="min-width: 170px;">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="surat_masuk" {{ request('category') === 'surat_masuk' ? 'selected' : '' }}>Surat Masuk</option>
                        <option value="surat_keluar" {{ request('category') === 'surat_keluar' ? 'selected' : '' }}>Surat Keluar</option>
                        <option value="nota_dinas" {{ request('category') === 'nota_dinas' ? 'selected' : '' }}>Nota Dinas</option>
                        <option value="laporan" {{ request('category') === 'laporan' ? 'selected' : '' }}>Laporan</option>
                        <option value="invoice" {{ request('category') === 'invoice' ? 'selected' : '' }}>Invoice / Keuangan</option>
                        <option value="lainnya" {{ request('category') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                @if(request()->filled('q') || request()->filled('category'))
                    <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </form>
        </div>

        <div class="card-body" style="padding: 0;">
            @if($documents->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3 style="font-size: 1.15rem; font-weight: 600; margin-bottom: 0.5rem;">Belum ada dokumen yang ditemukan</h3>
                    <p style="font-size: 0.9rem; margin-bottom: 1.25rem;">
                        {{ request()->filled('q') || request()->filled('category') ? 'Coba ubah kata kunci atau hapus filter pencarian.' : 'Mulai dengan mengunggah dokumen administrasi kantor pertama Anda.' }}
                    </p>
                    <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                        Unggah Dokumen Sekarang
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Berkas &amp; Judul</th>
                                <th>Kategori</th>
                                <th>Ukuran</th>
                                <th>Diupload Oleh</th>
                                <th>Tanggal</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.85rem;">
                                            <div class="file-icon file-icon-{{ $doc->extension }}">
                                                {{ strtoupper($doc->extension) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('documents.show', $doc) }}" style="font-weight: 600; color: #1e293b; text-decoration: none;">
                                                    {{ $doc->title }}
                                                </a>
                                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem;">
                                                    📄 {{ $doc->file_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $doc->category_badge_class }}">
                                            {{ $doc->category_label }}
                                        </span>
                                    </td>
                                    <td style="color: #475569; font-size: 0.88rem; font-family: monospace;">
                                        {{ $doc->formatted_size }}
                                    </td>
                                    <td>
                                        <div style="font-size: 0.88rem; font-weight: 500;">
                                            {{ $doc->user->name ?? 'Sistem' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            {{ $doc->user && $doc->user->isAdmin() ? '👑 Admin' : '👤 Staff' }}
                                        </div>
                                    </td>
                                    <td style="color: #64748b; font-size: 0.85rem;">
                                        {{ $doc->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="justify-content: flex-end;">
                                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-outline btn-sm" title="Lihat Informasi Dokumen">
                                                Detail
                                            </a>
                                            <a href="{{ route('documents.download', $doc) }}" class="btn btn-success btn-sm" title="Unduh Berkas dari Storage">
                                                ⬇ Unduh
                                            </a>

                                            @if(auth()->user()->isAdmin())
                                                <form action="{{ route('documents.destroy', $doc) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen \'{{ $doc->title }}\'? Tindakan ini tidak dapat dibatalkan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Dokumen (Khusus Admin)">
                                                        🗑 Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($documents->hasPages())
            <div class="card-footer">
                <div style="font-size: 0.85rem; color: #64748b;">
                    Menampilkan {{ $documents->firstItem() }} - {{ $documents->lastItem() }} dari {{ $documents->total() }} dokumen
                </div>
                <div class="pagination-wrapper">
                    {{ $documents->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

