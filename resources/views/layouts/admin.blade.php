<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi') — Admin Panel</title>
    <meta name="description" content="Panel administrasi sistem absensi karyawan berbasis face recognition">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* OOBAKE BAKERY PALETTE */
            --espresso:      #2c1503;
            --chestnut:      #7c4a1e;
            --amber:         #b8742a;
            --honey:         #d4922f;
            --caramel:       #e8a84a;
            --cream:         #f5e6d0;
            --warm-white:    #fdf8f2;
            --warm-border:   #e8d5bc;
            --text-dark:     #1e0e02;
            --text-mid:      #5c3d1e;
            --text-soft:     #9b7255;
            --text-cream:    #d4b896;
            --success:  #2d7a45;
            --warning:  #b07020;
            --danger:   #b83232;
            --sidebar-width: 264px;
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

        body { background: var(--cream); min-height: 100vh; color: var(--text-mid); }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0;
            height: 100vh; width: var(--sidebar-width);
            background: var(--espresso);
            overflow-y: auto; z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            display: flex; flex-direction: column;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.08) transparent;
        }

        .sidebar-brand {
            padding: 1.375rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; gap: 0.875rem;
        }

        .sidebar-brand-icon {
            width: 42px; height: 42px;
            background: var(--warm-white); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; flex-shrink: 0; overflow: hidden;
        }

        .sidebar-brand-text { color: #fdf8f2; font-weight: 700; font-size: 0.9rem; line-height: 1.2; }
        .sidebar-brand-sub  { color: var(--text-cream); font-size: 0.67rem; opacity: 0.7; margin-top: 1px; }

        .sidebar-nav { padding: 0.875rem 0; flex: 1; }

        .sidebar-section {
            padding: 0.625rem 1.25rem 0.25rem;
            font-size: 0.63rem; font-weight: 700;
            color: var(--text-cream); text-transform: uppercase;
            letter-spacing: 0.12em; margin-top: 0.375rem; opacity: 0.55;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1rem; color: #d4c4b0;
            text-decoration: none; font-size: 0.845rem; font-weight: 500;
            border-radius: 10px; transition: all 0.18s ease;
            margin: 0.1rem 0.625rem; position: relative;
        }

        .sidebar-link:hover { background: rgba(255,255,255,0.06); color: #fdf8f2; }
        .sidebar-link.active {
            background: var(--amber); color: #fff;
            box-shadow: 0 4px 14px rgba(184,116,42,0.35);
        }

        .sidebar-link i {
            font-size: 0.95rem; width: 18px; text-align: center;
            flex-shrink: 0; color: #a08060; transition: color 0.18s;
        }
        .sidebar-link:hover i { color: #d4b896; }
        .sidebar-link.active i { color: #fff; }

        .sidebar-badge {
            margin-left: auto; background: #b83232; color: white;
            font-size: 0.63rem; font-weight: 700;
            padding: 0.13rem 0.45rem; border-radius: 100px; line-height: 1.4;
        }

        .sidebar-footer { padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.07); }
        .sidebar-user { display: flex; align-items: center; gap: 0.75rem; }

        .sidebar-user-avatar {
            width: 36px; height: 36px; background: var(--amber);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: white; flex-shrink: 0;
        }

        .sidebar-user-name { color: #fdf8f2; font-size: 0.8rem; font-weight: 600; }
        .sidebar-user-role { color: var(--text-cream); font-size: 0.67rem; opacity: 0.7; }

        /* MAIN CONTENT */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }

        .topbar {
            background: var(--warm-white);
            border-bottom: 1px solid var(--warm-border);
            padding: 0.875rem 1.5rem;
            display: flex; align-items: center;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 0 rgba(124,74,30,0.06);
        }

        .topbar-title { font-size: 1rem; font-weight: 700; color: var(--text-dark); flex: 1; }
        .topbar-actions { display: flex; align-items: center; gap: 0.75rem; }
        .page-content { padding: 1.5rem; flex: 1; }

        /* STAT & MINI CARDS */
        .stat-card, .mini-stat {
            background: var(--warm-white); border-radius: 14px;
            padding: 1.25rem; border: 1px solid var(--warm-border);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover, .mini-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,74,30,0.1);
        }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
        .stat-value { font-size: 1.875rem; font-weight: 800; color: var(--text-dark); line-height: 1; font-feature-settings: 'tnum'; }
        .stat-label { font-size: 0.78rem; color: var(--text-soft); font-weight: 500; margin-top: 0.25rem; }
        .mini-stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1; }
        .mini-stat-label { font-size: 0.78rem; color: var(--text-soft); margin-top: 0.25rem; }

        /* CONTENT CARD */
        .content-card, .card-custom { background: var(--warm-white); border-radius: 14px; border: 1px solid var(--warm-border); overflow: hidden; }
        .content-card-header, .card-custom-header {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--warm-border);
            display: flex; align-items: center; justify-content: space-between;
            background: var(--warm-white);
        }
        .content-card-title, .card-title-custom { font-size: 0.9rem; font-weight: 700; color: var(--text-dark); margin: 0; }
        .content-card-body { padding: 1.25rem; }
        .card-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-right: 0.625rem; }

        /* TABLE */
        .table-custom { font-size: 0.845rem; }
        .table-custom thead th {
            background: #f9f2e8; color: var(--text-soft);
            font-weight: 600; font-size: 0.72rem; text-transform: uppercase;
            letter-spacing: 0.06em; border-bottom: 1px solid var(--warm-border);
            white-space: nowrap; padding: 0.75rem 1rem;
        }
        .table-custom tbody td { vertical-align: middle; padding: 0.75rem 1rem; border-bottom: 1px solid #f0e8da; color: var(--text-mid); }
        .table-custom tbody tr:hover { background: #fdf5ea; }

        /* STATUS BADGES */
        .status-badge { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 100px; display: inline-block; text-transform: capitalize; }
        .badge-hadir     { background: #dcf5e7; color: #1a6334; }
        .badge-terlambat { background: #fef3cd; color: #7a5010; }
        .badge-alpa      { background: #fde8e8; color: #8b2020; }
        .badge-cuti      { background: #e8f0fe; color: #1a3a8f; }
        .badge-libur     { background: #f0ebe4; color: #6b4f30; }
        .badge-pending   { background: #fef3cd; color: #7a5010; }
        .badge-approved  { background: #dcf5e7; color: #1a6334; }
        .badge-rejected  { background: #fde8e8; color: #8b2020; }

        /* FORMS */
        .form-control, .form-select {
            border-color: var(--warm-border); border-radius: 8px; font-size: 0.875rem;
            background: var(--warm-white); color: var(--text-dark);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--amber); box-shadow: 0 0 0 3px rgba(184,116,42,0.15);
            background: var(--warm-white);
        }
        .form-label { font-weight: 600; font-size: 0.78rem; color: var(--text-soft); margin-bottom: 0.375rem; text-transform: uppercase; letter-spacing: 0.04em; }

        /* BUTTONS */
        .btn-primary-custom {
            background: var(--chestnut); border: none; color: white;
            border-radius: 8px; font-weight: 600; font-size: 0.875rem;
            padding: 0.5rem 1.25rem; transition: all 0.18s ease;
        }
        .btn-primary-custom:hover { background: var(--amber); color: white; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(184,116,42,0.35); }
        .btn-primary { background-color: var(--chestnut) !important; border-color: var(--chestnut) !important; }
        .btn-primary:hover, .btn-primary:focus { background-color: var(--amber) !important; border-color: var(--amber) !important; }

        /* ALERTS */
        .alert { border: none; border-radius: 10px; font-size: 0.875rem; }
        .alert-success { background: #e8f5ee; color: #1a5c38; }
        .alert-danger  { background: #fde8e8; color: #7a1f1f; }
        .alert-warning { background: #fef6e0; color: #7a5010; }
        .alert-info    { background: #e8f0fe; color: #1e3a8a; }

        /* MOBILE */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(44,21,3,0.55); z-index: 999; }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    </style>

    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">🍞</div>
        <div>
            <div class="sidebar-brand-text">Oobake Bakery</div>
            <div class="sidebar-brand-sub">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Utama</div>
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>

        <div class="sidebar-section">Karyawan</div>
        <a href="{{ route('admin.karyawan.index') }}" class="sidebar-link {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Data Karyawan
        </a>

        <div class="sidebar-section">Manajemen Waktu</div>
        <a href="{{ route('admin.shift.index') }}" class="sidebar-link {{ request()->routeIs('admin.shift.*') ? 'active' : '' }}">
            <i class="bi bi-clock-fill"></i> Master Shift
        </a>
        <a href="{{ route('admin.lokasi-kantor.index') }}" class="sidebar-link {{ request()->routeIs('admin.lokasi-kantor.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> Lokasi Kantor
        </a>
        <a href="{{ route('admin.kalender-libur.index') }}" class="sidebar-link {{ request()->routeIs('admin.kalender-libur.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-x-fill"></i> Kalender Libur
        </a>

        <div class="sidebar-section">Cuti &amp; Laporan</div>
        <a href="{{ route('admin.cuti.index') }}" class="sidebar-link {{ request()->routeIs('admin.cuti.*') ? 'active' : '' }}">
            <i class="bi bi-journal-check"></i> Approval Cuti
            @php $pendingCuti = \App\Models\Cuti::where('status', 'pending')->count(); @endphp
            @if($pendingCuti > 0)<span class="sidebar-badge">{{ $pendingCuti }}</span>@endif
        </a>
        <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i> Laporan Absensi
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="flex-1">
                <div class="sidebar-user-name">{{ Str::limit(auth()->user()->name, 18) }}</div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                @csrf
                <button type="submit" class="btn btn-sm border-0 p-0" title="Logout"
                        onclick="return confirm('Yakin ingin logout?')"
                        style="color:var(--text-cream);opacity:0.6;transition:opacity .2s"
                        onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=.6">
                    <i class="bi bi-box-arrow-right" style="font-size:1.05rem"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="main-content">
    <header class="topbar">
        <button class="btn btn-sm d-lg-none me-3 border-0" onclick="toggleSidebar()" style="color:var(--text-soft)">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-actions">
            <span style="font-size:0.78rem;color:var(--text-soft)" class="d-none d-md-inline">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </header>

    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <main class="page-content">
        @yield('content')
    </main>

    <footer class="text-center py-3 small border-top"
            style="background:var(--warm-white);color:var(--text-soft);border-color:var(--warm-border) !important">
        Oobake Bakery &mdash; Sistem Absensi Karyawan &copy; {{ date('Y') }}
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show');
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    }
    document.querySelectorAll('.alert').forEach(function(alert) {
        // Hanya close alert yang memiliki tombol btn-close (session alerts)
        if(alert.querySelector('.btn-close')) {
            setTimeout(function() {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 6000); // dilamakan sedikit jadi 6 detik
        }
    });
</script>

@stack('scripts')
</body>
</html>
