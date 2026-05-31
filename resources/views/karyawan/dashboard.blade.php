@extends('layouts.karyawan')

@section('title', 'Dashboard Karyawan')

@section('content')
<div class="row g-4">

    {{-- GREETING + SHIFT CARD --}}
    <div class="col-12">
        <div class="card-custom p-0 overflow-hidden">
            <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:1.75rem 2rem;position:relative;overflow:hidden">
                <div style="position:absolute;width:250px;height:250px;background:radial-gradient(circle,rgba(37,99,235,0.15),transparent);top:-100px;right:-50px;border-radius:50%"></div>
                <div class="row align-items-center">
                    <div class="col-12 col-md-7">
                        <div style="color:rgba(255,255,255,0.5);font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </div>
                        <h1 style="color:white;font-size:1.6rem;font-weight:800;margin:0.3rem 0 0.25rem;line-height:1.2">
                            Selamat datang, {{ $karyawan->nama_lengkap }}!
                        </h1>
                        <div style="color:rgba(255,255,255,0.55);font-size:0.85rem">
                            {{ $karyawan->jabatan }}
                        </div>
                    </div>
                    <div class="col-12 col-md-5 mt-3 mt-md-0">
                        @if($shiftHariIni)
                        <div style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:1rem 1.25rem">
                            <div style="color:rgba(255,255,255,0.45);font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em">
                                <i class="bi bi-clock me-1"></i> Shift Hari Ini
                            </div>
                            <div style="color:white;font-size:1.05rem;font-weight:700;margin-top:0.3rem">
                                {{ $shiftHariIni->nama_shift }}
                            </div>
                            <div style="color:rgba(255,255,255,0.55);font-size:0.8rem;margin-top:0.15rem">
                                {{ $shiftHariIni->jam_masuk_format }} &mdash; {{ $shiftHariIni->jam_keluar_format }}
                                (toleransi {{ $shiftHariIni->toleransi_menit }} mnt)
                            </div>
                        </div>
                        @else
                        <div style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);border-radius:12px;padding:1rem 1.25rem">
                            <div style="color:#fbbf24;font-size:0.8rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Belum ada shift yang ditugaskan.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#f8fafc;border-top:1px solid #e2e8f0">
                @if($absensiHariIni && $absensiHariIni->jam_masuk)
                    <div class="d-flex align-items-center gap-3">
                        <span style="font-size:0.8rem;font-weight:600;color:#10b981"><i class="bi bi-check-circle-fill me-1"></i>Sudah Absen</span>
                        <span style="font-size:0.8rem;color:#64748b">Masuk: <strong>{{ substr($absensiHariIni->jam_masuk, 0, 5) }}</strong>
                        @if($absensiHariIni->jam_keluar) | Keluar: <strong>{{ substr($absensiHariIni->jam_keluar, 0, 5) }}</strong>@endif</span>
                        <span class="status-badge badge-{{ $absensiHariIni->status_kehadiran }}">{{ ucfirst($absensiHariIni->status_kehadiran) }}</span>
                    </div>
                @else
                    <div><span style="font-size:0.8rem;font-weight:600;color:#d97706"><i class="bi bi-exclamation-circle-fill me-1"></i>Belum Absen Hari Ini</span></div>
                @endif
                <a href="{{ route('karyawan.absensi.index') }}" class="btn btn-sm btn-primary fw-bold" style="border-radius:8px">
                    <i class="bi bi-camera me-1"></i> Buka Kamera Absen
                </a>
            </div>
        </div>
    </div>

    {{-- STATISTIK BULAN INI --}}
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-value text-success">{{ $statistikBulanIni->get('hadir', 0) + $statistikBulanIni->get('terlambat', 0) }}</div>
            <div class="mini-stat-label"><i class="bi bi-check-circle text-success me-1"></i>Hadir Bulan Ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-value text-warning">{{ $statistikBulanIni->get('terlambat', 0) }}</div>
            <div class="mini-stat-label"><i class="bi bi-clock-history text-warning me-1"></i>Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-value text-danger">{{ $statistikBulanIni->get('alpa', 0) }}</div>
            <div class="mini-stat-label"><i class="bi bi-x-circle text-danger me-1"></i>Alpa</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div class="mini-stat-value text-primary">{{ $statistikBulanIni->get('cuti', 0) }}</div>
            <div class="mini-stat-label"><i class="bi bi-calendar-check text-primary me-1"></i>Hari Cuti</div>
        </div>
    </div>

    {{-- ALERT CUTI --}}
    @if($cutiAktif)
    <div class="col-12">
        <div class="alert mb-0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;border-left:4px solid #2563eb">
            <i class="bi bi-calendar-check-fill text-primary me-2"></i>
            <strong style="color:#1e40af">Cuti Aktif:</strong>
            <span style="color:#1e40af"> {{ ucfirst($cutiAktif->jenis_cuti) }} — {{ $cutiAktif->tanggal_mulai->translatedFormat('d F') }} s/d {{ $cutiAktif->tanggal_selesai->translatedFormat('d F Y') }}</span>
        </div>
    </div>
    @endif
    @if($cutiPending)
    <div class="col-12">
        <div class="alert mb-0" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;border-left:4px solid #f59e0b">
            <i class="bi bi-hourglass-split text-warning me-2"></i>
            <strong style="color:#92400e">Pengajuan Pending:</strong>
            <span style="color:#92400e"> {{ ucfirst($cutiPending->jenis_cuti) }} — {{ $cutiPending->tanggal_mulai->translatedFormat('d F') }} s/d {{ $cutiPending->tanggal_selesai->translatedFormat('d F Y') }}</span>
        </div>
    </div>
    @endif

    {{-- RIWAYAT ABSENSI --}}
    <div class="col-12">
        <div class="card-custom">
            <div class="card-custom-header">
                <div class="card-icon" style="background:#eff6ff"><i class="bi bi-calendar3 text-primary"></i></div>
                <h2 class="card-title-custom">Riwayat Absensi 30 Hari Terakhir</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Durasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayatAbsensi as $absen)
                        <tr>
                            <td style="font-weight:600;font-size:0.82rem">{{ $absen->tanggal->translatedFormat('D, d M Y') }}</td>
                            <td style="font-variant-numeric:tabular-nums">{{ $absen->jam_masuk ? substr($absen->jam_masuk,0,5) : '—' }}</td>
                            <td style="font-variant-numeric:tabular-nums">{{ $absen->jam_keluar ? substr($absen->jam_keluar,0,5) : '—' }}</td>
                            <td style="font-size:0.8rem;color:#64748b">{{ $absen->durasi_kerja_format ?? '—' }}</td>
                            <td><span class="status-badge badge-{{ $absen->status_kehadiran }}">{{ ucfirst($absen->status_kehadiran) }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size:2rem;opacity:0.3"></i>
                                <div class="mt-2 small">Belum ada riwayat absensi</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
