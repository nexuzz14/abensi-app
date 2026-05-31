@extends('layouts.admin')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Data Karyawan')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">Edit: {{ $karyawan->nama_lengkap }}</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">NIP: {{ $karyawan->nip }}</p>
    </div>
</div>

<form action="{{ route('admin.karyawan.update', $karyawan) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title"><i class="bi bi-person-fill text-primary me-2"></i>Data Diri</h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIP *</label>
                            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                                   value="{{ old('nip', $karyawan->nip) }}" maxlength="20">
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror"
                                   value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}">
                            @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan *</label>
                            <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror"
                                   value="{{ old('jabatan', $karyawan->jabatan) }}">
                            @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP / WhatsApp *</label>
                            <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                                   value="{{ old('no_hp', $karyawan->no_hp) }}" maxlength="20"
                                   placeholder="081234567890">
                            @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status_aktif" class="form-select">
                                <option value="1" {{ old('status_aktif', $karyawan->status_aktif) ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ !old('status_aktif', $karyawan->status_aktif) ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Akun Login</h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info py-2 px-3" style="font-size:0.8rem">
                                <i class="bi bi-info-circle me-1"></i>
                                Login karyawan menggunakan <strong>NIP</strong>, bukan email.
                                <br>Email sistem: <code>{{ strtolower($karyawan->nip) }}@internal.app</code>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password Baru <span class="text-muted small">(kosongkan jika tidak diubah)</span></label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title"><i class="bi bi-clock-fill text-success me-2"></i>Penugasan Shift</h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Shift Kerja</label>
                            <select name="shift_id" class="form-select">
                                <option value="">— Tanpa Shift —</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', optional($shiftAktif)->id) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->nama_shift }} ({{ $shift->jam_masuk_format }} - {{ $shift->jam_keluar_format }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" name="tanggal_berlaku" class="form-control"
                                   value="{{ old('tanggal_berlaku', today()->toDateString()) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="content-card-header">
                    <h2 class="content-card-title"><i class="bi bi-person-bounding-box text-danger me-2"></i>Data Wajah</h2>
                </div>
                <div class="content-card-body">
                    @if($karyawan->hasFaceDescriptor())
                    <div class="alert alert-success py-2 mb-3 small"><i class="bi bi-check-circle me-1"></i>Wajah sudah terdaftar.</div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="reset_face" name="reset_face" value="1">
                        <label class="form-check-label text-danger fw-bold" for="reset_face">
                            Reset data wajah (daftar ulang diperlukan)
                        </label>
                        <div class="form-text text-danger">Karyawan tidak bisa absensi sampai wajah didaftarkan ulang.</div>
                    </div>
                    @else
                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>Wajah belum terdaftar.
                        <a href="{{ route('admin.karyawan.face-register', $karyawan) }}" class="alert-link">Daftarkan sekarang</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title"><i class="bi bi-image-fill text-info me-2"></i>Foto Profil</h2>
                </div>
                <div class="content-card-body text-center">
                    <div style="width:120px;height:120px;border-radius:50%;margin:0 auto 1rem;overflow:hidden;border:3px solid #e2e8f0;cursor:pointer;background:#f1f5f9"
                         onclick="document.getElementById('foto').click()">
                        @if($karyawan->foto)
                            <img src="{{ asset('storage/'.$karyawan->foto) }}" id="fotoImg" style="width:100%;height:100%;object-fit:cover" alt="Foto">
                        @else
                            <div id="fotoPlaceholder" style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94a3b8">
                                <i class="bi bi-person" style="font-size:2.5rem"></i>
                                <div style="font-size:0.7rem">Klik upload</div>
                            </div>
                            <img id="fotoImg" src="" style="width:100%;height:100%;object-fit:cover;display:none" alt="">
                        @endif
                    </div>
                    <input type="file" id="foto" name="foto" accept="image/*" class="d-none" onchange="previewFoto(this)">
                    <div class="text-muted small">JPG, PNG — Maks. 2MB</div>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary-custom py-3 fw-bold">
                    <i class="bi bi-save-fill me-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let img = document.getElementById('fotoImg');
            img.src = e.target.result;
            img.style.display = 'block';
            const ph = document.getElementById('fotoPlaceholder');
            if (ph) ph.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>
@endpush
