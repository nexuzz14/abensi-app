@extends('layouts.karyawan')

@section('title', 'Ajukan Cuti')

@section('content')
<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('karyawan.cuti.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">Ajukan Cuti Baru</h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">Isi form berikut untuk mengajukan cuti</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card-custom">
            <div class="card-custom-header">
                <div class="card-icon" style="background:#eff6ff"><i class="bi bi-journal-plus text-primary"></i></div>
                <h2 class="card-title-custom">Form Pengajuan Cuti</h2>
            </div>
            <div class="p-4">
                <form action="{{ route('karyawan.cuti.store') }}" method="POST" enctype="multipart/form-data" id="cutiForm">
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

                    {{-- Jenis Cuti --}}
                    <div class="mb-3">
                        <label class="form-label" for="jenis_cuti">Jenis Cuti *</label>
                        <select id="jenis_cuti" name="jenis_cuti"
                                class="form-select @error('jenis_cuti') is-invalid @enderror">
                            <option value="">— Pilih Jenis Cuti —</option>
                            <option value="tahunan"    {{ old('jenis_cuti') == 'tahunan' ? 'selected' : '' }}>Cuti Tahunan</option>
                            <option value="sakit"      {{ old('jenis_cuti') == 'sakit' ? 'selected' : '' }}>Cuti Sakit</option>
                            <option value="izin"       {{ old('jenis_cuti') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="melahirkan" {{ old('jenis_cuti') == 'melahirkan' ? 'selected' : '' }}>Cuti Melahirkan</option>
                            <option value="darurat"    {{ old('jenis_cuti') == 'darurat' ? 'selected' : '' }}>Darurat / Keluarga</option>
                        </select>
                        @error('jenis_cuti')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="tanggal_mulai">Tanggal Mulai *</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                                   class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   value="{{ old('tanggal_mulai') }}"
                                   min="{{ today()->toDateString() }}"
                                   onchange="hitungHari()">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="tanggal_selesai">Tanggal Selesai *</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                                   class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   value="{{ old('tanggal_selesai') }}"
                                   min="{{ today()->toDateString() }}"
                                   onchange="hitungHari()">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Indikator jumlah hari --}}
                    <div id="jumlahHariInfo" class="mb-3" style="display:none">
                        <div class="p-3 rounded-3" style="background:#eff6ff;border:1px solid #bfdbfe">
                            <strong style="color:#1e40af" id="jumlahHariText"></strong>
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div class="mb-3">
                        <label class="form-label" for="alasan">Alasan Cuti * <span class="text-muted small">(min. 10 karakter)</span></label>
                        <textarea id="alasan" name="alasan" rows="4"
                                  class="form-control @error('alasan') is-invalid @enderror"
                                  placeholder="Jelaskan alasan pengajuan cuti Anda..."
                                  minlength="10" maxlength="1000">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Upload Surat --}}
                    <div class="mb-4">
                        <label class="form-label" for="file_surat">
                            Surat Pendukung <span class="text-muted small">(opsional, maks. 5MB)</span>
                        </label>
                        <input type="file" id="file_surat" name="file_surat"
                               class="form-control @error('file_surat') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png"
                               onchange="validateFileCuti(this)">
                        <div class="form-text">Format: PDF, JPG, PNG. Contoh: surat dokter untuk cuti sakit.</div>
                        @error('file_surat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <script>
                        function validateFileCuti(input) {
                            if (input.files.length > 0) {
                                const file = input.files[0];
                                const ext = file.name.split('.').pop().toLowerCase();
                                const allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                                if (!allowed.includes(ext)) {
                                    alert('Error: File berformat .' + ext + ' tidak diizinkan. Harap upload format PDF, JPG, atau PNG.');
                                    input.value = ''; // Reset input
                                }
                            }
                        }
                    </script>

                    <button type="submit" class="btn btn-primary fw-bold w-100 py-3" id="submitBtn" style="border-radius:12px">
                        <i class="bi bi-send-fill me-2"></i> Kirim Pengajuan Cuti
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card-custom">
            <div class="card-custom-header">
                <div class="card-icon" style="background:#fef9c3"><i class="bi bi-info-circle text-warning"></i></div>
                <h2 class="card-title-custom">Ketentuan Pengajuan</h2>
            </div>
            <div class="p-4">
                <ul style="font-size:0.85rem;color:#475569;padding-left:1.25rem;line-height:1.8">
                    <li>Pengajuan diproses oleh <strong>Admin</strong> dalam 1–2 hari kerja</li>
                    <li>Tidak dapat mengajukan cuti untuk tanggal yang <strong>sudah lewat</strong></li>
                    <li>Tidak dapat mengajukan cuti yang <strong>bertabrakan</strong> dengan cuti yang sudah diapprove</li>
                    <li>Alasan minimal <strong>10 karakter</strong></li>
                    <li>Sertakan surat dokter untuk <strong>cuti sakit</strong></li>
                    <li>Hari Sabtu, Minggu, dan hari libur nasional <strong>tidak dihitung</strong></li>
                </ul>
                <div class="alert alert-warning py-2 mb-0 mt-3" style="border-radius:8px;font-size:0.78rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Pengajuan yang sudah dikirim <strong>tidak dapat dibatalkan</strong> sendiri. Hubungi admin jika ada perubahan.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function hitungHari() {
    const mulai   = document.getElementById('tanggal_mulai').value;
    const selesai = document.getElementById('tanggal_selesai').value;
    const info    = document.getElementById('jumlahHariInfo');
    const text    = document.getElementById('jumlahHariText');

    if (!mulai || !selesai) { info.style.display = 'none'; return; }

    const tgl1 = new Date(mulai);
    const tgl2 = new Date(selesai);

    if (tgl2 < tgl1) {
        info.style.display = 'block';
        text.style.color   = '#dc2626';
        text.textContent   = '⚠️ Tanggal selesai tidak boleh sebelum tanggal mulai';
        return;
    }

    // Hitung hari kerja (skip Sabtu=6, Minggu=0)
    let hariKerja = 0;
    const current = new Date(tgl1);
    while (current <= tgl2) {
        const hari = current.getDay();
        if (hari !== 0 && hari !== 6) hariKerja++;
        current.setDate(current.getDate() + 1);
    }

    info.style.display = 'block';
    text.style.color   = '#1e40af';
    text.innerHTML     = `<i class="bi bi-calendar-check me-2"></i>Durasi cuti: <strong>${hariKerja} hari kerja</strong>`;
}
</script>
@endpush
