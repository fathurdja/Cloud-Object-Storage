<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Office Admin Web Portal') - Laravel & RustFS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header class="navbar">
        <div class="navbar-container">
            <a href="{{ route('documents.index') }}" class="brand">
                <div class="brand-icon">📁</div>
                <span>Office Portal</span>
                <span class="brand-badge">RustFS S3</span>
            </a>

            @auth
                <nav class="nav-links">
                    <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                        📑 Daftar Dokumen
                    </a>
                    <a href="{{ route('documents.create') }}" class="nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}">
                        📤 Unggah Dokumen
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            👥 Manajemen User
                        </a>
                    @endif
                </nav>

                <div class="user-profile-menu">
                    <div class="user-tag">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <div>
                            <span class="user-role-badge {{ auth()->user()->isAdmin() ? 'role-admin' : 'role-staff' }}">
                                {{ auth()->user()->isAdmin() ? '👑 Administrator' : '👤 Staff' }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Keluar dari sesi">
                            Keluar ➔
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="main-wrapper">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div>
                    <strong>Terdapat beberapa kesalahan:</strong>
                    <ul style="margin-top: 0.25rem; padding-left: 1.25rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>Office Admin Web Portal &bull; Laravel 11 &amp; RustFS Object Storage &bull; Mode: Native CSS (No Node.js)</p>
    </footer>

    @yield('scripts')
</body>
</html>

