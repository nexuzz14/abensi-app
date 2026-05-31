@extends('layouts.admin')

@section('title', 'Edit Shift')
@section('page-title', 'Edit Shift')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.shift.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">Edit Shift: {{ $shift->nama_shift }}</h1>
</div>

<div class="row">
    <div class="col-12 col-lg-6">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-clock-fill text-warning me-2"></i>Edit Data Shift</h2>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.shift.update', $shift) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                        <label class="form-label">Nama Shift *</label>
                        <input type="text" name="nama_shift"
                               class="form-control @error('nama_shift') is-invalid @enderror"
                               value="{{ old('nama_shift', $shift->nama_shift) }}">
                        @error('nama_shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jam Masuk *</label>
                            <input type="time" name="jam_masuk"
                                   class="form-control @error('jam_masuk') is-invalid @enderror"
                                   value="{{ old('jam_masuk', substr($shift->jam_masuk, 0, 5)) }}">
                            @error('jam_masuk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Keluar *</label>
                            <input type="time" name="jam_keluar"
                                   class="form-control @error('jam_keluar') is-invalid @enderror"
                                   value="{{ old('jam_keluar', substr($shift->jam_keluar, 0, 5)) }}">
                            @error('jam_keluar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Toleransi Keterlambatan *</label>
                        <div class="input-group">
                            <input type="number" name="toleransi_menit"
                                   class="form-control @error('toleransi_menit') is-invalid @enderror"
                                   value="{{ old('toleransi_menit', $shift->toleransi_menit) }}"
                                   min="0" max="120">
                            <span class="input-group-text text-muted">menit</span>
                            @error('toleransi_menit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning fw-bold px-4" style="color:#92400e">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.shift.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
