@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

@section('content')
{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#dcfce7"><i class="bi bi-check-circle-fill" style="color:#16a34a"></i></div>
            </div>
            <div class="stat-value text-success">{{ number_format($summary['hadir']) }}</div>
            <div class="stat-label">Hadir Tepat Waktu</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#fef9c3"><i class="bi bi-clock-history" style="color:#d97706"></i></div>
            </div>
            <div class="stat-value text-warning">{{ number_format($summary['terlambat']) }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#fee2e2"><i class="bi bi-x-circle-fill" style="color:#dc2626"></i></div>
            </div>
            <div class="stat-value text-danger">{{ number_format($summary['alpa']) }}</div>
            <div class="stat-label">Alpa</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#dbeafe"><i class="bi bi-calendar-check-fill" style="color:#2563eb"></i></div>
            </div>
            <div class="stat-value text-primary">{{ number_format($summary['cuti']) }}</div>
            <div class="stat-label">Cuti</div>
        </div>
    </div>
</div>

{{-- Filter + Export --}}
<div class="content-card mb-4">
    <div class="content-card-header">
        <h2 class="content-card-title"><i class="bi bi-funnel text-primary me-2"></i>Filter Laporan</h2>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.laporan.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset/menghapus SELURUH data absensi karyawan? Tindakan ini tidak dapat dibatalkan!');">
                @csrf
                <button type="submit" class="btn btn-sm btn-dark fw-bold" style="border-radius:8px">
                    <i class="bi bi-trash3 me-1"></i> Reset Data
                </button>
            </form>
            <a href="{{ route('admin.laporan.pdf', request()->all()) }}" target="_blank"
               class="btn btn-sm btn-danger fw-bold" style="border-radius:8px">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('admin.laporan.excel', request()->all()) }}"
               class="btn btn-sm btn-success fw-bold" style="border-radius:8px">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
        </div>
    </div>
    <div class="content-card-body">
        <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-2">
            <div class="col-12 col-md-2">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">Semua</option>
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ ($filter['bulan'] ?? '') == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-1">
                <label class="form-label">Tahun</label>
                <input type="number" name="tahun" class="form-control" value="{{ $filter['tahun'] ?? now()->year }}" min="2020" max="2030">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" class="form-control" value="{{ $filter['tanggal_dari'] ?? '' }}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-control" value="{{ $filter['tanggal_sampai'] ?? '' }}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Karyawan</label>
                <select name="karyawan_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($karyawanList as $k)
                        <option value="{{ $k->id }}" {{ ($filter['karyawan_id'] ?? '') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="hadir"     {{ ($filter['status'] ?? '') == 'hadir'     ? 'selected' : '' }}>Hadir</option>
                    <option value="terlambat" {{ ($filter['status'] ?? '') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="alpa"      {{ ($filter['status'] ?? '') == 'alpa'      ? 'selected' : '' }}>Alpa</option>
                    <option value="cuti"      {{ ($filter['status'] ?? '') == 'cuti'      ? 'selected' : '' }}>Cuti</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary-custom"><i class="bi bi-search me-1"></i> Tampilkan</button>
                <a href="{{ route('admin.laporan.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="content-card">
    <div class="content-card-header">
        <h2 class="content-card-title"><i class="bi bi-table text-primary me-2"></i>Data Absensi</h2>
        <span class="badge bg-secondary rounded-pill">{{ $absensi->count() }} record</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>NIP</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Durasi</th>
                    <th>Status</th>
                    <th>GPS</th>
                    <th>Liveness</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $index => $a)
                <tr>
                    <td class="text-muted small">{{ $index + 1 }}</td>
                    <td><code style="font-size:0.75rem;background:#f1f5f9;padding:0.15rem 0.4rem;border-radius:4px">{{ $a->karyawan->nip ?? '—' }}</code></td>
                    <td style="font-weight:600;font-size:0.83rem;color:#1e293b">{{ $a->karyawan->nama_lengkap ?? '—' }}</td>
                    <td style="font-size:0.8rem;color:#64748b">{{ $a->karyawan->jabatan ?? '—' }}</td>
                    <td style="font-size:0.8rem">{{ $a->tanggal->translatedFormat('d M Y') }}</td>
                    <td style="font-variant-numeric:tabular-nums;font-size:0.83rem">{{ $a->jam_masuk ? substr($a->jam_masuk,0,5) : '—' }}</td>
                    <td style="font-variant-numeric:tabular-nums;font-size:0.83rem">{{ $a->jam_keluar ? substr($a->jam_keluar,0,5) : '—' }}</td>
                    <td style="font-size:0.78rem;color:#64748b">{{ $a->durasi_kerja_format ?? '—' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge badge-{{ $a->status_kehadiran }}">{{ ucfirst($a->status_kehadiran) }}</span>
                            <button type="button" class="btn btn-sm btn-link p-0 text-muted" onclick="openEditStatusModal('{{ $a->id }}', '{{ $a->status_kehadiran }}')" title="Edit Status">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        @if($a->status_fake_gps === 'clean')
                            <span style="font-size:0.72rem;color:#10b981"><i class="bi bi-check-circle-fill"></i></span>
                        @elseif($a->status_fake_gps === 'suspicious')
                            <span style="font-size:0.72rem;color:#f59e0b"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        @else
                            <span style="font-size:0.72rem;color:#94a3b8">—</span>
                        @endif
                    </td>
                    <td>
                        @if($a->status_liveness === 'passed')
                            <span style="font-size:0.72rem;color:#10b981"><i class="bi bi-eye-fill"></i></span>
                        @elseif($a->status_liveness === 'skipped')
                            <span style="font-size:0.72rem;color:#94a3b8">Skip</span>
                        @else
                            <span style="font-size:0.72rem;color:#ef4444"><i class="bi bi-eye-slash-fill"></i></span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:0.3"></i>
                        <div class="mt-2 small">Tidak ada data absensi untuk filter yang dipilih</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Edit Status --}}
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.1)">
            <form id="editStatusForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fs-6 fw-bold text-dark" id="editStatusModalLabel">Ubah Status Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-medium">Pilih Status Baru</label>
                        <select name="status_kehadiran" id="statusSelect" class="form-select form-select-sm" required>
                            <option value="hadir">Hadir</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="alpa">Alpa</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openEditStatusModal(id, currentStatus) {
        const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
        const form = document.getElementById('editStatusForm');
        const select = document.getElementById('statusSelect');
        
        // Setup action URL
        form.action = `/laporan/absensi/${id}/status`;
        
        // Set selected option
        select.value = currentStatus;
        
        modal.show();
    }
</script>
@endpush
@endsection
