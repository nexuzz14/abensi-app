@extends('layouts.admin')

@section('title', 'Detail Cuti')
@section('page-title', 'Detail Pengajuan Cuti')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.cuti.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">
        Pengajuan Cuti — {{ $cuti->karyawan->nama_lengkap ?? '—' }}
    </h1>
</div>

<div class="row g-4">
    {{-- Info Karyawan & Cuti --}}
    <div class="col-12 col-lg-6">
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-person-fill text-primary me-2"></i>Info Karyawan</h2>
            </div>
            <div class="content-card-body">
                <table style="width:100%;font-size:0.85rem">
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0;width:40%">Nama</td>
                        <td style="font-weight:600">{{ $cuti->karyawan->nama_lengkap ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">NIP</td>
                        <td><code>{{ $cuti->karyawan->nip ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Jabatan</td>
                        <td>{{ $cuti->karyawan->jabatan ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-journal-text text-success me-2"></i>Detail Pengajuan</h2>
            </div>
            <div class="content-card-body">
                <table style="width:100%;font-size:0.85rem">
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0;width:40%">Jenis Cuti</td>
                        <td><span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb">{{ ucfirst($cuti->jenis_cuti) }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Tanggal Mulai</td>
                        <td style="font-weight:600">{{ $cuti->tanggal_mulai->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Tanggal Selesai</td>
                        <td style="font-weight:600">{{ $cuti->tanggal_selesai->translatedFormat('l, d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Jumlah Hari</td>
                        <td><strong>{{ $cuti->tanggal_mulai->diffInWeekdays($cuti->tanggal_selesai->addDay()) }} hari kerja</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Status</td>
                        <td><span class="status-badge badge-{{ $cuti->status }}">{{ ucfirst($cuti->status) }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Diajukan</td>
                        <td style="font-size:0.8rem;color:#64748b">{{ $cuti->created_at->translatedFormat('d M Y, H:i') }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <div style="font-size:0.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.375rem">Alasan</div>
                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:0.85rem;color:#334155">
                        {{ $cuti->alasan }}
                    </div>
                </div>

                @if($cuti->file_surat)
                <div class="mt-3">
                    <a href="{{ route('admin.cuti.download-surat', $cuti) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Unduh Surat Pendukung
                    </a>
                </div>
                @endif

                @if($cuti->status !== 'pending' && $cuti->approvedBy)
                <div class="mt-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div style="font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:0.375rem">Diproses oleh</div>
                    <div style="font-size:0.85rem;font-weight:600;color:#1e293b">{{ $cuti->approvedBy->name }}</div>
                    <div style="font-size:0.75rem;color:#94a3b8">{{ $cuti->tanggal_diproses ? \Carbon\Carbon::parse($cuti->tanggal_diproses)->translatedFormat('d M Y, H:i') : '—' }}</div>
                    @if($cuti->catatan_admin)
                    <div style="font-size:0.82rem;color:#475569;margin-top:0.5rem;font-style:italic">"{{ $cuti->catatan_admin }}"</div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Approve / Reject Forms --}}
    @if($cuti->status === 'pending')
    <div class="col-12 col-lg-6">
        {{-- Approve --}}
        <div class="content-card mb-4" style="border:2px solid #bbf7d0">
            <div class="content-card-header" style="background:#f0fdf4">
                <h2 class="content-card-title text-success">
                    <i class="bi bi-check-circle-fill me-2"></i>Setujui Cuti
                </h2>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.cuti.approve', $cuti) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="catatan_admin" rows="3" class="form-control"
                                  placeholder="Tambahkan catatan untuk karyawan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success fw-bold w-100"
                            onclick="return confirm('Setujui pengajuan cuti ini? Absensi akan otomatis diupdate.')">
                        <i class="bi bi-check-lg me-2"></i> Approve Cuti
                    </button>
                </form>
            </div>
        </div>

        {{-- Reject --}}
        <div class="content-card" style="border:2px solid #fecaca">
            <div class="content-card-header" style="background:#fef2f2">
                <h2 class="content-card-title text-danger">
                    <i class="bi bi-x-circle-fill me-2"></i>Tolak Cuti
                </h2>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.cuti.reject', $cuti) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-muted small">(dianjurkan)</span></label>
                        <textarea name="catatan_admin" rows="3" class="form-control"
                                  placeholder="Jelaskan alasan penolakan cuti..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger fw-bold w-100"
                            onclick="return confirm('Tolak pengajuan cuti ini?')">
                        <i class="bi bi-x-lg me-2"></i> Tolak Cuti
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
