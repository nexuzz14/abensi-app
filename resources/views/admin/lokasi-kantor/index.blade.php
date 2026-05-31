@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
    #map { height: 400px; border-radius: 12px; border: 1px solid var(--warm-border); z-index: 1; }
    .circle-custom { stroke: var(--amber); stroke-width: 2; fill: var(--amber); fill-opacity: 0.2; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h4 mb-1" style="color:var(--text-dark);font-weight:800;">Lokasi Kantor</h2>
        <p class="text-muted mb-0" style="font-size:0.875rem;">Atur titik koordinat GPS dan radius absensi karyawan</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="content-card">
            <div class="content-card-header">
                <div class="card-icon" style="background:#fef6e0;color:#7a5010;"><i class="bi bi-geo-alt-fill"></i></div>
                <h3 class="content-card-title">Form Pengaturan</h3>
            </div>
            <div class="content-card-body">
                <form action="{{ route('admin.lokasi-kantor.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi</label>
                        <input type="text" name="nama_lokasi" class="form-control @error('nama_lokasi') is-invalid @enderror" value="{{ old('nama_lokasi', $lokasi->nama_lokasi) }}" required>
                        @error('nama_lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" id="inputLat" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $lokasi->latitude) }}" required onchange="syncMapFromInput()">
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" id="inputLng" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $lokasi->longitude) }}" required onchange="syncMapFromInput()">
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Radius Absensi (Meter)</label>
                        <div class="input-group">
                            <input type="number" name="radius_meter" id="inputRadius" class="form-control @error('radius_meter') is-invalid @enderror" value="{{ old('radius_meter', $lokasi->radius_meter) }}" min="10" max="5000" required>
                            <span class="input-group-text">Meter</span>
                            @error('radius_meter')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Karyawan hanya bisa absen jika jaraknya kurang dari radius ini.</small>
                    </div>

                    <button type="button" class="btn btn-outline-primary w-100 mb-3" onclick="gunakanLokasiSaya()">
                        <i class="bi bi-crosshair me-2"></i> Gunakan Lokasi Saya (GPS)
                    </button>

                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-save-fill me-2"></i> Simpan Pengaturan
                    </button>
                </form>

                <div class="mt-3 p-3 rounded-3" style="background:#fffbeb;border:1px solid #fde68a;font-size:0.78rem;color:#92400e">
                    <strong><i class="bi bi-google me-1"></i> Cara ambil koordinat dari Google Maps:</strong>
                    <ol class="mb-0 mt-1 ps-3">
                        <li>Buka <a href="https://maps.google.com" target="_blank">Google Maps</a></li>
                        <li>Cari lokasi kantor</li>
                        <li>Klik kanan (atau tekan lama di HP) pada titik lokasi</li>
                        <li>Copy angka koordinat yang muncul (misal: -6.348123, 106.732456)</li>
                        <li>Paste ke kolom Latitude dan Longitude di atas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="content-card h-100">
            <div class="content-card-header">
                <div class="card-icon" style="background:#e8f0fe;color:#1e3a8a;"><i class="bi bi-map-fill"></i></div>
                <h3 class="content-card-title">Peta Interaktif</h3>
            </div>
            <div class="content-card-body">
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Cara Penggunaan:</strong>
                    <ul class="mb-0 mt-1" style="font-size:0.82rem">
                        <li>Klik lokasi di peta untuk mengubah titik koordinat kantor</li>
                        <li>Gunakan kotak pencarian di pojok kanan atas peta</li>
                        <li><strong>Tips:</strong> Cari berdasarkan <em>alamat jalan</em> (misal: "Jl. Raya Bogor No. 123"), bukan nama toko/bisnis</li>
                        <li>Ubah angka radius di form untuk membesarkan/mengecilkan area absen</li>
                    </ul>
                </div>
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
let currentLat, currentLng, currentRadius, map, marker, circle;

document.addEventListener('DOMContentLoaded', function() {
    currentLat = {{ $lokasi->latitude }};
    currentLng = {{ $lokasi->longitude }};
    currentRadius = {{ $lokasi->radius_meter }};

    map = L.map('map').setView([currentLat, currentLng], 17);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
        maxZoom: 20
    }).addTo(map);

    marker = L.marker([currentLat, currentLng]).addTo(map)
        .bindPopup('<b>{{ $lokasi->nama_lokasi }}</b>').openPopup();

    circle = L.circle([currentLat, currentLng], {
        color: '#b8742a',
        fillColor: '#b8742a',
        fillOpacity: 0.2,
        radius: currentRadius
    }).addTo(map);

    // Tambahkan Fitur Pencarian Alamat (Geocoder)
    const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: "Cari alamat jalan, misal: Jl. Raya Bogor...",
        errorMessage: "Alamat tidak ditemukan. Coba gunakan nama jalan.",
        suggestMinLength: 3,
    })
    .on('markgeocode', function(e) {
        var bbox = e.geocode.bbox;
        map.fitBounds(bbox);
        
        currentLat = e.geocode.center.lat;
        currentLng = e.geocode.center.lng;

        // Update Inputs
        document.getElementById('inputLat').value = currentLat.toFixed(8);
        document.getElementById('inputLng').value = currentLng.toFixed(8);

        // Update Marker & Circle
        marker.setLatLng(e.geocode.center);
        circle.setLatLng(e.geocode.center);
        marker.bindPopup('<b>' + e.geocode.name + '</b>').openPopup();
    })
    .addTo(map);

    // Update form when map is clicked
    map.on('click', function(e) {
        currentLat = e.latlng.lat;
        currentLng = e.latlng.lng;

        // Update Inputs
        document.getElementById('inputLat').value = currentLat.toFixed(8);
        document.getElementById('inputLng').value = currentLng.toFixed(8);

        // Update Marker & Circle
        marker.setLatLng(e.latlng);
        circle.setLatLng(e.latlng);
    });

    // Update circle when radius input changes
    document.getElementById('inputRadius').addEventListener('input', function(e) {
        let val = parseInt(e.target.value);
        if(!isNaN(val) && val > 0) {
            circle.setRadius(val);
        }
    });
});

// Gunakan GPS browser untuk menentukan lokasi
function gunakanLokasiSaya() {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung GPS.');
        return;
    }

    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-arrow-repeat me-2 spin"></i> Mencari lokasi...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            document.getElementById('inputLat').value = lat.toFixed(8);
            document.getElementById('inputLng').value = lng.toFixed(8);

            // Trigger map sync
            syncMapFromInput();

            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Lokasi ditemukan!';
            btn.classList.replace('btn-outline-primary', 'btn-outline-success');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('btn-outline-success', 'btn-outline-primary');
                btn.disabled = false;
            }, 2000);
        },
        function(err) {
            alert('Gagal mendapatkan lokasi GPS: ' + err.message + '\n\nPastikan izin lokasi sudah diaktifkan.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// Sync peta dari input manual (paste dari Google Maps)
function syncMapFromInput() {
    const lat = parseFloat(document.getElementById('inputLat').value);
    const lng = parseFloat(document.getElementById('inputLng').value);

    if (isNaN(lat) || isNaN(lng)) return;
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;

    currentLat = lat;
    currentLng = lng;
    marker.setLatLng([lat, lng]);
    circle.setLatLng([lat, lng]);
    map.setView([lat, lng], 17);
}
</script>
@endpush
