@extends('layouts.admin')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan Baru')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">Tambah Karyawan Baru</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">Lengkapi semua data berikut untuk mendaftarkan karyawan</p>
    </div>
</div>

<form action="{{ route('admin.karyawan.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
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
    <div class="row g-4">

        {{-- KOLOM KIRI: Data Diri --}}
        <div class="col-12 col-lg-8">
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title">
                        <i class="bi bi-person-fill text-primary me-2"></i> Data Diri Karyawan
                    </h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nip">NIP (Nomor Induk Karyawan) *</label>
                            <input type="text" id="nip" name="nip"
                                   class="form-control"
                                   value="{{ $nip_baru }}"
                                   readonly
                                   style="background-color: #f1f5f9; cursor: not-allowed; color: #475569; font-weight: 600;">
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="nama_lengkap">Nama Lengkap *</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap"
                                   class="form-control @error('nama_lengkap') is-invalid @enderror"
                                   value="{{ old('nama_lengkap') }}"
                                   placeholder="Nama sesuai identitas">
                            @error('nama_lengkap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="jabatan">Jabatan *</label>
                            <input type="text" id="jabatan" name="jabatan"
                                   class="form-control @error('jabatan') is-invalid @enderror"
                                   value="{{ old('jabatan') }}"
                                   placeholder="Contoh: Staff IT">
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="no_hp">No. HP / WhatsApp *</label>
                            <input type="text" id="no_hp" name="no_hp"
                                   class="form-control @error('no_hp') is-invalid @enderror"
                                   value="{{ old('no_hp') }}"
                                   placeholder="Contoh: 081234567890"
                                   maxlength="20">
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Akun Login --}}
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <h2 class="content-card-title">
                        <i class="bi bi-shield-lock-fill text-warning me-2"></i> Akun Login
                    </h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info py-2 px-3" style="font-size:0.8rem">
                                <i class="bi bi-info-circle me-1"></i>
                                Email login akan di-generate otomatis dari NIP: <strong><nip>@internal.app</strong>
                                <br>Karyawan login menggunakan <strong>NIP</strong>, bukan email.
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password">Password *</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 karakter"
                                       minlength="8">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password *</label>
                            <div class="input-group">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       placeholder="Ulangi password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation', this)">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Penugasan Shift --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h2 class="content-card-title">
                        <i class="bi bi-clock-fill text-success me-2"></i> Penugasan Shift
                    </h2>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="shift_id">Shift Kerja</label>
                            <select id="shift_id" name="shift_id" class="form-select @error('shift_id') is-invalid @enderror">
                                <option value="">— Pilih Shift —</option>
                                @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->nama_shift }}
                                    ({{ $shift->jam_masuk_format }} - {{ $shift->jam_keluar_format }})
                                </option>
                                @endforeach
                            </select>
                            @error('shift_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="tanggal_berlaku">Tanggal Berlaku</label>
                            <input type="date" id="tanggal_berlaku" name="tanggal_berlaku"
                                   class="form-control @error('tanggal_berlaku') is-invalid @enderror"
                                   value="{{ old('tanggal_berlaku', today()->toDateString()) }}">
                            @error('tanggal_berlaku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Foto --}}
        <div class="col-12 col-lg-4">
            <div class="content-card">
                <div class="content-card-header">
                    <h2 class="content-card-title">
                        <i class="bi bi-image-fill text-info me-2"></i> Foto Profil
                    </h2>
                </div>
                <div class="content-card-body text-center">
                    <div id="fotoPreview"
                         style="width:120px;height:120px;border-radius:50%;margin:0 auto 1rem;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;border:3px dashed #e2e8f0;cursor:pointer"
                         onclick="document.getElementById('foto').click()">
                        <div id="fotoPlaceholder" style="text-align:center;color:#94a3b8">
                            <i class="bi bi-person" style="font-size:2.5rem"></i>
                            <div style="font-size:0.7rem;margin-top:0.25rem">Klik untuk upload</div>
                        </div>
                        <img id="fotoImg" src="" style="width:100%;height:100%;object-fit:cover;display:none" alt="Preview">
                    </div>

                    <input type="file" id="foto" name="foto" accept="image/*" class="d-none"
                           onchange="previewFoto(this)">

                    <div class="text-muted small">
                        JPG, PNG, JPEG<br>Maks. 2MB
                    </div>

                    @error('foto')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Info Box --}}
            <div class="mt-3 p-3 rounded-3"
                 style="background:#eff6ff;border:1px solid #bfdbfe">
                <div style="font-size:0.78rem;font-weight:700;color:#1e40af;margin-bottom:0.5rem">
                    <i class="bi bi-info-circle me-1"></i> Informasi
                </div>
                <ul style="font-size:0.75rem;color:#1e40af;padding-left:1.25rem;margin:0">
                    <li>Foto wajah untuk absensi akan diregistrasikan terpisah</li>
                    <li>Setelah simpan, klik tombol "Daftarkan Wajah" di halaman daftar karyawan</li>
                    <li>Password karyawan minimal 8 karakter</li>
                </ul>
            </div>

            {{-- Submit --}}
            <div class="mt-3 d-grid">
                <button type="submit" class="btn btn-primary-custom py-3">
                    <i class="bi bi-person-plus-fill me-2"></i> Simpan Karyawan
                </button>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    // Preview foto sebelum upload
    function previewFoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('fotoImg').src = e.target.result;
                document.getElementById('fotoImg').style.display = 'block';
                document.getElementById('fotoPlaceholder').style.display = 'none';
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
