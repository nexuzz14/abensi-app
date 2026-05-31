@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('content')

{{-- ========================
     STAT CARDS
======================== --}}
<div class="row g-3 mb-4">
    {{-- Total Hadir Bulan Ini --}}
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#dcfce7">
                    <i class="bi bi-check-circle-fill" style="color:#16a34a"></i>
                </div>
                <span class="text-muted" style="font-size:0.7rem">Bulan Ini</span>
            </div>
            <div class="stat-value text-success">{{ number_format($totalHadir) }}</div>
            <div class="stat-label">Total Hadir</div>
        </div>
    </div>

    {{-- Terlambat --}}
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#fef9c3">
                    <i class="bi bi-clock-history" style="color:#d97706"></i>
                </div>
                <span class="text-muted" style="font-size:0.7rem">Bulan Ini</span>
            </div>
            <div class="stat-value text-warning">{{ number_format($totalTerlambat) }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>

    {{-- Alpa --}}
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#fee2e2">
                    <i class="bi bi-x-circle-fill" style="color:#dc2626"></i>
                </div>
                <span class="text-muted" style="font-size:0.7rem">Bulan Ini</span>
            </div>
            <div class="stat-value text-danger">{{ number_format($totalAlpa) }}</div>
            <div class="stat-label">Alpa</div>
        </div>
    </div>

    {{-- Karyawan Aktif --}}
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:#dbeafe">
                    <i class="bi bi-people-fill" style="color:#2563eb"></i>
                </div>
                <span class="text-muted" style="font-size:0.7rem">Total</span>
            </div>
            <div class="stat-value text-primary">{{ number_format($totalKaryawanAktif) }}</div>
            <div class="stat-label">Karyawan Aktif</div>
        </div>
    </div>
</div>

{{-- ========================
     ROW 2: ALERT CARDS
======================== --}}
<div class="row g-3 mb-4">
    @if($cutiMenungguApproval > 0)
    <div class="col-12 col-md-6">
        <div class="alert alert-warning d-flex align-items-center justify-content-between mb-0"
             style="border-radius:12px;border-left:4px solid #f59e0b">
            <div>
                <strong><i class="bi bi-journal-check me-2"></i>{{ $cutiMenungguApproval }} Pengajuan Cuti</strong>
                <div class="small mt-1">menunggu persetujuan Anda</div>
            </div>
            <a href="{{ route('admin.cuti.index') }}?status=pending" class="btn btn-warning btn-sm fw-bold">
                Proses Sekarang
            </a>
        </div>
    </div>
    @endif

    @if($belumDaftarWajah > 0)
    <div class="col-12 col-md-6">
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-0"
             style="border-radius:12px;border-left:4px solid #3b82f6">
            <div>
                <strong><i class="bi bi-person-bounding-box me-2"></i>{{ $belumDaftarWajah }} Karyawan</strong>
                <div class="small mt-1">belum mendaftarkan wajah untuk absensi</div>
            </div>
            <a href="{{ route('admin.karyawan.index') }}" class="btn btn-info btn-sm fw-bold">
                Lihat Daftar
            </a>
        </div>
    </div>
    @endif
</div>

{{-- ========================
     ROW 3: GRAFIK + ABSENSI HARI INI
======================== --}}
<div class="row g-3">

    {{-- Grafik Kehadiran 7 Hari --}}
    <div class="col-12 col-xl-7">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>
                    Kehadiran 7 Hari Terakhir
                </h2>
            </div>
            <div class="content-card-body">
                <canvas id="attendanceChart" style="max-height:260px"></canvas>
            </div>
        </div>
    </div>

    {{-- Absensi Hari Ini --}}
    <div class="col-12 col-xl-5">
        <div class="content-card h-100">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="bi bi-clock text-success me-2"></i>
                    Absensi Hari Ini
                </h2>
                <span class="badge bg-success rounded-pill">{{ $sudahAbsenHariIni }} / {{ $totalKaryawanAktif }}</span>
            </div>
            <div style="overflow-y:auto;max-height:300px">
                @forelse($absensiHariIni as $absen)
                <div class="d-flex align-items-center gap-3 px-3 py-2"
                     style="border-bottom:1px solid #f1f5f9">
                    @if($absen->karyawan && $absen->karyawan->foto)
                        <img src="{{ asset('storage/'.$absen->karyawan->foto) }}" 
                             alt="Foto Profil"
                             class="rounded-circle flex-shrink-0"
                             style="width:36px;height:36px;object-fit:cover;border:2px solid #e2e8f0">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;font-weight:700;font-size:0.8rem">
                            {{ strtoupper(substr($absen->karyawan->nama_lengkap ?? 'K', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div style="font-size:0.8rem;font-weight:600;color:#1e293b">
                            {{ Str::limit($absen->karyawan->nama_lengkap ?? '-', 22) }}
                        </div>
                        <div style="font-size:0.7rem;color:#94a3b8">
                            Masuk: {{ $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '-' }}
                        </div>
                    </div>
                    <span class="status-badge badge-{{ $absen->status_kehadiran }}">
                        {{ ucfirst($absen->status_kehadiran) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size:2rem"></i>
                    <div class="mt-2 small">Belum ada yang absen hari ini</div>
                </div>
                @endforelse
            </div>
            <div class="p-3 border-top">
                <a href="{{ route('admin.laporan.index') }}?tanggal_dari={{ today()->toDateString() }}&tanggal_sampai={{ today()->toDateString() }}"
                   class="btn btn-sm btn-outline-secondary w-100">
                    Lihat Laporan Lengkap
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data dari PHP (server-side)
    const grafikData = @json($grafikData);

    const labels   = grafikData.map(d => d.tanggal);
    const hadir    = grafikData.map(d => d.hadir);
    const terlambat= grafikData.map(d => d.terlambat);
    const alpa     = grafikData.map(d => d.alpa);

    const ctx = document.getElementById('attendanceChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Hadir',
                    data: hadir,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Terlambat',
                    data: terlambat,
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Alpa',
                    data: alpa,
                    backgroundColor: '#ef4444',
                    borderRadius: 6,
                    borderSkipped: false,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyleWidth: 10,
                        font: { size: 11, family: 'Inter' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { family: 'Inter', size: 12 },
                    bodyFont: { family: 'Inter', size: 11 },
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 11 } }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { family: 'Inter', size: 11 }
                    },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
});
</script>
@endpush
