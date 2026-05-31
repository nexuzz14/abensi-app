<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Karyawan') — Oobake Bakery</title>
    <meta name="description" content="Portal absensi karyawan Oobake Bakery berbasis face recognition">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* OOBAKE BAKERY PALETTE — sama dengan admin */
            --espresso:    #2c1503;
            --chestnut:    #7c4a1e;
            --amber:       #b8742a;
            --honey:       #d4922f;
            --caramel:     #e8a84a;
            --cream:       #f5e6d0;
            --warm-white:  #fdf8f2;
            --warm-border: #e8d5bc;
            --text-dark:   #1e0e02;
            --text-mid:    #5c3d1e;
            --text-soft:   #9b7255;
            --success:     #2d7a45;
            --warning:     #b07020;
            --danger:      #b83232;
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }
        body { background: var(--cream); min-height: 100vh; color: var(--text-mid); }

        /* ========================
           NAVBAR KARYAWAN
        ======================== */
        .navbar-custom {
            background: var(--espresso);
            box-shadow: 0 2px 12px rgba(44,21,3,0.25);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .navbar-brand-icon {
            width: 34px; height: 34px;
            background: var(--warm-white);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        .navbar-brand-text {
            font-weight: 800;
            font-size: 1rem;
            color: #fdf8f2 !important;
            letter-spacing: -0.01em;
        }

        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.65) !important;
            font-weight: 500;
            font-size: 0.855rem;
            padding: 0.45rem 0.75rem !important;
            border-radius: 8px;
            transition: all 0.18s;
        }

        .navbar-nav .nav-link:hover {
            color: #fdf8f2 !important;
            background: rgba(255,255,255,0.07);
        }

        .navbar-nav .nav-link.active {
            color: #fdf8f2 !important;
            background: var(--amber);
        }

        .user-pill {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 100px;
            padding: 0.3rem 0.875rem 0.3rem 0.45rem;
            display: flex; align-items: center; gap: 0.5rem;
            color: rgba(255,255,255,0.85);
            font-size: 0.8rem; font-weight: 600;
        }

        .user-avatar-sm {
            width: 28px; height: 28px;
            background: var(--amber);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.72rem; color: white;
        }

        /* ========================
           CONTENT AREA
        ======================== */
        .main-wrapper {
            padding: 1.75rem 0;
            min-height: calc(100vh - 60px);
        }

        /* ========================
           CARDS
        ======================== */
        .card-custom, .content-card {
            background: var(--warm-white);
            border: 1px solid var(--warm-border);
            border-radius: 14px;
            overflow: hidden;
        }

        .card-custom-header, .content-card-header {
            padding: 1rem 1.375rem;
            border-bottom: 1px solid var(--warm-border);
            display: flex; align-items: center; gap: 0.625rem;
        }

        .card-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }

        .card-title-custom, .content-card-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin: 0;
        }

        .content-card-body { padding: 1.25rem; }

        /* ========================
           STATUS BADGES
        ======================== */
        .status-badge {
            font-size: 0.7rem; font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 100px; display: inline-block;
        }

        .badge-hadir     { background: #dcf5e7; color: #1a6334; }
        .badge-terlambat { background: #fef3cd; color: #7a5010; }
        .badge-alpa      { background: #fde8e8; color: #8b2020; }
        .badge-cuti      { background: #e8f0fe; color: #1a3a8f; }
        .badge-libur     { background: #f0ebe4; color: #6b4f30; }
        .badge-pending   { background: #fef3cd; color: #7a5010; }
        .badge-approved  { background: #dcf5e7; color: #1a6334; }
        .badge-rejected  { background: #fde8e8; color: #8b2020; }

        /* ========================
           MINI STAT CARDS
        ======================== */
        .mini-stat {
            background: var(--warm-white);
            border: 1px solid var(--warm-border);
            border-radius: 12px;
            padding: 1rem 1.125rem;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .mini-stat:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(124,74,30,0.1); }

        .mini-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
        .mini-stat-label { font-size: 0.75rem; color: var(--text-soft); font-weight: 500; margin-top: 0.25rem; }

        /* ========================
           FORMS
        ======================== */
        .form-control, .form-select {
            border-color: var(--warm-border);
            border-radius: 10px;
            font-size: 0.875rem;
            padding: 0.6rem 0.875rem;
            background: var(--warm-white);
            color: var(--text-dark);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--amber);
            box-shadow: 0 0 0 3px rgba(184,116,42,0.15);
            background: var(--warm-white);
        }

        .form-label {
            font-weight: 600; font-size: 0.78rem;
            color: var(--text-soft); margin-bottom: 0.375rem;
            text-transform: uppercase; letter-spacing: 0.04em;
        }

        /* ========================
           TABLE
        ======================== */
        .table-custom { font-size: 0.825rem; }

        .table-custom thead th {
            background: #f9f2e8; color: var(--text-soft);
            font-weight: 600; font-size: 0.72rem;
            text-transform: uppercase; letter-spacing: 0.05em;
            border-bottom: 1px solid var(--warm-border);
            padding: 0.75rem 1rem;
        }

        .table-custom tbody td {
            vertical-align: middle; padding: 0.7rem 1rem;
            border-bottom: 1px solid #f0e8da; color: var(--text-mid);
        }

        .table-custom tbody tr:hover { background: #fdf5ea; }

        /* ========================
           ALERTS
        ======================== */
        .alert { border: none; border-radius: 10px; font-size: 0.875rem; }
        .alert-success { background: #e8f5ee; color: #1a5c38; }
        .alert-danger  { background: #fde8e8; color: #7a1f1f; }
        .alert-warning { background: #fef6e0; color: #7a5010; }
        .alert-info    { background: #e8f0fe; color: #1e3a8a; }

        /* ========================
           BUTTONS
        ======================== */
        .btn { border-radius: 8px; font-weight: 600; font-size: 0.875rem; }

        .btn-primary {
            background-color: var(--chestnut) !important;
            border-color: var(--chestnut) !important;
            color: white !important;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--amber) !important;
            border-color: var(--amber) !important;
        }

        .btn-primary-custom {
            background: var(--chestnut); border: none; color: white;
            border-radius: 8px; font-weight: 600; font-size: 0.875rem;
            padding: 0.5rem 1.25rem; transition: all 0.18s ease;
        }
        .btn-primary-custom:hover { background: var(--amber); color: white; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(184,116,42,0.3); }
    </style>

    @stack('styles')
</head>
<body>

<!-- NAVBAR KARYAWAN -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid px-4">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('karyawan.dashboard') }}">
            <div class="navbar-brand-icon">🍞</div>
            <span class="navbar-brand-text">Oobake Bakery</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarKaryawan" style="color:rgba(255,255,255,0.7)">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Nav Links -->
        <div class="collapse navbar-collapse" id="navbarKaryawan">
            <ul class="navbar-nav me-auto ms-lg-3 my-3 my-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}"
                       href="{{ route('karyawan.dashboard') }}">
                        <i class="bi bi-grid me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('karyawan.absensi.*') ? 'active' : '' }}"
                       href="{{ route('karyawan.absensi.index') }}">
                        <i class="bi bi-camera me-1"></i> Absensi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('karyawan.cuti.*') ? 'active' : '' }}"
                       href="{{ route('karyawan.cuti.index') }}">
                        <i class="bi bi-journal-text me-1"></i> Cuti
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('karyawan.profil.*') ? 'active' : '' }}"
                       href="{{ route('karyawan.profil.index') }}">
                        <i class="bi bi-person me-1"></i> Profil
                    </a>
                </li>
            </ul>

            <!-- User Pill + Logout -->
            <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0 mb-3 mb-lg-0">
                <div class="user-pill">
                    <div class="user-avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    {{ Str::limit(auth()->user()->name, 16) }}
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm"
                            style="background:rgba(255,255,255,0.09);color:rgba(255,255,255,0.75);border:1px solid rgba(255,255,255,0.15)"
                            onclick="return confirm('Yakin ingin logout?')">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="main-wrapper">
    <div class="container-fluid px-4">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-3 small"
        style="background:var(--warm-white);color:var(--text-soft);border-top:1px solid var(--warm-border)">
    Oobake Bakery &mdash; Sistem Absensi Karyawan &copy; {{ date('Y') }}
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-dismiss alerts (hanya untuk session alert yang ada tombol btn-close)
    document.querySelectorAll('.alert').forEach(function(alert) {
        if(alert.querySelector('.btn-close')) {
            setTimeout(function() {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 6000);
        }
    });
</script>

@stack('scripts')
</body>
</html>
