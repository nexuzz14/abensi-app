@extends('layouts.karyawan')

@section('title', 'Riwayat Cuti')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="mb-0" style="font-size:1.3rem;font-weight:800;color:#1e293b">Pengajuan Cuti</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">Riwayat dan status pengajuan cuti Anda</p>
    </div>
    <a href="{{ route('karyawan.cuti.create') }}" class="btn btn-primary fw-bold" style="border-radius:10px">
        <i class="bi bi-plus-lg me-1"></i> Ajukan Cuti Baru
    </a>
</div>

<div class="card-custom">
    <div class="card-custom-header">
        <div class="card-icon" style="background:#eff6ff"><i class="bi bi-journal-text text-primary"></i></div>
        <h2 class="card-title-custom">Riwayat Pengajuan</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Jenis Cuti</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Jumlah Hari</th>
                    <th>Alasan</th>
                    <th>Status</th>
                    <th>Catatan Admin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatCuti as $index => $cuti)
                <tr>
                    <td class="text-muted small">{{ $riwayatCuti->firstItem() + $index }}</td>
                    <td>
                        <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb;font-size:0.72rem;font-weight:600">
                            {{ ucfirst($cuti->jenis_cuti) }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem">{{ $cuti->tanggal_mulai->translatedFormat('d M Y') }}</td>
                    <td style="font-size:0.82rem">{{ $cuti->tanggal_selesai->translatedFormat('d M Y') }}</td>
                    <td class="text-center">
                        <strong>{{ $cuti->tanggal_mulai->diffInWeekdays($cuti->tanggal_selesai->addDay()) }}</strong>
                        <span class="text-muted" style="font-size:0.72rem"> hari</span>
                    </td>
                    <td style="font-size:0.82rem;max-width:180px">
                        <span title="{{ $cuti->alasan }}">{{ Str::limit($cuti->alasan, 40) }}</span>
                    </td>
                    <td>
                        <span class="status-badge badge-{{ $cuti->status }}">
                            {{ ucfirst($cuti->status) }}
                        </span>
                    </td>
                    <td style="font-size:0.78rem;color:#64748b;max-width:150px">
                        {{ $cuti->catatan_admin ? Str::limit($cuti->catatan_admin, 50) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:0.25;color:#94a3b8"></i>
                        <div class="mt-2 text-muted small">Belum ada pengajuan cuti</div>
                        <a href="{{ route('karyawan.cuti.create') }}" class="btn btn-sm btn-primary mt-2">
                            Ajukan Cuti Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($riwayatCuti->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">Menampilkan {{ $riwayatCuti->firstItem() }}–{{ $riwayatCuti->lastItem() }} dari {{ $riwayatCuti->total() }}</div>
        {{ $riwayatCuti->links() }}
    </div>
    @endif
</div>
@endsection
