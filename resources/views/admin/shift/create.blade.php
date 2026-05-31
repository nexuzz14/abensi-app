@extends('layouts.admin')

@section('title', 'Tambah Shift')
@section('page-title', 'Tambah Shift Baru')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.shift.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">Tambah Shift Baru</h1>
</div>

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-clock-fill text-primary me-2"></i>Data Shift</h2>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.shift.store') }}" method="POST">
                    @csrf

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="nama_shift">Nama Shift *</label>
                        <input type="text" id="nama_shift" name="nama_shift"
                               class="form-control @error('nama_shift') is-invalid @enderror"
                               value="{{ old('nama_shift') }}"
                               placeholder="Contoh: Shift Pagi">
                        @error('nama_shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="jam_masuk">Jam Masuk *</label>
                            <input type="time" id="jam_masuk" name="jam_masuk"
                                   class="form-control @error('jam_masuk') is-invalid @enderror"
                                   value="{{ old('jam_masuk', '08:00') }}">
                            @error('jam_masuk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="jam_keluar">Jam Keluar *</label>
                            <input type="time" id="jam_keluar" name="jam_keluar"
                                   class="form-control @error('jam_keluar') is-invalid @enderror"
                                   value="{{ old('jam_keluar', '17:00') }}">
                            @error('jam_keluar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="toleransi_menit">
                            Toleransi Keterlambatan * <span class="text-muted small">(0–120 menit)</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="toleransi_menit" name="toleransi_menit"
                                   class="form-control @error('toleransi_menit') is-invalid @enderror"
                                   value="{{ old('toleransi_menit', 15) }}"
                                   min="0" max="120">
                            <span class="input-group-text text-muted">menit</span>
                            @error('toleransi_menit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Karyawan yang masuk setelah (jam masuk + toleransi) akan ditandai Terlambat.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="bi bi-plus-circle me-1"></i> Simpan Shift
                        </button>
                        <a href="{{ route('admin.shift.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
