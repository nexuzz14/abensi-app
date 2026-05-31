@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">{{ $karyawan->nama_lengkap }}</h1>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-sm" style="background:#eff6ff;color:#2563eb;border-radius:8px;font-weight:600">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.karyawan.face-register', $karyawan) }}" class="btn btn-sm" style="background:#f0fdf4;color:#16a34a;border-radius:8px;font-weight:600">
            <i class="bi bi-person-bounding-box me-1"></i> Registrasi Wajah
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Info Karyawan --}}
    <div class="col-12 col-lg-4">
        <div class="content-card">
            <div class="content-card-body text-center p-4">
                @if($karyawan->foto)
                    <img src="{{ asset('storage/'.$karyawan->foto) }}"
                         class="rounded-circle mb-3"
                         style="width:100px;height:100px;object-fit:cover;border:3px solid #e2e8f0">
                @else
                    <div style="width:100px;height:100px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:800;color:white;margin:0 auto 1rem">
                        {{ strtoupper(substr($karyawan->nama_lengkap, 0, 1)) }}
                    </div>
                @endif
                <h2 style="font-size:1.1rem;font-weight:800;color:#1e293b;margin-bottom:0.25rem">{{ $karyawan->nama_lengkap }}</h2>
                <div style="color:#64748b;font-size:0.85rem">{{ $karyawan->jabatan }}</div>
                <div class="mt-2">
                    @if($karyawan->status_aktif)
                        <span style="background:#dcfce7;color:#166534;font-size:0.75rem;font-weight:600;padding:0.25rem 0.75rem;border-radius:100px">Aktif</span>
                    @else
                        <span style="background:#f1f5f9;color:#64748b;font-size:0.75rem;font-weight:600;padding:0.25rem 0.75rem;border-radius:100px">Nonaktif</span>
                    @endif
                </div>
            </div>
            <div style="border-top:1px solid #f1f5f9" class="p-4">
                <table style="width:100%;font-size:0.82rem">
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0;width:40%">NIP</td>
                        <td><code style="background:#f1f5f9;padding:0.15rem 0.5rem;border-radius:4px">{{ $karyawan->nip }}</code></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Email</td>
                        <td>{{ $karyawan->user->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.4rem 0">Wajah</td>
                        <td>
                            @if($karyawan->hasFaceDescriptor())
                                <span style="color:#10b981;font-size:0.75rem;font-weight:600"><i class="bi bi-check-circle-fill me-1"></i>Terdaftar</span>
                            @else
                                <span style="color:#ef4444;font-size:0.75rem;font-weight:600"><i class="bi bi-x-circle-fill me-1"></i>Belum</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi --}}
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-calendar3 text-primary me-2"></i>Riwayat Absensi (30 Hari)</h2>
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
                            <th>GPS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawan->absensi as $absen)
                        <tr>
                            <td style="font-size:0.82rem;font-weight:600">{{ $absen->tanggal->translatedFormat('D, d M Y') }}</td>
                            <td>{{ $absen->jam_masuk ? substr($absen->jam_masuk,0,5) : '—' }}</td>
                            <td>{{ $absen->jam_keluar ? substr($absen->jam_keluar,0,5) : '—' }}</td>
                            <td style="font-size:0.78rem;color:#64748b">{{ $absen->durasi_kerja_format }}</td>
                            <td><span class="status-badge badge-{{ $absen->status_kehadiran }}">{{ ucfirst($absen->status_kehadiran) }}</span></td>
                            <td>
                                @if($absen->status_fake_gps === 'clean')
                                    <span style="color:#10b981;font-size:0.75rem"><i class="bi bi-check-circle-fill"></i></span>
                                @elseif($absen->status_fake_gps === 'suspicious')
                                    <span style="color:#f59e0b;font-size:0.75rem"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                @else
                                    <span style="color:#cbd5e1">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
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
