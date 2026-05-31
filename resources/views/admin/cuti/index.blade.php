@extends('layouts.admin')

@section('title', 'Approval Cuti')
@section('page-title', 'Approval Pengajuan Cuti')

@section('content')
{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value text-warning">{{ $totalPending }}</div>
            <div class="stat-label"><i class="bi bi-hourglass-split me-1 text-warning"></i>Menunggu</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value text-success">{{ $totalApproved }}</div>
            <div class="stat-label"><i class="bi bi-check-circle me-1 text-success"></i>Disetujui</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value text-danger">{{ $totalRejected }}</div>
            <div class="stat-label"><i class="bi bi-x-circle me-1 text-danger"></i>Ditolak</div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="content-card mb-4">
    <div class="content-card-body">
        <form method="GET" class="row g-2">
            <div class="col-12 col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="bulan" class="form-select">
                    <option value="">Semua Bulan</option>
                    @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $i)->translatedFormat('F') }}
                    </option>
                    @endfor
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary-custom w-100">Filter</button>
            </div>
            <div class="col-12 col-md-1">
                <a href="{{ route('admin.cuti.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="content-card">
    <div class="content-card-header">
        <h2 class="content-card-title"><i class="bi bi-journal-check text-primary me-2"></i>Daftar Pengajuan Cuti</h2>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-secondary rounded-pill">{{ $cuti->total() }} total</span>
            <a href="{{ route('admin.cuti.export-pdf', request()->all()) }}" target="_blank" class="btn btn-sm btn-danger fw-bold">
                <i class="bi bi-file-earmark-pdf me-1"></i> PDF
            </a>
            <a href="{{ route('admin.cuti.export-excel', request()->all()) }}" class="btn btn-sm btn-success fw-bold">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Jenis</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Hari Kerja</th>
                    <th>Status</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cuti as $c)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:0.85rem;color:#1e293b">{{ $c->karyawan->nama_lengkap ?? '—' }}</div>
                        <div style="font-size:0.72rem;color:#94a3b8">{{ $c->karyawan->nip ?? '' }}</div>
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;font-size:0.72rem">
                            {{ ucfirst($c->jenis_cuti) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem">{{ $c->tanggal_mulai->translatedFormat('d M Y') }}</td>
                    <td style="font-size:0.82rem">{{ $c->tanggal_selesai->translatedFormat('d M Y') }}</td>
                    <td class="text-center">
                        <strong>{{ $c->tanggal_mulai->diffInWeekdays($c->tanggal_selesai->addDay()) }}</strong>
                    </td>
                    <td>
                        <span class="status-badge badge-{{ $c->status }}">{{ ucfirst($c->status) }}</span>
                    </td>
                    <td style="font-size:0.75rem;color:#64748b">{{ $c->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.cuti.show', $c) }}"
                           class="btn btn-sm" style="background:#eff6ff;color:#2563eb;border-radius:6px;font-size:0.72rem">
                            <i class="bi bi-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:0.3"></i>
                        <div class="mt-2 small">Tidak ada pengajuan cuti</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cuti->hasPages())
    <div class="p-3 border-top">{{ $cuti->links() }}</div>
    @endif
</div>
@endsection
