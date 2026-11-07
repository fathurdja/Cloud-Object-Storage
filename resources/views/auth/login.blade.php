<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Office Admin Web Portal</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto 0.75rem; width: 48px; height: 48px; font-size: 1.5rem;">
                    📁
                </div>
                <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a;">Office Admin Portal</h2>
                <p style="font-size: 0.88rem; color: #64748b; margin-top: 0.25rem;">
                    Penyimpanan Dokumen Kantor &bull; Integrasi RustFS S3
                </p>
            </div>

            <div class="auth-body">
                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom: 1.25rem; font-size: 0.85rem;">
                        <span>✅</span>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 1.25rem; font-size: 0.85rem;">
                        <span>⚠️</span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control" 
                            value="{{ old('email', 'admin@office.local') }}" 
                            placeholder="nama@office.local" 
                            required 
                            autofocus
                        >
                        @error('email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control" 
                            value="password"
                            placeholder="••••••••" 
                            required
                        >
                        @error('password')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #475569; cursor: pointer;">
                            <input type="checkbox" name="remember" value="1" checked>
                            Ingat sesi saya
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600;">
                        Masuk ke Portal ➔
                    </button>
                </form>

                <!-- Demo Credentials Quick-Fill -->
                <div class="demo-account-box">
                    <h4>Akun Bawaan (Klik untuk Isi Cepat)</h4>
                    <div class="demo-account-list">
                        <div class="demo-item" onclick="quickFill('admin@office.local', 'password')">
                            <div>
                                <strong>admin@office.local</strong>
                                <div style="font-size: 0.75rem; color: #64748b;">Role: Administrator (Akses Penuh)</div>
                            </div>
                            <span class="badge badge-rose" style="font-size: 0.7rem;">👑 Admin</span>
                        </div>

                        <div class="demo-item" onclick="quickFill('staff1@office.local', 'password')">
                            <div>
                                <strong>staff1@office.local</strong>
                                <div style="font-size: 0.75rem; color: #64748b;">Role: Staff Operasional</div>
                            </div>
                            <span class="badge badge-blue" style="font-size: 0.7rem;">👤 Staff 1</span>
                        </div>

                        <div class="demo-item" onclick="quickFill('staff2@office.local', 'password')">
                            <div>
                                <strong>staff2@office.local</strong>
                                <div style="font-size: 0.75rem; color: #64748b;">Role: Staff Keuangan & Umum</div>
                            </div>
                            <span class="badge badge-blue" style="font-size: 0.7rem;">👤 Staff 2</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quickFill(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>

