# Sistem Absensi Karyawan Terintegrasi AI

Sistem informasi absensi karyawan modern yang dibangun menggunakan framework Laravel 11. Aplikasi ini didesain secara khusus untuk mencegah manipulasi absensi dengan mengimplementasikan teknologi verifikasi wajah cerdas (*Face Recognition* dengan *Liveness Detection*) dan pelacakan lokasi akurat (*Geofencing*).

## ✨ Fitur Utama

- **Face Recognition & Liveness Detection:** Menggunakan teknologi TensorFlow.js (`face-api.js`), sistem memvalidasi kecocokan wajah karyawan secara *real-time* dari *browser*, lengkap dengan deteksi kedipan mata (*liveness*) untuk menghindari pemalsuan menggunakan foto cetak.
- **Geofencing & Anti-Fake GPS:** Karyawan diwajibkan untuk berada di dalam radius kantor yang telah ditentukan (perhitungan *Haversine Formula*). Sistem dapat mendeteksi manipulasi lokasi / GPS palsu.
- **Manajemen Karyawan & NIP Otomatis:** Pendataan profil karyawan beserta pemisahan shift kerja. NIP (Nomor Induk Pegawai) akan di-*generate* secara otomatis oleh sistem (Auto Increment).
- **Pengajuan Cuti:** Karyawan dapat mengajukan izin/cuti dengan mengunggah surat keterangan dokter pendukung (khusus format `.pdf`, `.png`, `.jpg`).
- **Perekaman Otomatis (Cron Job):** Karyawan yang absen/mangkir tanpa keterangan hingga pukul 23:55 akan otomatis ditandai sebagai "Alpha" oleh sistem.
- **Laporan Komprehensif:** Laporan absensi bulanan yang detail (Hadir, Telat, Cuti, Izin, Sakit, Alpha), bisa disaring, serta di-export langsung ke format **PDF** maupun **Excel**.

## 🛠️ Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Bootstrap 5, Vanilla CSS, Vanilla JS
- **AI/Machine Learning:** `face-api.js` (Face Detection, Face Landmark, Face Recognition)
- **Database:** MySQL
- **Dependencies Lain:** `Leaflet.js` (Pemetaan Geofencing), `Maatwebsite/Laravel-Excel`, `barryvdh/laravel-dompdf`.

## 🚀 Cara Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/nexuzz14/abensi-app.git
   cd abensi-app
   ```

2. **Install Composer Dependencies**
   ```bash
   composer install
   ```

3. **Install NPM Packages (Opsional)**
   ```bash
   npm install
   npm run build
   ```

4. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`.
   ```bash
   cp .env.example .env
   ```
   Atur kredensial *database* Anda pada file `.env`:
   ```env
   DB_DATABASE=absensi
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate App Key & Setup Database**
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```

6. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```
   Buka `http://localhost:8000` di *browser* Anda. Gunakan kredensial *default* berikut untuk login:
   - **Admin:** `admin@admin.com` | Password: `password`
   - **Karyawan:** Gunakan NIP yang tertera di menu kelola karyawan.

## 📄 Lisensi
Hak Cipta dilindungi. Sistem ini dikembangkan untuk tujuan akademis dan demonstrasi tertutup.
