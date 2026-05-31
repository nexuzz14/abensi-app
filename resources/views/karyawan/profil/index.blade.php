@extends('layouts.karyawan')

@section('title', 'Profil Saya')

@section('content')
<h1 class="mb-4" style="font-size:1.3rem;font-weight:800;color:#1e293b">Profil Saya</h1>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius:12px;border:none;background:#ecfdf5;color:#065f46">
    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

<div class="row g-4">
    {{-- INFO PROFIL --}}
    <div class="col-12 col-lg-5">
        <div class="card-custom">
            <div class="card-custom-header">
                <div class="card-icon" style="background:#eff6ff"><i class="bi bi-person-fill text-primary"></i></div>
                <h2 class="card-title-custom">Informasi Karyawan</h2>
            </div>
            <div class="p-4">
                <form action="{{ route('karyawan.profil.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Avatar --}}
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            @if($karyawan && $karyawan->foto)
                                <img src="{{ asset('storage/'.$karyawan->foto) }}"
                                     class="rounded-circle" style="width:90px;height:90px;object-fit:cover;border:3px solid #e2e8f0" id="fotoPreview">
                            @else
                                <div style="width:90px;height:90px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:white;margin:0 auto" id="fotoPreviewPlaceholder">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <img src="" class="rounded-circle d-none" style="width:90px;height:90px;object-fit:cover;border:3px solid #e2e8f0" id="fotoPreview">
                            @endif
                            <label for="fotoInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center cursor-pointer" style="width:28px;height:28px;cursor:pointer;border:2px solid white">
                                <i class="bi bi-camera-fill" style="font-size:0.75rem"></i>
                            </label>
                            <input type="file" name="foto" id="fotoInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <div style="font-size:1rem;font-weight:700;color:#1e293b;margin-top:0.75rem">{{ $user->name }}</div>
                        <div style="font-size:0.8rem;color:#64748b">{{ $user->email }}</div>
                    </div>

                {{-- Detail --}}
                <table style="width:100%;font-size:0.85rem">
                    @if($karyawan)
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">NIP</label>
                        <div><code style="background:#f1f5f9;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.85rem">{{ $karyawan->nip }}</code></div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-sm @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $karyawan->nama_lengkap) }}">
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">Jabatan</label>
                        <div style="font-size:0.9rem;font-weight:600;color:#1e293b">{{ $karyawan->jabatan }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">No. HP</label>
                        <input type="text" class="form-control form-control-sm @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp', $karyawan->no_hp) }}">
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">Status</label>
                            <div>
                            @if($karyawan->status_aktif)
                                <span style="background:#dcfce7;color:#166534;font-size:0.75rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px">Aktif</span>
                            @else
                                <span style="background:#f1f5f9;color:#64748b;font-size:0.75rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px">Nonaktif</span>
                            @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:0.85rem;color:#64748b;margin-bottom:0.2rem">Wajah</label>
                            <div>
                                @if($karyawan->hasFaceDescriptor())
                                    <span style="background:#dcfce7;color:#166534;font-size:0.75rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px">
                                        <i class="bi bi-check-circle me-1"></i> Terdaftar
                                    </span>
                                @else
                                    <span style="background:#fee2e2;color:#991b1b;font-size:0.75rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px">
                                        <i class="bi bi-x-circle me-1"></i> Belum
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 mb-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- UBAH PASSWORD --}}
    <div class="col-12 col-lg-7">
        <div class="card-custom">
            <div class="card-custom-header">
                <div class="card-icon" style="background:#fef9c3"><i class="bi bi-shield-lock-fill text-warning"></i></div>
                <h2 class="card-title-custom">Ubah Password</h2>
            </div>
            <div class="p-4">
                <form action="{{ route('karyawan.profil.ubah-password') }}" method="POST" id="passwordForm">
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
                        <label class="form-label" for="password_lama">Password Lama *</label>
                        <div class="input-group">
                            <input type="password" id="password_lama" name="password_lama"
                                   class="form-control @error('password_lama') is-invalid @enderror"
                                   placeholder="Masukkan password lama Anda">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_lama','icon1')">
                                <i class="bi bi-eye" id="icon1"></i>
                            </button>
                            @error('password_lama')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_baru">Password Baru * <span class="text-muted small">(min. 8 karakter)</span></label>
                        <div class="input-group">
                            <input type="password" id="password_baru" name="password_baru"
                                   class="form-control @error('password_baru') is-invalid @enderror"
                                   placeholder="Masukkan password baru"
                                   minlength="8"
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_baru','icon2')">
                                <i class="bi bi-eye" id="icon2"></i>
                            </button>
                            @error('password_baru')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Password strength --}}
                        <div class="mt-2" id="strengthBar" style="display:none">
                            <div style="height:4px;background:#e2e8f0;border-radius:4px;overflow:hidden">
                                <div id="strengthFill" style="height:100%;border-radius:4px;transition:all 0.3s;width:0%"></div>
                            </div>
                            <div id="strengthText" style="font-size:0.72rem;margin-top:4px;color:#64748b"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password_baru_confirmation">Konfirmasi Password Baru *</label>
                        <div class="input-group">
                            <input type="password" id="password_baru_confirmation" name="password_baru_confirmation"
                                   class="form-control @error('password_baru_confirmation') is-invalid @enderror"
                                   placeholder="Ulangi password baru">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('password_baru_confirmation','icon3')">
                                <i class="bi bi-eye" id="icon3"></i>
                            </button>
                            @error('password_baru_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning fw-bold" style="border-radius:10px;color:#92400e">
                        <i class="bi bi-shield-check me-2"></i> Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-3 p-3 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div style="font-size:0.78rem;font-weight:700;color:#166534;margin-bottom:0.4rem">
                <i class="bi bi-shield-check me-1"></i> Tips Keamanan Password
            </div>
            <ul style="font-size:0.75rem;color:#166534;padding-left:1.25rem;margin:0">
                <li>Minimal 8 karakter</li>
                <li>Kombinasikan huruf besar, kecil, angka, dan simbol</li>
                <li>Jangan gunakan tanggal lahir atau nama sendiri</li>
                <li>Ganti password secara berkala (3 bulan sekali)</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type     = 'password';
        icon.className = 'bi bi-eye';
    }
}

function checkStrength(value) {
    const bar    = document.getElementById('strengthBar');
    const fill   = document.getElementById('strengthFill');
    const text   = document.getElementById('strengthText');

    if (!value) { bar.style.display = 'none'; return; }
    bar.style.display = 'block';

    let score = 0;
    if (value.length >= 8)  score++;
    if (/[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    const levels = [
        { pct: '25%', color: '#ef4444', label: 'Lemah' },
        { pct: '50%', color: '#f59e0b', label: 'Cukup' },
        { pct: '75%', color: '#3b82f6', label: 'Kuat' },
        { pct: '100%', color: '#10b981', label: 'Sangat Kuat' },
    ];
    const level     = levels[score - 1] || levels[0];
    fill.style.width      = level.pct;
    fill.style.background = level.color;
    text.textContent      = `Kekuatan: ${level.label}`;
    text.style.color      = level.color;
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('fotoPreview');
            const placeholder = document.getElementById('fotoPreviewPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if(placeholder) placeholder.classList.add('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
