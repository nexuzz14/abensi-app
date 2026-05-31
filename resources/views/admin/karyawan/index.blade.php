@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('page-title', 'Manajemen Karyawan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-0" style="font-size:1.3rem;font-weight:800;color:#1e293b">Data Karyawan</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">Kelola semua data karyawan dalam sistem</p>
    </div>
    <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
    </a>
</div>

{{-- Filter & Search --}}
<div class="content-card mb-4">
    <div class="content-card-body">
        <form method="GET" action="{{ route('admin.karyawan.index') }}" class="row g-2">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text border-0 bg-light">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0 bg-light ps-0"
                           placeholder="Cari nama, NIP, jabatan..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom flex-1">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Karyawan --}}
<div class="content-card">
    <div class="content-card-header">
        <h2 class="content-card-title">
            <i class="bi bi-people text-primary me-2"></i>
            Daftar Karyawan
        </h2>
        <span class="badge bg-secondary rounded-pill">{{ $karyawan->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Karyawan</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>No. HP</th>
                    <th>Wajah</th>
                    <th>Status</th>
                    <th style="width:180px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawan as $index => $k)
                <tr>
                    <td class="text-muted small">{{ $karyawan->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            {{-- Avatar --}}
                            @if($k->foto)
                                <img src="{{ asset('storage/'.$k->foto) }}"
                                     class="rounded-circle"
                                     style="width:36px;height:36px;object-fit:cover">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:white;font-weight:700;font-size:0.85rem">
                                    {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600;font-size:0.85rem;color:#1e293b">
                                    {{ $k->nama_lengkap }}
                                </div>
                                <div style="font-size:0.72rem;color:#94a3b8">
                                    {{ $k->nip }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <code style="font-size:0.78rem;background:#f1f5f9;padding:0.2rem 0.4rem;border-radius:4px">
                            {{ $k->nip }}
                        </code>
                    </td>
                    <td style="font-size:0.85rem">{{ $k->jabatan }}</td>
                    <td style="font-size:0.85rem">{{ $k->no_hp ?? '-' }}</td>
                    <td>
                        @if($k->hasFaceDescriptor())
                            <span class="status-badge badge-hadir">
                                <i class="bi bi-check-circle-fill me-1"></i> Terdaftar
                            </span>
                        @else
                            <span class="status-badge badge-alpa">
                                <i class="bi bi-x-circle-fill me-1"></i> Belum
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($k->status_aktif)
                            <span class="status-badge badge-hadir">Aktif</span>
                        @else
                            <span class="status-badge" style="background:#f1f5f9;color:#64748b">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            {{-- Registrasi Wajah --}}
                            <a href="{{ route('admin.karyawan.face-register', $k) }}"
                               class="btn btn-sm"
                               style="background:#eff6ff;color:#2563eb;font-size:0.7rem;border-radius:6px"
                               title="{{ $k->hasFaceDescriptor() ? 'Daftarkan Ulang Wajah' : 'Daftarkan Wajah' }}">
                                <i class="bi bi-person-bounding-box"></i>
                            </a>

                            @if($k->hasFaceDescriptor())
                                <form action="{{ route('admin.karyawan.face-descriptor.reset', $k) }}" method="POST"
                                      onsubmit="return confirm('Apakah Anda yakin ingin me-reset (menghapus) data wajah {{ $k->nama_lengkap }}? Karyawan harus mendaftar ulang untuk bisa absen.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                            style="background:#fff7ed;color:#ea580c;font-size:0.7rem;border-radius:6px"
                                            title="Reset Wajah">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- Edit --}}
                            <a href="{{ route('admin.karyawan.edit', $k) }}"
                               class="btn btn-sm"
                               style="background:#f0fdf4;color:#16a34a;font-size:0.7rem;border-radius:6px"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            {{-- Nonaktifkan / Aktifkan --}}
                            @if($k->status_aktif)
                                <form action="{{ route('admin.karyawan.destroy', $k) }}" method="POST"
                                      onsubmit="return confirm('Nonaktifkan karyawan {{ $k->nama_lengkap }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm"
                                            style="background:#fef2f2;color:#dc2626;font-size:0.7rem;border-radius:6px"
                                            title="Nonaktifkan">
                                        <i class="bi bi-person-x-fill"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.karyawan.aktifkan', $k) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm"
                                            style="background:#f0fdf4;color:#16a34a;font-size:0.7rem;border-radius:6px"
                                            title="Aktifkan Kembali">
                                        <i class="bi bi-person-check-fill"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-people" style="font-size:2.5rem;opacity:0.3"></i>
                        <div class="mt-2">Tidak ada karyawan yang ditemukan</div>
                        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-sm btn-primary-custom mt-2">
                            Tambah Karyawan Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($karyawan->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Menampilkan {{ $karyawan->firstItem() }}-{{ $karyawan->lastItem() }} dari {{ $karyawan->total() }} karyawan
        </div>
        {{ $karyawan->links() }}
    </div>
    @endif
</div>

@endsection
