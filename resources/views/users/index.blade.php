@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen Pengguna Kantor</h1>
            <p class="page-subtitle">Kelola akses akun Administrator dan Staff sesuai ketentuan kuota PRD</p>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info" style="margin-bottom: 1.5rem;">
        <span>ℹ️</span>
        <div>
            <strong>Ketentuan Kuota Akun (PRD MVP):</strong>
            <ul style="margin-top: 0.25rem; padding-left: 1.25rem;">
                <li><strong>1 Akun Admin</strong> (<code>admin@office.local</code>) &mdash; Akses penuh kelola berkas, hapus dokumen, dan manajemen user.</li>
                <li><strong>2 Akun Staff</strong> (<code>staff1@office.local</code>, <code>staff2@office.local</code>) &mdash; Unggah dan unduh berkas operasional kantor.</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daftar Pengguna Terdaftar ({{ $users->count() }})</h2>
        </div>

        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama &amp; Email</th>
                            <th>Peran / Role</th>
                            <th>Status Akun</th>
                            <th>Dokumen Diunggah</th>
                            <th>Terdaftar Sejak</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #0f172a;">{{ $user->name }}</div>
                                    <div style="font-size: 0.82rem; color: #64748b; font-family: monospace;">{{ $user->email }}</div>
                                </td>
                                <td>
                                    <span class="user-role-badge {{ $user->isAdmin() ? 'role-admin' : 'role-staff' }}" style="font-size: 0.8rem;">
                                        {{ $user->isAdmin() ? '👑 Administrator' : '👤 Staff Biasa' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge badge-green">Aktif</span>
                                    @else
                                        <span class="badge badge-rose">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #334155;">{{ $user->documents_count }}</span> berkas
                                </td>
                                <td style="color: #64748b; font-size: 0.85rem;">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        @if($user->id !== auth()->id())
                                            <!-- Toggle Status -->
                                            <form action="{{ route('users.toggle', $user) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-success' }}">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <!-- Reset Password -->
                                            <form action="{{ route('users.reset-password', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Reset kata sandi pengguna {{ $user->name }} menjadi default \'password\'?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    Reset Sandi
                                                </button>
                                            </form>
                                        @else
                                            <span style="font-size: 0.82rem; color: #94a3b8; font-style: italic;">Akun Anda</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

