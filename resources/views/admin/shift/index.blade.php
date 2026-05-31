@extends('layouts.admin')

@section('title', 'Master Shift')
@section('page-title', 'Manajemen Shift')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-0" style="font-size:1.3rem;font-weight:800;color:#1e293b">Master Shift Kerja</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">Kelola jadwal shift karyawan</p>
    </div>
    <a href="{{ route('admin.shift.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Tambah Shift
    </a>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h2 class="content-card-title"><i class="bi bi-clock-fill text-primary me-2"></i>Daftar Shift</h2>
        <span class="badge bg-secondary rounded-pill">{{ $shifts->count() }} shift</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Shift</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Toleransi</th>
                    <th>Karyawan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $index => $shift)
                <tr>
                    <td class="text-muted small">{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight:700;color:#1e293b">{{ $shift->nama_shift }}</div>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:1.05rem">
                            {{ $shift->jam_masuk_format }}
                        </span>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:1.05rem">
                            {{ $shift->jam_keluar_format }}
                        </span>
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;font-size:0.75rem">
                            <i class="bi bi-clock me-1"></i>{{ $shift->toleransi_menit }} menit
                        </span>
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:#eff6ff;color:#1d4ed8;font-size:0.75rem">
                            {{ $shift->active_karyawans->count() }} karyawan
                        </span>
                        @if($shift->active_karyawans->count() > 0)
                        <div style="margin-top:4px">
                            @foreach($shift->active_karyawans as $karyawan)
                                <span class="badge" style="background:#f0fdf4;color:#166534;font-size:0.65rem;font-weight:500;margin:1px 1px 1px 0">
                                    {{ $karyawan->nama_lengkap ?? '-' }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.shift.edit', $shift) }}"
                               class="btn btn-sm" style="background:#f0fdf4;color:#16a34a;border-radius:6px;font-size:0.75rem">
                                <i class="bi bi-pencil-fill me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.shift.destroy', $shift) }}" method="POST"
                                  onsubmit="return confirm('Hapus shift {{ $shift->nama_shift }}? Karyawan yang menggunakan shift ini akan kehilangan penugasan shift.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm"
                                        style="background:#fef2f2;color:#dc2626;border-radius:6px;font-size:0.75rem">
                                    <i class="bi bi-trash-fill me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-clock" style="font-size:2.5rem;opacity:0.3"></i>
                        <div class="mt-2">Belum ada shift yang dibuat</div>
                        <a href="{{ route('admin.shift.create') }}" class="btn btn-sm btn-primary-custom mt-2">
                            Buat Shift Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
