@extends('layouts.admin')

@section('title', 'Registrasi Wajah — ' . $karyawan->nama_lengkap)
@section('page-title', 'Registrasi Wajah')

@push('styles')
<style>
    /* ========================
       FACE REGISTER STYLES
    ======================== */
    .face-container {
        background: #0f172a;
        border-radius: 16px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .webcam-wrapper {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }

    #videoEl {
        width: 100%;
        border-radius: 12px;
        display: block;
        transform: scaleX(-1); /* Mirror effect */
        background: #000;
    }

    #canvasOverlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        transform: scaleX(-1);
        border-radius: 12px;
    }

    /* Face guide circle */
    .face-guide {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 200px;
        height: 250px;
        border: 3px solid rgba(255,255,255,0.25);
        border-radius: 50% / 60%;
        pointer-events: none;
        animation: pulse-guide 2s ease-in-out infinite;
    }

    .face-guide.detected {
        border-color: #10b981;
        box-shadow: 0 0 20px rgba(16,185,129,0.4);
        animation: none;
    }

    @keyframes pulse-guide {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.7; }
    }

    /* Status indicator */
    .detection-status {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.375rem 1rem;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
        backdrop-filter: blur(8px);
    }

    .detection-status.success { background: rgba(16,185,129,0.8); }
    .detection-status.warning { background: rgba(245,158,11,0.8); }
    .detection-status.danger  { background: rgba(239,68,68,0.8); }

    /* Capture steps */
    .capture-steps {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.375rem;
        min-width: 70px;
    }

    .step-circle {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .step-circle.capturing {
        border-color: #f59e0b;
        box-shadow: 0 0 15px rgba(245,158,11,0.5);
        animation: pulse-step 0.5s ease infinite;
    }

    .step-circle.done {
        border-color: #10b981;
        background: rgba(16,185,129,0.15);
    }

    .step-circle.done i { color: #10b981; }

    @keyframes pulse-step {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .step-circle i {
        color: rgba(255,255,255,0.4);
        font-size: 1.25rem;
    }

    .step-label {
        color: rgba(255,255,255,0.5);
        font-size: 0.65rem;
        font-weight: 600;
        text-align: center;
    }

    .step-item.active .step-label {
        color: #f59e0b;
    }

    .step-item.done .step-label {
        color: #10b981;
    }

    /* Progress bar */
    .progress-custom {
        height: 6px;
        background: rgba(255,255,255,0.1);
        border-radius: 100px;
        overflow: hidden;
    }

    .progress-custom .progress-bar {
        background: linear-gradient(90deg, #2563eb, #10b981);
        border-radius: 100px;
        transition: width 0.5s ease;
    }

    /* Capture button */
    .btn-capture {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        border: none;
        border-radius: 100px;
        color: white;
        font-weight: 700;
        padding: 0.875rem 2.5rem;
        font-size: 0.95rem;
        transition: all 0.2s;
        position: relative;
    }

    .btn-capture:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(37,99,235,0.4);
        color: white;
    }

    .btn-capture:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .btn-capture.ready {
        animation: pulse-btn 1.5s ease-in-out infinite;
    }

    @keyframes pulse-btn {
        0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); }
        50% { box-shadow: 0 0 0 10px rgba(37,99,235,0); }
    }

    /* Log area */
    #captureLog {
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        max-height: 120px;
        overflow-y: auto;
        font-size: 0.75rem;
        font-family: monospace;
        color: rgba(255,255,255,0.6);
    }

    #captureLog .log-item {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding: 0.2rem 0;
    }

    #captureLog .log-success { color: #10b981; }
    #captureLog .log-info    { color: #3b82f6; }
    #captureLog .log-warn    { color: #f59e0b; }
    #captureLog .log-error   { color: #ef4444; }

    /* Result card */
    .result-card {
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.3);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        display: none;
    }

    .result-card.show { display: block; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center mb-4 gap-3">
    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div>
        <h1 class="mb-0" style="font-size:1.2rem;font-weight:800;color:#1e293b">
            Registrasi Wajah: {{ $karyawan->nama_lengkap }}
        </h1>
        <p class="text-muted mb-0" style="font-size:0.8rem">
            NIP: {{ $karyawan->nip }} — {{ $karyawan->jabatan }}
        </p>
    </div>
</div>

@if($sudahTerdaftar)
<div class="alert alert-info d-flex align-items-center gap-3 mb-4"
     style="border-radius:12px;border-left:4px solid #3b82f6">
    <i class="bi bi-check-circle-fill text-info fs-4"></i>
    <div>
        <strong>Wajah sudah terdaftar.</strong>
        Karyawan ini sudah memiliki data wajah. Anda bisa daftarkan ulang untuk memperbarui.
    </div>
</div>
@endif

<div class="row g-4">

    {{-- WEBCAM & CAPTURE --}}
    <div class="col-12 col-lg-7">
        <div class="face-container">
            {{-- Webcam area --}}
            <div class="webcam-wrapper mb-4">
                <video id="videoEl" autoplay muted playsinline></video>
                <canvas id="canvasOverlay"></canvas>
                <div class="face-guide" id="faceGuide"></div>
                <div class="detection-status" id="statusBadge">
                    <i class="bi bi-camera me-1"></i> Memuat kamera...
                </div>
            </div>

            {{-- Progress --}}
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="color:rgba(255,255,255,0.6);font-size:0.75rem;font-weight:600">
                        Progress Capture
                    </span>
                    <span style="color:rgba(255,255,255,0.8);font-size:0.75rem;font-weight:700" id="progressText">
                        0 / 5 sudut
                    </span>
                </div>
                <div class="progress-custom">
                    <div class="progress-bar" id="progressBar" style="width:0%"></div>
                </div>
            </div>

            {{-- Capture Steps (5 sudut wajah) --}}
            <div class="capture-steps mb-4">
                @php
                    $steps = [
                        ['id' => 'step-depan',  'icon' => 'bi-dot',           'label' => 'Depan'],
                        ['id' => 'step-kiri',   'icon' => 'bi-arrow-left',    'label' => 'Kiri'],
                        ['id' => 'step-kanan',  'icon' => 'bi-arrow-right',   'label' => 'Kanan'],
                        ['id' => 'step-atas',   'icon' => 'bi-arrow-up',      'label' => 'Atas'],
                        ['id' => 'step-bawah',  'icon' => 'bi-arrow-down',    'label' => 'Bawah'],
                    ];
                @endphp

                @foreach($steps as $index => $step)
                <div class="step-item" id="{{ $step['id'] }}-item">
                    <div class="step-circle" id="{{ $step['id'] }}-circle">
                        <i class="bi {{ $step['icon'] }}"></i>
                    </div>
                    <span class="step-label">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Instruksi --}}
            <div class="text-center mb-3">
                <p id="instruksiText"
                   style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0">
                    Posisikan wajah di dalam area oval, lalu klik "Mulai Capture"
                </p>
            </div>

            {{-- Tombol Capture --}}
            <div class="text-center mb-3">
                <button id="btnCapture" class="btn-capture" disabled>
                    <i class="bi bi-camera-fill me-2"></i>
                    <span id="btnCaptureText">Memuat Model AI...</span>
                </button>
            </div>

            {{-- Log --}}
            <div id="captureLog">
                <div class="log-item log-info">⏳ Memuat model face-api.js... harap tunggu</div>
            </div>
        </div>
    </div>

    {{-- PANDUAN & HASIL --}}
    <div class="col-12 col-lg-5">
        {{-- Panduan --}}
        <div class="content-card mb-4">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="bi bi-question-circle text-info me-2"></i> Panduan Registrasi
                </h2>
            </div>
            <div class="content-card-body">
                <ol style="font-size:0.85rem;color:#475569;padding-left:1.25rem">
                    <li class="mb-2">Pastikan pencahayaan <strong>cukup terang</strong></li>
                    <li class="mb-2">Hadapkan wajah ke kamera, lalu klik <strong>"Mulai Capture"</strong></li>
                    <li class="mb-2">Sistem akan meminta <strong>5 sudut wajah</strong> secara berurutan</li>
                    <li class="mb-2">Ikuti instruksi yang muncul (depan, kiri, kanan, atas, bawah)</li>
                    <li class="mb-2">Jaga agar wajah tetap <strong>di dalam oval</strong></li>
                    <li>Setelah 5 sudut selesai, klik <strong>"Simpan Wajah"</strong></li>
                </ol>

                <div class="alert alert-warning py-2 mb-0" style="border-radius:8px;font-size:0.8rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Data wajah disimpan terenkripsi dan tidak bisa diakses dari luar sistem.
                </div>
            </div>
        </div>

        {{-- Hasil Registrasi --}}
        <div class="result-card" id="resultCard">
            <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
            <h3 style="color:white;font-size:1.1rem;font-weight:700;margin:0.75rem 0 0.5rem">
                Wajah Berhasil Didaftarkan!
            </h3>
            <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;margin-bottom:1rem">
                Data wajah <strong>{{ $karyawan->nama_lengkap }}</strong> telah tersimpan.
                Karyawan sekarang bisa melakukan absensi menggunakan face recognition.
            </p>
            <a href="{{ route('admin.karyawan.index') }}" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Karyawan
            </a>
        </div>

        {{-- Info Karyawan --}}
        <div class="content-card">
            <div class="content-card-header">
                <h2 class="content-card-title">
                    <i class="bi bi-person-fill text-primary me-2"></i> Info Karyawan
                </h2>
            </div>
            <div class="content-card-body">
                <table style="font-size:0.85rem;width:100%">
                    <tr>
                        <td style="color:#94a3b8;width:40%;padding:0.3rem 0">Nama</td>
                        <td style="font-weight:600;color:#1e293b">{{ $karyawan->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.3rem 0">NIP</td>
                        <td><code>{{ $karyawan->nip }}</code></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;padding:0.3rem 0">Jabatan</td>
                        <td>{{ $karyawan->jabatan }}</td>
                    </tr>

                    <tr>
                        <td style="color:#94a3b8;padding:0.3rem 0">Status Wajah</td>
                        <td>
                            @if($sudahTerdaftar)
                                <span class="status-badge badge-hadir">Terdaftar</span>
                            @else
                                <span class="status-badge badge-alpa">Belum Terdaftar</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{--
    face-api.js CDN
    Model files harus ada di public/models/
    Download dari: https://github.com/justadudewhohacks/face-api.js/tree/master/weights
--}}
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    // ================================================================
    // FACE REGISTRATION SCRIPT
    // Capture 5 sudut wajah, hitung rata-rata descriptor, simpan ke DB
    // ================================================================

    const MODEL_URL   = '/models';
    const KARYAWAN_ID = {{ $karyawan->id }};
    const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const STORE_URL   = '{{ route('admin.karyawan.face-descriptor.store', [$karyawan->id], false) }}';

    // Nama sudut wajah yang harus dicapture (urutan)
    const SUDUT = ['depan', 'kiri', 'kanan', 'atas', 'bawah'];
    const INSTRUKSI = {
        depan: '😊 Hadapkan wajah lurus ke depan',
        kiri:  '👈 Miringkan sedikit ke kiri (15°)',
        kanan: '👉 Miringkan sedikit ke kanan (15°)',
        atas:  '☝️  Angkat wajah sedikit ke atas (15°)',
        bawah: '👇 Tundukkan sedikit ke bawah (15°)',
    };

    // State aplikasi
    let video          = document.getElementById('videoEl');
    let canvas         = document.getElementById('canvasOverlay');
    let statusBadge    = document.getElementById('statusBadge');
    let faceGuide      = document.getElementById('faceGuide');
    let instruksi      = document.getElementById('instruksiText');
    let btnCapture     = document.getElementById('btnCapture');
    let btnCaptureText = document.getElementById('btnCaptureText');

    let descriptors      = [];   // Array of Float32Array descriptors dari 5 sudut
    let currentSudutIdx  = 0;    // Index sudut yang sedang dicapture
    let isCameraReady    = false;
    let isFaceDetected   = false;
    let isCapturing      = false;
    let detectionLoop    = null;

    // ========================
    // LOGGING HELPER
    // ========================
    function addLog(msg, type = 'info') {
        const log  = document.getElementById('captureLog');
        const item = document.createElement('div');
        item.className = `log-item log-${type}`;
        item.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
        log.appendChild(item);
        log.scrollTop = log.scrollHeight;
    }

    // ========================
    // INIT: LOAD MODELS
    // ========================
    async function loadModels() {
        addLog('Memuat model Tiny Face Detector...', 'info');
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        addLog('Memuat model Face Landmark 68...', 'info');
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        addLog('Memuat model Face Recognition...', 'info');
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        addLog('✅ Semua model berhasil dimuat!', 'success');
    }

    // ========================
    // INIT: START WEBCAM
    // ========================
    async function startCamera() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error("Browser memblokir akses kamera (harus HTTPS atau localhost)");
            }
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: 'user' }
            });
            video.srcObject = stream;
            await new Promise(resolve => video.onloadedmetadata = resolve);
            video.play();

            // Sesuaikan ukuran canvas dengan video
            canvas.width  = video.videoWidth;
            canvas.height = video.videoHeight;

            isCameraReady = true;
            addLog('✅ Kamera berhasil diaktifkan', 'success');

            // Aktifkan tombol capture
            btnCapture.disabled = false;
            btnCapture.classList.add('ready');
            btnCaptureText.textContent = 'Mulai Capture';

            // Mulai loop deteksi real-time
            startDetectionLoop();
        } catch (err) {
            addLog(`❌ Gagal mengakses kamera: ${err.message}`, 'error');
            statusBadge.textContent = '❌ Kamera tidak dapat diakses';
            statusBadge.className = 'detection-status danger';
        }
    }

    // ========================
    // DETECTION LOOP
    // Jalankan setiap 300ms untuk deteksi wajah real-time
    // ========================
    function startDetectionLoop() {
        detectionLoop = setInterval(async () => {
            if (!isCameraReady || !video.readyState === 4) return;

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    isFaceDetected = true;
                    faceGuide.classList.add('detected');

                    // Gambar bounding box di canvas
                    const dims = faceapi.matchDimensions(canvas, video, true);
                    const resized = faceapi.resizeResults(detection, dims);

                    // Gambar box (custom color)
                    const { x, y, width, height } = resized.detection.box;
                    ctx.strokeStyle = '#10b981';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(x, y, width, height);

                    // Label
                    ctx.fillStyle = 'rgba(16,185,129,0.8)';
                    ctx.fillRect(x, y - 22, 80, 22);
                    ctx.fillStyle = 'white';
                    ctx.font = '12px Inter';
                    ctx.fillText('Wajah OK', x + 5, y - 6);

                    statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> Wajah terdeteksi';
                    statusBadge.className = 'detection-status success';
                } else {
                    isFaceDetected = false;
                    faceGuide.classList.remove('detected');
                    statusBadge.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Wajah tidak terdeteksi';
                    statusBadge.className = 'detection-status warning';
                }
            } catch (err) {
                // Abaikan error deteksi individual
            }
        }, 300);
    }

    // ========================
    // CAPTURE HANDLER
    // ========================
    btnCapture.addEventListener('click', async function() {
        if (isCapturing) return;

        // Cek apakah semua sudut sudah dicapture
        if (currentSudutIdx >= SUDUT.length) {
            await simpanDescriptor();
            return;
        }

        // Cek apakah wajah terdeteksi
        if (!isFaceDetected) {
            addLog('⚠️  Wajah tidak terdeteksi. Pastikan wajah dalam frame.', 'warn');
            return;
        }

        isCapturing = true;
        const sudut = SUDUT[currentSudutIdx];
        const stepItem   = document.getElementById(`step-${sudut}-item`);
        const stepCircle = document.getElementById(`step-${sudut}-circle`);

        // Visual: step sedang di-capture
        stepCircle.classList.add('capturing');
        stepItem.classList.add('active');
        addLog(`📸 Mengambil gambar sudut: ${sudut.toUpperCase()}...`, 'info');

        // Countdown 1.5 detik sebelum capture (beri waktu untuk berpose)
        btnCaptureText.textContent = '3...';
        await sleep(500);
        btnCaptureText.textContent = '2...';
        await sleep(500);
        btnCaptureText.textContent = '1...';
        await sleep(500);

        try {
            // Deteksi final untuk mengambil descriptor
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                addLog(`❌ Wajah tidak terdeteksi saat capture ${sudut}. Coba lagi.`, 'error');
                stepCircle.classList.remove('capturing');
                stepItem.classList.remove('active');
                isCapturing = false;
                btnCaptureText.textContent = `Capture ${sudut.charAt(0).toUpperCase() + sudut.slice(1)}`;
                return;
            }

            // Simpan descriptor untuk sudut ini
            descriptors.push(Array.from(detection.descriptor));

            // Update UI: step selesai
            stepCircle.classList.remove('capturing');
            stepCircle.classList.add('done');
            stepCircle.innerHTML = '<i class="bi bi-check-lg"></i>';
            stepItem.classList.remove('active');
            stepItem.classList.add('done');

            addLog(`✅ Sudut ${sudut.toUpperCase()} berhasil di-capture!`, 'success');

            currentSudutIdx++;

            // Update progress bar
            const progress = (currentSudutIdx / SUDUT.length) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;
            document.getElementById('progressText').textContent = `${currentSudutIdx} / ${SUDUT.length} sudut`;

            // Instruksi berikutnya
            if (currentSudutIdx < SUDUT.length) {
                const nextSudut = SUDUT[currentSudutIdx];
                instruksi.textContent = INSTRUKSI[nextSudut];
                btnCaptureText.textContent = `Capture ${nextSudut.charAt(0).toUpperCase() + nextSudut.slice(1)}`;

                // Highlight step berikutnya
                const nextStepCircle = document.getElementById(`step-${nextSudut}-circle`);
                nextStepCircle.style.borderColor = 'rgba(245,158,11,0.5)';
            } else {
                // Semua sudut selesai
                instruksi.textContent = '🎉 Semua sudut berhasil! Klik "Simpan Wajah" untuk menyimpan.';
                btnCaptureText.textContent = '💾 Simpan Wajah ke Database';
                btnCapture.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                addLog('🎉 Semua 5 sudut wajah berhasil di-capture! Siap untuk disimpan.', 'success');
            }
        } catch (err) {
            addLog(`❌ Error saat capture: ${err.message}`, 'error');
        }

        isCapturing = false;
    });

    // ========================
    // SIMPAN DESCRIPTOR KE DB
    // ========================
    async function simpanDescriptor() {
        if (descriptors.length < 5) {
            addLog('⚠️  Belum semua sudut selesai di-capture.', 'warn');
            return;
        }

        addLog('💾 Menghitung rata-rata descriptor wajah...', 'info');

        // Hitung rata-rata dari 5 descriptor (128 nilai masing-masing)
        const avgDescriptor = [];
        for (let i = 0; i < 128; i++) {
            let sum = 0;
            for (let j = 0; j < descriptors.length; j++) {
                sum += descriptors[j][i];
            }
            avgDescriptor.push(sum / descriptors.length);
        }

        addLog('📡 Mengirim data ke server...', 'info');

        btnCapture.disabled = true;
        btnCaptureText.textContent = 'Menyimpan...';

        try {
            const response = await fetch(STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ descriptor: avgDescriptor }),
            });

            const data = await response.json();

            if (data.success) {
                addLog('✅ ' + data.message, 'success');

                // Tampilkan result card
                document.getElementById('resultCard').style.display = 'block';
                document.getElementById('resultCard').classList.add('show');

                // Hentikan kamera
                clearInterval(detectionLoop);
                btnCapture.style.display = 'none';

                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Wajah Tersimpan!';
                statusBadge.className = 'detection-status success';
            } else {
                addLog('❌ ' + data.message, 'error');
                btnCapture.disabled = false;
                btnCaptureText.textContent = '💾 Coba Simpan Lagi';
            }
        } catch (err) {
            addLog('❌ Gagal menghubungi server: ' + err.message, 'error');
            btnCapture.disabled = false;
            btnCaptureText.textContent = '💾 Coba Simpan Lagi';
        }
    }

    // ========================
    // HELPER
    // ========================
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ========================
    // MAIN INIT
    // ========================
    (async function init() {
        try {
            await loadModels();
            await startCamera();
            instruksi.textContent = INSTRUKSI['depan'];
        } catch (err) {
            addLog('❌ Gagal inisialisasi: ' + err.message, 'error');
        }
    })();
</script>
@endpush
