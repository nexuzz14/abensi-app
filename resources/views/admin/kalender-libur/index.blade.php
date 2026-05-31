@extends('layouts.admin')

@section('title', 'Kalender Hari Libur')
@section('page-title', 'Kalender Hari Libur')

@section('content')
<div class="row g-4">
    {{-- Form Tambah Libur --}}
    <div class="col-12 col-lg-4">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Hari Libur</h2>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.kalender-libur.store') }}" method="POST">
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

                    {{-- Tipe: Single / Range --}}
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipeSingle" value="single" checked
                                       onchange="toggleTipe('single')">
                                <label class="form-check-label" for="tipeSingle">Satu Hari</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipeRange" value="range"
                                       onchange="toggleTipe('range')">
                                <label class="form-check-label" for="tipeRange">Rentang Tanggal</label>
                            </div>
                        </div>
                    </div>

                    {{-- Single Date --}}
                    <div id="fieldSingle" class="mb-3">
                        <label class="form-label" for="tanggal">Tanggal *</label>
                        <input type="date" id="tanggal" name="tanggal"
                               class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal') }}">
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Range Dates --}}
                    <div id="fieldRange" style="display:none">
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai *</label>
                            <input type="date" name="tanggal_mulai"
                                   class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                   value="{{ old('tanggal_mulai') }}">
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Selesai *</label>
                            <input type="date" name="tanggal_selesai"
                                   class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                   value="{{ old('tanggal_selesai') }}">
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="keterangan">Keterangan *</label>
                        <input type="text" id="keterangan" name="keterangan"
                               class="form-control @error('keterangan') is-invalid @enderror"
                               value="{{ old('keterangan') }}"
                               placeholder="Contoh: Hari Raya Idul Fitri">
                        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Jenis *</label>
                        <select name="jenis" class="form-select @error('jenis') is-invalid @enderror">
                            <option value="nasional" {{ old('jenis') == 'nasional' ? 'selected' : '' }}>Libur Nasional</option>
                            <option value="bersama" {{ old('jenis') == 'bersama' ? 'selected' : '' }}>Cuti Bersama</option>
                        </select>
                        @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Hari Libur
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Hari Libur --}}
    <div class="col-12 col-lg-8">
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title"><i class="bi bi-calendar-x text-danger me-2"></i>Daftar Hari Libur</h2>
                <span class="badge bg-danger rounded-pill">{{ $libur->count() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($libur as $bulan => $items)
                            <tr>
                                <td colspan="5" style="background:var(--warm-white);font-weight:700;color:var(--text-dark)">
                                    {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}
                                </td>
                            </tr>
                            @foreach($items as $l)
                            <tr>
                                <td>
                                    <span style="font-weight:700;color:#1e293b">
                                        {{ \Carbon\Carbon::parse($l->tanggal)->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td style="font-size:0.8rem;color:#64748b">
                                    {{ \Carbon\Carbon::parse($l->tanggal)->translatedFormat('l') }}
                                </td>
                                <td style="font-size:0.85rem">{{ $l->keterangan }}</td>
                                <td>
                                    @if($l->jenis === 'nasional')
                                        <span class="badge rounded-pill" style="background:#fee2e2;color:#991b1b;font-size:0.72rem">Nasional</span>
                                    @else
                                        <span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;font-size:0.72rem">Cuti Bersama</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.kalender-libur.destroy', $l) }}" method="POST"
                                          onsubmit="return confirm('Hapus hari libur ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm"
                                                style="background:#fef2f2;color:#dc2626;border-radius:6px;font-size:0.72rem">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:0.3"></i>
                                <div class="mt-2 small">Belum ada hari libur yang ditambahkan</div>
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

@push('scripts')
<script>
function toggleTipe(tipe) {
    document.getElementById('fieldSingle').style.display = tipe === 'single' ? 'block' : 'none';
    document.getElementById('fieldRange').style.display  = tipe === 'range'  ? 'block' : 'none';

    // Sesuaikan required attribute
    document.getElementById('tanggal').required = tipe === 'single';
}
</script>
@endpush
