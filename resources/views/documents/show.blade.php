@extends('layouts.app')

@section('title', $document->title)

@section('content')
    <div style="max-width: 860px; margin: 0 auto;">
        <!-- Header -->
        <div class="page-header">
            <div>
                <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm" style="margin-bottom: 0.75rem;">
                    ← Kembali ke Arsip
                </a>
                <h1 class="page-title">{{ $document->title }}</h1>
                <p class="page-subtitle">Detail berkas dan metadata penyimpanan objek</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                    ⬇ Unduh Berkas
                </a>
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini secara permanen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            🗑 Hapus Dokumen
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Document Card -->
        <div class="card">
            <div class="card-header" style="background-color: #fafbfc;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="file-icon file-icon-{{ $document->extension }}" style="width: 52px; height: 52px; font-size: 1rem;">
                        {{ strtoupper($document->extension) }}
                    </div>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">{{ $document->title }}</h2>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
                            <span class="badge {{ $document->category_badge_class }}">
                                {{ $document->category_label }}
                            </span>
                            <span style="font-size: 0.85rem; color: #64748b;">
                                📦 {{ $document->formatted_size }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <h3 style="font-size: 1rem; font-weight: 600; color: #334155; margin-bottom: 1rem;">
                    Metadata Berkas &amp; Informasi Penyimpanan
                </h3>

                <table class="table" style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    <tbody>
                        <tr>
                            <td style="width: 200px; font-weight: 600; background-color: #f8fafc; color: #475569;">Nama File Asli</td>
                            <td style="font-family: monospace;">{{ $document->file_name }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Kategori Dokumen</td>
                            <td>
                                <span class="badge {{ $document->category_badge_class }}">
                                    {{ $document->category_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Ukuran Berkas</td>
                            <td>{{ $document->formatted_size }} ({{ number_format($document->file_size) }} bytes)</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Tipe MIME</td>
                            <td style="font-family: monospace;">{{ $document->mime_type }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Path Objek Storage</td>
                            <td>
                                <code style="background-color: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #0f172a;">
                                    {{ $document->file_path }}
                                </code>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Target Bucket S3</td>
                            <td>
                                <span class="storage-tag">{{ config('filesystems.disks.rustfs.bucket', 'office-documents') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Pengunggah</td>
                            <td>
                                <strong>{{ $document->user->name ?? 'User Sistem' }}</strong> 
                                ({{ $document->user->email ?? '-' }}) 
                                &bull; <span class="user-role-badge {{ $document->user && $document->user->isAdmin() ? 'role-admin' : 'role-staff' }}">
                                    {{ $document->user && $document->user->isAdmin() ? '👑 Admin' : '👤 Staff' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Waktu Unggah</td>
                            <td>{{ $document->created_at->format('d F Y, H:i:s') }} ({{ $document->created_at->diffForHumans() }})</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; background-color: #f8fafc; color: #475569;">Deskripsi / Catatan</td>
                            <td>
                                @if($document->description)
                                    <div style="white-space: pre-line; color: #334155;">{{ $document->description }}</div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Tidak ada catatan tambahan untuk dokumen ini.</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if(!auth()->user()->isAdmin())
                    <div class="alert alert-info" style="margin-top: 1.5rem; margin-bottom: 0;">
                        <span>ℹ️</span>
                        <div>
                            <strong>Hak Akses User Biasa (Staff):</strong> Anda memiliki izin untuk melihat dan mengunduh berkas ini. Penghapusan berkas hanya dapat dilakukan oleh akun berwewenang Administrator.
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer" style="justify-content: flex-end; gap: 0.75rem;">
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
                <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                    ⬇ Unduh File Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection

