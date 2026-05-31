@extends('layouts.karyawan')

@section('title', 'Absensi — Check In / Out')

@push('styles')
<style>
    /* ========================
       ABSENSI PAGE STYLES
    ======================== */
    .absensi-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .absensi-hero::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%);
        top: -100px;
        right: -100px;
    }

    .webcam-section {
        background: #0f172a;
        border-radius: 16px;
        padding: 1.5rem;
        overflow: hidden;
    }

    .webcam-wrapper {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }

    #videoEl {
        width: 100%;
        border-radius: 12px;
        display: block;
        transform: scaleX(-1);
        background: #000;
        min-height: 300px;
    }

    #canvasOverlay {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        transform: scaleX(-1);
        border-radius: 12px;
    }

    /* Detection status di atas webcam */
    .detection-badge {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.75);
        color: white;
        padding: 0.35rem 0.875rem;
        border-radius: 100px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .detection-badge.detected { background: rgba(16,185,129,0.85); }
    .detection-badge.undetected { background: rgba(245,158,11,0.85); }
    .detection-badge.matched { background: rgba(37,99,235,0.85); }
    .detection-badge.loading { background: rgba(124,58,237,0.85); }

    /* Liveness overlay */
    .liveness-overlay {
        position: absolute;
        bottom: 10px;
        left: 0; right: 0;
        margin: 0 1rem;
        background: rgba(0,0,0,0.8);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        backdrop-filter: blur(8px);
        display: none;
    }

    .liveness-overlay.show { display: block; }

    /* EAR meter visual indicator */
    .ear-meter {
        height: 4px;
        background: rgba(255,255,255,0.15);
        border-radius: 4px;
        margin-top: 6px;
        overflow: hidden;
        position: relative;
    }
    .ear-meter-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.1s ease, background 0.1s ease;
        width: 0%;
    }
    .ear-meter-threshold {
        position: absolute;
        top: -2px;
        width: 2px;
        height: 8px;
        background: #f59e0b;
        border-radius: 1px;
    }

    .liveness-retry-btn {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: none;
        color: white;
        padding: 0.35rem 1rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 8px;
        display: none;
    }
    .liveness-retry-btn:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    .blink-dots {
        display: flex;
        gap: 0.4rem;
        margin-top: 0.4rem;
    }

    .blink-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        transition: all 0.2s;
    }

    .blink-dot.done {
        background: #10b981;
        border-color: #10b981;
        box-shadow: 0 0 8px rgba(16,185,129,0.5);
    }

    /* Action panel */
    .action-panel {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
    }

    .clock-display {
        font-size: 3rem;
        font-weight: 800;
        color: #1e293b;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.02em;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .date-display {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .btn-clock-in {
        background: linear-gradient(135deg, #10b981, #059669);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        padding: 1rem 2rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-clock-in:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16,185,129,0.4);
        color: white;
    }

    .btn-clock-out {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 700;
        font-size: 1rem;
        padding: 1rem 2rem;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-clock-out:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(245,158,11,0.4);
        color: white;
    }

    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Match result card */
    .match-result {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 0.875rem;
        display: none;
    }

    .match-result.show { display: block; }

    /* Status item dalam panel */
    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.82rem;
    }

    .status-item:last-child { border-bottom: none; }
    .status-label { color: #64748b; font-weight: 500; }
    .status-value { font-weight: 600; color: #1e293b; }

    /* GPS indicator */
    .gps-indicator {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
    }

    .gps-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #94a3b8;
    }

    .gps-dot.connected { background: #10b981; animation: pulse-dot 1.5s ease infinite; }
    .gps-dot.error { background: #ef4444; }

    @keyframes pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
        50% { box-shadow: 0 0 0 4px rgba(16,185,129,0); }
    }

    /* Selesai state */
    .selesai-card {
        text-align: center;
        padding: 2rem;
    }

    .selesai-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin: 0 auto 1.25rem;
        box-shadow: 0 8px 24px rgba(16,185,129,0.4);
    }
</style>
@endpush

@section('content')

{{-- Header Info --}}
<div class="absensi-hero">
    <div class="row align-items-center">
        <div class="col-12 col-md-8">
            <div style="color:rgba(255,255,255,0.5);font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em">
                Portal Absensi
            </div>
            <h1 style="color:white;font-size:1.5rem;font-weight:800;margin:0.25rem 0">
                Selamat Datang, {{ $karyawan->nama_lengkap }}!
            </h1>
            <div style="color:rgba(255,255,255,0.6);font-size:0.85rem">
                {{ $karyawan->jabatan }}
            </div>
        </div>
        <div class="col-12 col-md-4 mt-3 mt-md-0">
            @if($shiftHariIni)
            <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.875rem 1rem">
                <div style="color:rgba(255,255,255,0.5);font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em">
                    Shift Hari Ini
                </div>
                <div style="color:white;font-size:1rem;font-weight:700;margin-top:0.25rem">
                    {{ $shiftHariIni->nama_shift }}
                </div>
                <div style="color:rgba(255,255,255,0.6);font-size:0.8rem">
                    <i class="bi bi-clock me-1"></i>
                    {{ $shiftHariIni->jam_masuk_format }} — {{ $shiftHariIni->jam_keluar_format }}
                    (Toleransi {{ $shiftHariIni->toleransi_menit }} mnt)
                </div>
            </div>
            @else
            <div style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);border-radius:12px;padding:0.875rem 1rem">
                <div style="color:#f59e0b;font-size:0.8rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Belum ada shift yang ditugaskan.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($mode === 'selesai')
{{-- ========================
     MODE: SUDAH SELESAI
======================== --}}
<div class="row g-4">
    <div class="col-12 col-lg-6 offset-lg-3">
        <div class="card-custom">
            <div class="selesai-card">
                <div class="selesai-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2 style="font-size:1.25rem;font-weight:800;color:#1e293b;margin-bottom:0.5rem">
                    Absensi tgl {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }} selesai!
                </h2>
                <p class="text-muted" style="font-size:0.875rem">
                    @if($absensiHariIni && in_array($absensiHariIni->status_kehadiran, ['cuti', 'izin', 'sakit']))
                        Hari ini Anda berstatus {{ ucfirst($absensiHariIni->status_kehadiran) }}.
                    @else
                        Anda sudah check-in dan check-out hari ini.
                    @endif
                </p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div style="background:#f0fdf4;border-radius:10px;padding:0.875rem;text-align:center">
                            <div style="font-size:1.5rem;font-weight:800;color:#10b981">
                                {{ $absensiHariIni?->jam_masuk ? substr($absensiHariIni->jam_masuk, 0, 5) : '-' }}
                            </div>
                            <div style="font-size:0.75rem;color:#16a34a;font-weight:600">Check In</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#fef9c3;border-radius:10px;padding:0.875rem;text-align:center">
                            <div style="font-size:1.5rem;font-weight:800;color:#d97706">
                                {{ $absensiHariIni?->jam_keluar ? substr($absensiHariIni->jam_keluar, 0, 5) : '-' }}
                            </div>
                            <div style="font-size:0.75rem;color:#d97706;font-weight:600">Check Out</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="status-badge badge-{{ $absensiHariIni?->status_kehadiran ?? 'hadir' }} px-3 py-2">
                        {{ ucfirst($absensiHariIni?->status_kehadiran ?? 'hadir') }}
                    </span>
                </div>
                <a href="{{ route('karyawan.dashboard') }}" class="btn btn-outline-secondary mt-3">
                    <i class="bi bi-house me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

@else
{{-- ========================
     MODE: CHECK IN / CHECK OUT
======================== --}}

@if(!$sudahTerdaftar)
<div class="row g-4">
    <div class="col-12 col-lg-6 offset-lg-3">
        <div class="card-custom text-center py-5">
            <div style="width:80px;height:80px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;color:#ef4444;font-size:2rem">
                <i class="bi bi-person-bounding-box"></i>
            </div>
            <h2 style="font-size:1.25rem;font-weight:800;color:#1e293b;margin-bottom:0.5rem">
                Wajah Belum Terdaftar!
            </h2>
            <p class="text-muted" style="font-size:0.875rem;margin-bottom:1.5rem">
                Anda belum dapat melakukan absensi karena data wajah Anda belum terdaftar di sistem.
                Silakan daftarkan wajah Anda terlebih dahulu melalui halaman Profil.
            </p>
            <a href="{{ route('karyawan.profil.face-register') }}" class="btn btn-primary" style="background:#10b981;border:none">
                <i class="bi bi-camera me-1"></i> Daftarkan Wajah Sekarang
            </a>
        </div>
    </div>
</div>
@else

<div class="row g-4">

    {{-- WEBCAM SECTION --}}
    <div class="col-12 col-lg-7">
        <div class="webcam-section">
            <h3 style="color:white;font-size:0.9rem;font-weight:700;margin-bottom:1rem">
                <i class="bi bi-camera-fill me-2"></i> Kamera Face Recognition
            </h3>

            <div class="webcam-wrapper mb-3">
                <video id="videoEl" autoplay muted playsinline></video>
                <canvas id="canvasOverlay"></canvas>

                {{-- Status badge --}}
                <div class="detection-badge loading" id="detectionBadge">
                    <span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;border-width:2px"></span>
                    Memuat AI...
                </div>

                {{-- Liveness Overlay --}}
                <div class="liveness-overlay" id="livenessOverlay">
                    <div style="color:white;font-size:0.8rem;font-weight:600;margin-bottom:0.375rem">
                        <i class="bi bi-eye me-1"></i>
                        <span id="livenessText">Kedip mata <strong>1x</strong> — perlahan dan jelas</span>
                    </div>
                    <div class="blink-dots">
                        <div class="blink-dot" id="blink1"></div>
                        <div class="blink-dot" id="blink2"></div>
                        <div style="color:rgba(255,255,255,0.5);font-size:0.7rem;margin-left:0.5rem" id="livenessTimer">
                            Sisa waktu: 30 detik
                        </div>
                    </div>
                    {{-- EAR Meter: visual indikator agar user tahu matanya terdeteksi --}}
                    <div style="display:flex;align-items:center;gap:6px;margin-top:6px">
                        <span id="earLabel" style="color:rgba(255,255,255,0.6);font-size:0.65rem;min-width:60px">Mata: —</span>
                        <div class="ear-meter" style="flex:1">
                            <div class="ear-meter-bar" id="earMeterBar"></div>
                            <div class="ear-meter-threshold" style="left:75%"></div>
                        </div>
                    </div>
                    <div class="mt-1" style="font-size:0.7rem;color:rgba(255,255,255,0.4)">
                        💡 Tips: Kedipkan mata secara perlahan (tutup 1 detik, lalu buka). Pastikan pencahayaan cukup.
                    </div>
                    <button type="button" class="liveness-retry-btn" id="livenessRetryBtn" onclick="retryLiveness()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                    </button>
                </div>
            </div>

            {{-- Match Result --}}
            <div class="match-result" id="matchResult">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1.25rem;flex-shrink:0">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div style="font-weight:700;color:#166534;font-size:0.9rem" id="matchName">—</div>
                        <div style="font-size:0.75rem;color:#4ade80" id="matchInfo">—</div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge" style="background:#dcfce7;color:#166534;font-size:0.7rem" id="matchScore">—</span>
                    </div>
                </div>
            </div>

            {{-- Instruksi liveness --}}
            <div class="mt-3 p-3 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07)">
                <p style="color:rgba(255,255,255,0.6);font-size:0.78rem;margin:0;text-align:center" id="instruksiText">
                    Memuat model AI... harap tunggu sebentar
                </p>
            </div>
        </div>
    </div>

    {{-- ACTION PANEL --}}
    <div class="col-12 col-lg-5">
        <div class="action-panel">
            {{-- Jam Digital --}}
            <div class="mb-4">
                <div class="clock-display" id="clockDisplay">00:00:00</div>
                <div class="date-display" id="dateDisplay">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>

            {{-- Status Hari Ini --}}
            @if($absensiHariIni && $absensiHariIni->jam_masuk)
            <div class="p-3 rounded-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0">
                <div style="font-size:0.75rem;font-weight:700;color:#166534;margin-bottom:0.5rem">
                    ✅ Sudah Check-In
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <div style="font-size:1.5rem;font-weight:800;color:#10b981">
                            {{ substr($absensiHariIni->jam_masuk, 0, 5) }}
                        </div>
                        <div style="font-size:0.72rem;color:#16a34a">Jam Masuk</div>
                    </div>
                    <div class="ms-auto">
                        <span class="status-badge badge-{{ $absensiHariIni->status_kehadiran }}">
                            {{ ucfirst($absensiHariIni->status_kehadiran) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Status Validasi --}}
            <div class="mb-4">
                <div style="font-size:0.75rem;font-weight:700;color:#1e293b;margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em">
                    Status Validasi
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="bi bi-person-bounding-box me-2"></i>Face Match</span>
                    <span class="status-value" id="statusFace">
                        <span class="badge bg-secondary rounded-pill" style="font-size:0.7rem">Belum</span>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="bi bi-eye me-2"></i>Liveness</span>
                    <span class="status-value" id="statusLiveness">
                        <span class="badge bg-secondary rounded-pill" style="font-size:0.7rem">Belum</span>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label"><i class="bi bi-geo-alt me-2"></i>GPS</span>
                    <span class="status-value" id="statusGps">
                        <div class="gps-indicator">
                            <div class="gps-dot" id="gpsDot"></div>
                            <span id="gpsText">Memuat...</span>
                        </div>
                    </span>
                </div>
            </div>

            <div class="d-flex gap-3 mt-3">
                <button class="btn-clock-in w-100" id="btnClockIn" disabled>
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    <span id="btnClockInText">Check In</span>
                </button>
                <button class="btn-clock-out w-100" id="btnClockOut" disabled>
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span id="btnClockOutText">Check Out</span>
                </button>
            </div>

            <div id="submitResult" class="mt-3"></div>
        </div>
    </div>

</div>
@endif
@endif

@endsection

@push('scripts')
@if($sudahTerdaftar && $mode !== 'selesai')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
// ================================================================
// ABSENSI SCRIPT — FACE RECOGNITION + LIVENESS + GPS
// ================================================================

const MODE          = '{{ $mode }}';
const MODEL_URL     = '/models';
const API_URL_DESCRIPTORS = '/api/face-descriptors';
const CLOCK_IN_URL  = '{{ route('karyawan.absensi.clock-in', [], false) }}';
const CLOCK_OUT_URL = '{{ route('karyawan.absensi.clock-out', [], false) }}';
const CSRF_TOKEN    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Koordinat kantor dari database
const KANTOR_LAT    = {{ $lokasiKantor?->latitude ?? -6.2 }};
const KANTOR_LNG    = {{ $lokasiKantor?->longitude ?? 106.8 }};
const KANTOR_RADIUS = {{ $lokasiKantor?->radius_meter ?? 100 }}; // meter

// ========================
// STATE
// ========================
let video          = document.getElementById('videoEl');
let canvas         = document.getElementById('canvasOverlay');
let isModelLoaded  = false;
let isCameraReady  = false;

// Face recognition state
let labeledFaceDescriptors = []; // Array of LabeledFaceDescriptors
let currentMatchId         = null;
let currentMatchName       = null;
let currentMatchScore      = null;

// Liveness state — DINONAKTIFKAN (langsung passed)
// Face recognition + GPS sudah cukup untuk validasi kehadiran
let livenessStatus  = 'passed';
let livenessStarted = true;

// GPS state
let gpsLatitude  = null;
let gpsLongitude = null;
let gpsAccuracy  = null;
let gpsStatus    = 'loading';
let gpsJarak     = null; // Jarak ke kantor dalam meter

// Hitung jarak Haversine antara 2 koordinat (dalam meter)
function hitungJarakMeter(lat1, lng1, lat2, lng2) {
    const R = 6371000; // Radius bumi dalam meter
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ========================
// CLOCK DISPLAY
// ========================
function updateClock() {
    const now  = new Date();
    const hh   = String(now.getHours()).padStart(2, '0');
    const mm   = String(now.getMinutes()).padStart(2, '0');
    const ss   = String(now.getSeconds()).padStart(2, '0');
    const el   = document.getElementById('clockDisplay');
    if (el) el.textContent = `${hh}:${mm}:${ss}`;
}
setInterval(updateClock, 1000);
updateClock();

// ========================
// GPS TRACKER
// ========================
function initGPS() {
    if (!navigator.geolocation) {
        setGpsStatus('error', 'GPS tidak didukung');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            gpsLatitude  = pos.coords.latitude;
            gpsLongitude = pos.coords.longitude;
            gpsAccuracy  = pos.coords.accuracy;
            gpsStatus    = 'connected';

            // Hitung jarak ke kantor secara real-time
            gpsJarak = hitungJarakMeter(gpsLatitude, gpsLongitude, KANTOR_LAT, KANTOR_LNG);
            const dalamRadius = gpsJarak <= KANTOR_RADIUS;

            const jarakText = gpsJarak < 1000
                ? `${Math.round(gpsJarak)}m`
                : `${(gpsJarak/1000).toFixed(1)}km`;

            const warna = dalamRadius ? '#10b981' : '#ef4444';
            const icon  = dalamRadius ? '✓' : '✗';
            const info  = dalamRadius
                ? `Dalam area kantor (${jarakText})`
                : `Di luar area! Jarak: ${jarakText} (Maks: ${KANTOR_RADIUS}m)`;

            document.getElementById('gpsDot').className = 'gps-dot connected';
            document.getElementById('gpsText').textContent = `±${Math.round(gpsAccuracy)}m`;

            updateStatusItem('statusGps', `
                <div class="gps-indicator" style="flex-direction:column;align-items:flex-start;gap:2px">
                    <div style="display:flex;align-items:center;gap:6px">
                        <div class="gps-dot connected" id="gpsDot"></div>
                        <span style="color:${warna};font-weight:600">${icon} ${jarakText} dari kantor</span>
                    </div>
                    <span style="font-size:0.65rem;color:#64748b">Akurasi: ±${Math.round(gpsAccuracy)}m</span>
                </div>
            `);

            if (!dalamRadius) {
                showInstruksi(`📍 Anda berada ${jarakText} dari kantor. Radius absen maksimal ${KANTOR_RADIUS}m. Pastikan Anda berada di lokasi kantor.`);
            } else if (gpsAccuracy > 100) {
                showInstruksi('⚠️ Akurasi GPS rendah (±' + Math.round(gpsAccuracy) + 'm). Pindah ke area terbuka untuk akurasi lebih baik.');
            }

            // Cek ulang apakah tombol sudah bisa aktif
            checkAllReady();
        },
        function(err) {
            if (err.message.includes('secure origins') || err.message.includes('secure origin') || err.message.includes('HTTPS')) {
                // BYPASS untuk development local HTTP
                gpsLatitude  = -6.2088; // Default Jakarta
                gpsLongitude = 106.8456;
                gpsAccuracy  = 10;
                gpsStatus    = 'connected';

                document.getElementById('gpsDot').className = 'gps-dot connected';
                document.getElementById('gpsText').textContent = '±10m (Bypass)';
                updateStatusItem('statusGps', `
                    <div class="gps-indicator">
                        <div class="gps-dot connected" id="gpsDot"></div>
                        <span style="color:#10b981;font-weight:600">±10m (Bypass Dev)</span>
                    </div>
                `);
            } else {
                gpsStatus = 'error';
                setGpsStatus('error', 'GPS gagal: ' + err.message);
            }
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function setGpsStatus(status, text) {
    document.getElementById('gpsDot').className = 'gps-dot ' + status;
    document.getElementById('gpsText').textContent = text;
}

// ========================
// LOAD MODELS
// ========================
async function loadModels() {
    showInstruksi('⏳ Memuat model AI... harap tunggu');
    const t0 = performance.now();

    // Load semua model secara PARALEL (jauh lebih cepat)
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);

    const ms = Math.round(performance.now() - t0);
    console.log(`[face-api] Models loaded in ${ms}ms`);
    isModelLoaded = true;
}

// ========================
// LOAD FACE DESCRIPTORS DARI SERVER
// ========================
async function loadFaceDescriptors() {
    try {
        const response = await fetch(API_URL_DESCRIPTORS, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();

        if (data.length === 0) {
            showInstruksi('⚠️ Belum ada karyawan yang mendaftarkan wajah di sistem.');
            return;
        }

        // Buat LabeledFaceDescriptors untuk setiap karyawan
        labeledFaceDescriptors = data.map(k => {
            const descriptorArray = new Float32Array(k.face_descriptor);
            return new faceapi.LabeledFaceDescriptors(
                String(k.id), // Label = ID karyawan (sebagai string)
                [descriptorArray]
            );
        });

        // Simpan mapping id -> nama untuk ditampilkan
        window.karyawanMap = {};
        data.forEach(k => {
            window.karyawanMap[String(k.id)] = {
                nama: k.nama_lengkap,
                jabatan: k.jabatan,
            };
        });

    } catch (err) {
        showInstruksi('❌ Gagal memuat data wajah dari server: ' + err.message);
    }
}

// ========================
// CAMERA
// ========================
async function startCamera() {
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error("Browser memblokir akses kamera. Anda harus menggunakan koneksi HTTPS atau localhost.");
        }
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 640, height: 480, facingMode: 'user' }
        });
        video.srcObject = stream;
        await new Promise(resolve => video.onloadedmetadata = resolve);
        video.play();

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        isCameraReady = true;
    } catch (err) {
        showInstruksi('❌ Gagal mengakses kamera: ' + err.message);
        const badge = document.getElementById('detectionBadge');
        if (badge) badge.innerHTML = '<i class="bi bi-camera-video-off"></i> Kamera Error';
    }
}

// ========================
// HITUNG EAR (Eye Aspect Ratio) untuk Liveness Detection
// EAR = (||p2-p6|| + ||p3-p5||) / (2 * ||p1-p4||)
// ========================
function hitungEAR(eyePoints) {
    const p1 = eyePoints[0];
    const p2 = eyePoints[1];
    const p3 = eyePoints[2];
    const p4 = eyePoints[3];
    const p5 = eyePoints[4];
    const p6 = eyePoints[5];

    const dist = (a, b) => Math.sqrt(Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2));

    const vertikal1 = dist(p2, p6);
    const vertikal2 = dist(p3, p5);
    const horizontal = dist(p1, p4);

    return (vertikal1 + vertikal2) / (2.0 * horizontal);
}

// Smoothing EAR agar tidak terpengaruh noise satu frame
function getSmoothedEAR(rawEAR) {
    earHistory.push(rawEAR);
    if (earHistory.length > EAR_SMOOTH_SIZE) earHistory.shift();
    return earHistory.reduce((a, b) => a + b, 0) / earHistory.length;
}

// Update visual EAR meter
function updateEarMeter(ear) {
    const bar = document.getElementById('earMeterBar');
    const label = document.getElementById('earLabel');
    if (!bar || !label) return;
    
    const pct = Math.min(100, Math.max(0, (ear / 0.45) * 100));
    bar.style.width = pct + '%';
    bar.style.background = ear < EAR_THRESHOLD ? '#ef4444' : '#10b981';
    label.textContent = `Mata: ${ear.toFixed(2)}`;
}

// ========================
// MULAI LIVENESS DETECTION
// ========================
function startLiveness() {
    if (livenessStatus === 'passed') return;

    blinkCount      = 0;
    eyeClosed       = false;
    eyeClosedFrames = 0;
    earHistory      = [];
    livenessStatus  = 'pending';

    document.getElementById('livenessOverlay').classList.add('show');
    document.getElementById('blink1').classList.remove('done');
    document.getElementById('blink2').classList.remove('done');
    document.getElementById('livenessText').innerHTML = 'Kedip mata <strong>1x</strong> — perlahan dan jelas';
    document.getElementById('livenessRetryBtn').style.display = 'none';

    let detikTersisa = 30;  // Tambah waktu jadi 30 detik
    document.getElementById('livenessTimer').textContent = 'Sisa waktu: 30 detik';

    if (livenessTimer) clearInterval(livenessTimer);
    livenessTimer = setInterval(() => {
        detikTersisa--;
        document.getElementById('livenessTimer').textContent = `Sisa waktu: ${detikTersisa} detik`;

        if (detikTersisa <= 0) {
            clearInterval(livenessTimer);
            if (livenessStatus !== 'passed') {
                livenessStatus = 'failed';
                document.getElementById('livenessText').innerHTML = '❌ Gagal mendeteksi kedipan. Coba lagi?';
                document.getElementById('livenessRetryBtn').style.display = 'inline-block';
                updateLivenessStatus(false);
            }
        }
    }, 1000);
}

// Fungsi retry liveness
function retryLiveness() {
    livenessStatus  = 'pending';
    livenessStarted = false;
    startLiveness();
}

function prosesLiveness(landmarks) {
    if (livenessStatus === 'passed' || livenessStatus === 'failed') return;

    const positions = landmarks.positions;

    // Titik mata kiri: 36-41, kanan: 42-47
    const mataKiri  = positions.slice(36, 42);
    const mataKanan = positions.slice(42, 48);

    const earKiri  = hitungEAR(mataKiri);
    const earKanan = hitungEAR(mataKanan);
    const earRata  = (earKiri + earKanan) / 2;

    // Smoothing untuk mengurangi noise
    const earSmooth = getSmoothedEAR(earRata);

    // Update visual meter
    updateEarMeter(earSmooth);

    if (earSmooth < EAR_THRESHOLD) {
        // Mata tertutup (kedip)
        eyeClosedFrames++;
        if (!eyeClosed && eyeClosedFrames >= MIN_CLOSED_FRAMES) {
            // Minimal beberapa frame tertutup = bukan noise, blink asli dimulai
            eyeClosed = true;
        }
    } else if (earSmooth > EAR_OPEN_THRESHOLD) {
        // Mata terbuka (hysteresis: harus benar-benar di atas threshold buka)
        if (eyeClosed) {
            // Transisi dari tertutup ke terbuka = satu kedipan selesai
            blinkCount++;
            eyeClosed = false;
            eyeClosedFrames = 0;

            if (blinkCount >= 1) document.getElementById('blink1').classList.add('done');
            if (blinkCount >= 2) document.getElementById('blink2').classList.add('done');

            document.getElementById('livenessText').innerHTML =
                blinkCount >= BLINK_TARGET
                    ? '<i class="bi bi-check-circle-fill me-1"></i> Verifikasi berhasil!'
                    : `Bagus! Kedip <strong>${BLINK_TARGET - blinkCount}x</strong> lagi`;

            if (blinkCount >= BLINK_TARGET) {
                // Liveness passed!
                livenessStatus = 'passed';
                clearInterval(livenessTimer);

                setTimeout(() => {
                    document.getElementById('livenessOverlay').classList.remove('show');
                }, 1500);

                updateLivenessStatus(true);
                checkAllReady();
            }
        } else {
            eyeClosedFrames = 0;
        }
    }
}

function updateLivenessStatus(passed) {
    const el = document.getElementById('statusLiveness');
    if (passed) {
        el.innerHTML = '<span class="badge rounded-pill" style="background:#dcfce7;color:#166534;font-size:0.7rem">✓ Passed</span>';
    } else {
        el.innerHTML = '<span class="badge rounded-pill" style="background:#fee2e2;color:#991b1b;font-size:0.7rem">✗ Failed</span>';
    }
}

// ========================
// DETECTION LOOP UTAMA
// Interval 200ms untuk menangkap kedipan mata dengan cepat
// Proses berat (descriptor) hanya dijalankan saat wajah belum dikenali
// ========================
let isDetecting = false; // Guard: hindari race condition kalau AI belum selesai frame sebelumnya
async function startDetectionLoop() {
    setInterval(async () => {
        if (!isCameraReady || !isModelLoaded || isDetecting) return;
        isDetecting = true;

        try {
            let detection;

            if (currentMatchId === null) {
                // BELUM MATCH: Jalankan proses berat lengkap dengan Face Descriptor
                detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.3 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
            } else {
                // SUDAH MATCH: Jalankan proses sangat ringan (hanya cari Landmark mata untuk Liveness)
                detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.3 }))
                    .withFaceLandmarks();
            }

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (!detection) {
                updateBadge('undetected', '👀 Tidak ada wajah terdeteksi');
                // PENTING: Reset state liveness saat wajah hilang
                // Agar kamera dialihkan tidak dihitung sebagai kedipan
                eyeClosed = false;
                eyeClosedFrames = 0;
                earHistory = [];
                return;
            }

            // === FACE RECOGNITION (Hanya jika belum match & punya descriptor) ===
            if (currentMatchId === null && detection.descriptor && labeledFaceDescriptors.length > 0) {
                const matcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.5);
                const match   = matcher.findBestMatch(detection.descriptor);

                if (match.label !== 'unknown') {
                    const karyawan  = window.karyawanMap[match.label];
                    currentMatchId   = parseInt(match.label);
                    currentMatchName = karyawan?.nama || '?';
                    currentMatchScore = (1 - match.distance).toFixed(2);

                    // Update UI match result
                    document.getElementById('matchResult').classList.add('show');
                    document.getElementById('matchName').textContent = currentMatchName;
                    document.getElementById('matchInfo').textContent = karyawan?.jabatan || '';
                    document.getElementById('matchScore').textContent = `${(currentMatchScore * 100).toFixed(0)}% match`;

                    document.getElementById('statusFace').innerHTML =
                        `<span class="badge rounded-pill" style="background:#dcfce7;color:#166534;font-size:0.7rem">✓ ${currentMatchName}</span>`;

                    updateBadge('matched', `✓ ${currentMatchName}`);
                } else {
                    updateBadge('undetected', '❓ Wajah tidak dikenali');
                    document.getElementById('matchResult').classList.remove('show');
                }
            } else if (labeledFaceDescriptors.length === 0) {
                updateBadge('detected', '✓ Wajah terdeteksi (Data kosong)');
            }

            // === LIVENESS & UI (Jika sudah match) ===
            if (currentMatchId !== null) {
                // Gambar bounding box hijau
                const dims    = faceapi.matchDimensions(canvas, video, true);
                const resized = faceapi.resizeResults(detection, dims);
                const box = resized.detection ? resized.detection.box : resized.box;
                const { x, y, width, height } = box;

                ctx.strokeStyle = '#10b981';
                ctx.lineWidth = 2;
                ctx.strokeRect(x, y, width, height);

                ctx.fillStyle = 'rgba(16,185,129,0.85)';
                ctx.fillRect(x, y - 24, Math.min(width, 200), 24);
                ctx.fillStyle = 'white';
                ctx.font = 'bold 12px Inter';
                ctx.fillText(currentMatchName.substring(0, 20), x + 5, y - 7);

                // Liveness dinonaktifkan — langsung update status sebagai passed
                updateLivenessStatus(true);

                // Cek apakah semua siap
                checkAllReady();
            }

        } catch (err) { /* ignore */ } finally {
            isDetecting = false; // Lepas guard setelah selesai
        }
    }, 120); // Dipercepat jadi 120ms agar kedipan mata cepat tidak terlewat
}

// ========================
// UPDATE DETECTION BADGE
// ========================
function updateBadge(type, text) {
    const badge = document.getElementById('detectionBadge');
    badge.className = 'detection-badge ' + type;
    badge.innerHTML = text;
}

// ========================
// CEK APAKAH SEMUA KONDISI SIAP
// ========================
function checkAllReady() {
    const faceReady     = currentMatchId !== null;
    const livenessReady = livenessStatus === 'passed';
    // GPS ready: sudah konek DAN berada dalam radius kantor
    const gpsReady      = gpsLatitude !== null && gpsStatus === 'connected' && gpsJarak !== null && gpsJarak <= KANTOR_RADIUS;

    const btnClockIn = document.getElementById('btnClockIn');
    const btnClockOut = document.getElementById('btnClockOut');
    const btnClockInText = document.getElementById('btnClockInText');
    const btnClockOutText = document.getElementById('btnClockOutText');

    if (faceReady && livenessReady && gpsReady) {
        if(btnClockIn) btnClockIn.disabled = false;
        if(btnClockOut) btnClockOut.disabled = false;
        
        showInstruksi('✅ Semua validasi OK! Pilih tombol Check In atau Check Out.');
    } else {
        if(btnClockIn) btnClockIn.disabled = true;
        if(btnClockOut) btnClockOut.disabled = true;
        const missing = [];
        if (!faceReady)     missing.push('wajah dikenali');
        if (!livenessReady) missing.push('liveness (kedip 2x)');
        if (!gpsReady)      missing.push('GPS');
        showInstruksi('⏳ Menunggu: ' + missing.join(', '));
    }
}

// ========================
// TAMPILKAN INSTRUKSI
// ========================
function showInstruksi(text) {
    const el = document.getElementById('instruksiText');
    if (el) el.innerHTML = text;
}

// ========================
// UPDATE STATUS ITEM
// ========================
function updateStatusItem(id, html) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = html;
}

async function submitAbsensi(tipe) {
    if (!currentMatchId || livenessStatus !== 'passed' || !gpsLatitude) return;

    const btn = tipe === 'clock-in' ? document.getElementById('btnClockIn') : document.getElementById('btnClockOut');
    const btnText = tipe === 'clock-in' ? document.getElementById('btnClockInText') : document.getElementById('btnClockOutText');
    
    // Disable both
    document.getElementById('btnClockIn').disabled = true;
    document.getElementById('btnClockOut').disabled = true;
    btnText.textContent = 'Memproses...';

    // Ambil foto dari video sebagai base64
    const tempCanvas = document.createElement('canvas');
    tempCanvas.width  = video.videoWidth;
    tempCanvas.height = video.videoHeight;
    tempCanvas.getContext('2d').drawImage(video, 0, 0);
    const fotoBase64 = tempCanvas.toDataURL('image/jpeg', 0.8);

    const url = tipe === 'clock-in' ? CLOCK_IN_URL : CLOCK_OUT_URL;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                foto_base64:     fotoBase64,
                latitude:        gpsLatitude,
                longitude:       gpsLongitude,
                accuracy:        gpsAccuracy,
                status_liveness: livenessStatus,
                face_match_id:   currentMatchId,
            })
        });

        const result = await response.json();
        const resDiv = document.getElementById('submitResult');

        if (result.success) {
            resDiv.innerHTML = `<div class="alert alert-success mt-2 py-2"><i class="bi bi-check-circle-fill me-1"></i>${result.message}</div>`;
            setTimeout(() => {
                window.location.href = "{{ route('karyawan.dashboard') }}";
            }, 1500);
        } else {
            resDiv.innerHTML = `<div class="alert alert-danger mt-2 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>${result.message}</div>`;
            // Enable again
            document.getElementById('btnClockIn').disabled = false;
            document.getElementById('btnClockOut').disabled = false;
            document.getElementById('btnClockInText').textContent = 'Check In';
            document.getElementById('btnClockOutText').textContent = 'Check Out';
        }
    } catch (err) {
        alert('Terjadi kesalahan jaringan.');
        document.getElementById('btnClockIn').disabled = false;
        document.getElementById('btnClockOut').disabled = false;
        document.getElementById('btnClockInText').textContent = 'Check In';
        document.getElementById('btnClockOutText').textContent = 'Check Out';
    }
}

document.getElementById('btnClockIn')?.addEventListener('click', () => submitAbsensi('clock-in'));
document.getElementById('btnClockOut')?.addEventListener('click', () => submitAbsensi('clock-out'));

// ========================
// MAIN INIT
// ========================
(async function init() {
    if (MODE === 'selesai') return;

    // 1. Inisialisasi GPS (non-blocking)
    initGPS();

    try {
        // 2. Nyalakan kamera DULU (agar user langsung lihat feed, tidak menunggu)
        showInstruksi('🎥 Mengaktifkan kamera...');
        await startCamera();
        showInstruksi('⏳ Kamera aktif! Memuat model AI...');

        // 3. Load model AI dan descriptor di background (sementara kamera sudah jalan)
        await loadModels();
        await loadFaceDescriptors();

        updateBadge('detected', '🔍 Mendeteksi wajah...');
        showInstruksi('Posisikan wajah Anda di depan kamera.');

        // 4. Mulai loop deteksi
        await startDetectionLoop();

        if(document.getElementById('btnClockInText')) document.getElementById('btnClockInText').textContent = 'Tunggu...';
        if(document.getElementById('btnClockOutText')) document.getElementById('btnClockOutText').textContent = 'Tunggu...';

    } catch (err) {
        showInstruksi('❌ Error: ' + err.message);
        updateBadge('error', '❌ ' + err.message);
        }
    })();
</script>
@endif
@endpush
