import re

path = r'C:\Users\user\.gemini\antigravity\brain\20e7fc91-b668-42b1-b0e1-337b754ea3ed\uml_updated.md'
with open(path, 'r', encoding='utf-8') as f:
    text = f.read()

replacements = {
    r'@startuml\ntitle Sequence Diagram - Login Admin.*?@enduml': '''@startuml
title Sequence Diagram - Login Admin

autonumber
actor Admin
boundary "Browser"
control "AuthController" as Auth
control "Laravel Auth" as LaravelAuth
entity "Database"
entity "Session"

activate Admin
Admin -> Browser: Akses halaman /login
activate Browser
Browser -> Auth: GET /login
activate Auth
Auth -> Browser: Tampilkan form login
deactivate Auth

Admin -> Browser: Isi email & password, klik Submit
Browser -> Auth: POST /login
activate Auth
Auth -> LaravelAuth: Auth::attempt(credentials)
activate LaravelAuth
LaravelAuth -> Database: Cek email & password di tabel users
activate Database
Database -> LaravelAuth: Return user dengan role=admin
deactivate Database
LaravelAuth -> Session: Generate session & CSRF token
activate Session
Session -> Auth: Redirect /admin/dashboard
deactivate Session
deactivate LaravelAuth

Auth -> Browser: Tampilkan Admin Dashboard
deactivate Auth
Browser -> Admin: Visualisasi data ringkasan admin
deactivate Browser
deactivate Admin
@enduml''',

    r'@startuml\ntitle Sequence Diagram - Clock-In dengan Face Recognition & Liveness.*?@enduml': '''@startuml
title Sequence Diagram - Clock-In dengan Face Recognition & Liveness

autonumber
actor Karyawan
boundary "Browser"
control "Laravel Server" as Laravel
control "face-api.js" as FaceAPI
boundary "Webcam"
entity "Database"

activate Karyawan
Karyawan -> Browser: Buka halaman Clock-In
activate Browser
Browser -> Laravel: GET /absensi/clock-in
activate Laravel
Laravel -> Browser: Return View + CSRF Token
deactivate Laravel

Browser -> Laravel: GET /api/face-descriptors
activate Laravel
Laravel -> Database: Query face_descriptor karyawan aktif
activate Database
Database -> Laravel: Return data descriptor
deactivate Database
Laravel -> Browser: Return JSON descriptors
deactivate Laravel

Browser -> FaceAPI: Load AI Models
activate FaceAPI
FaceAPI -> Webcam: Request akses kamera
activate Webcam
Webcam -> FaceAPI: Stream video feed
deactivate Webcam
FaceAPI -> Browser: Deteksi wajah & tampilkan kotak hijau
deactivate FaceAPI

Browser -> Karyawan: Instruksi "Kedip mata 2x"
Karyawan -> Webcam: Berkedip 2x
activate Webcam
Webcam -> FaceAPI: Send feed
activate FaceAPI
FaceAPI -> Browser: EAR turun, Liveness Passed
deactivate FaceAPI
deactivate Webcam

Browser -> Karyawan: Tombol Check-In aktif
Karyawan -> Browser: Klik Check-In
Browser -> Laravel: POST /absensi/clock-in (foto, lat, lng, id)
activate Laravel
Laravel -> Database: INSERT INTO absensi
activate Database
Database -> Laravel: Success
deactivate Database
Laravel -> Browser: HTTP 200 JSON success
deactivate Laravel
Browser -> Karyawan: Notifikasi berhasil & redirect Dashboard
deactivate Browser
deactivate Karyawan
@enduml''',

    r'@startuml\ntitle Sequence Diagram - Validasi GPS & Geofencing.*?@enduml': '''@startuml
title Sequence Diagram - Validasi GPS & Geofencing

autonumber
boundary "Browser"
control "AbsensiController" as Controller
control "AbsensiService" as AbsensiSvc
control "FakeGpsService" as GpsSvc
entity "Database"

activate Browser
Browser -> Controller: POST payload (lat, lng, accuracy)
activate Controller
Controller -> AbsensiSvc: prosesClockIn()
activate AbsensiSvc
AbsensiSvc -> GpsSvc: validasi(karyawan, lat, lng, accuracy)
activate GpsSvc
GpsSvc -> GpsSvc: Cek akurasi sinyal satelit
GpsSvc -> AbsensiSvc: Return status aman/tidak aman
deactivate GpsSvc

AbsensiSvc -> Database: Query tabel lokasi_kantors
activate Database
Database -> AbsensiSvc: Return koordinat kantor
deactivate Database

AbsensiSvc -> GpsSvc: hitungJarak(latKantor, lngKantor, latKaryawan, lngKaryawan)
activate GpsSvc
GpsSvc -> AbsensiSvc: Return jarak dalam meter (Haversine)
deactivate GpsSvc

AbsensiSvc -> AbsensiSvc: Cek jarak > radius?
AbsensiSvc -> Controller: Return error jika di luar radius
deactivate AbsensiSvc

Controller -> Browser: Batalkan absen & kirim pesan error
deactivate Controller
deactivate Browser
@enduml''',

    r'@startuml\ntitle Sequence Diagram - Approval Cuti oleh Admin.*?@enduml': '''@startuml
title Sequence Diagram - Approval Cuti oleh Admin

autonumber
actor Admin
boundary "Browser"
control "CutiController" as Controller
entity "Database"

activate Admin
Admin -> Browser: Klik tombol Approve pada tabel cuti
activate Browser
Browser -> Controller: PATCH /admin/cuti/{id} {status: approved}
activate Controller
Controller -> Database: UPDATE cuti SET status=approved WHERE id={id}
activate Database
Database -> Controller: OK berhasil update
deactivate Database

Controller -> Browser: Redirect back + flash message success
Browser -> Controller: GET /admin/cuti (muat ulang halaman)
Controller -> Browser: Return HTML baru
deactivate Controller
Browser -> Admin: Tampilkan badge hijau Approved
deactivate Browser
deactivate Admin
@enduml''',

    r'@startuml\ntitle Sequence Diagram - Generate Alpha Otomatis\n\nactor "Cron / Task Scheduler" as Cron.*?@enduml': '''@startuml
title Sequence Diagram - Generate Alpha Otomatis

autonumber
actor "Cron / Task Scheduler" as Cron
control "routes/console.php" as Kernel
control "GenerateAlpha Command" as Command
entity "Database"

activate Cron
Cron -> Kernel: Execute schedule run (Setiap menit)
activate Kernel
Kernel -> Command: Call absensi:generate-alpha (Jika jam 23:55)
activate Command
Command -> Database: Cek KalenderLibur (Apakah hari libur?)
activate Database
Database -> Command: Return false (tidak libur)
deactivate Database

Command -> Database: SELECT * FROM karyawan WHERE status_aktif = true
activate Database
Database -> Command: Return Collection Karyawan
deactivate Database

loop Untuk Setiap Karyawan
  Command -> Database: Cek tabel Absensi hari ini
  activate Database
  Database -> Command: Return null (belum absen)
  deactivate Database
  
  Command -> Database: Cek tabel Cuti hari ini (status=approved)
  activate Database
  Database -> Command: Return null (tidak cuti)
  deactivate Database
  
  Command -> Database: INSERT INTO absensi (karyawan_id, tanggal, status_kehadiran = 'alpa')
  activate Database
  Database -> Command: OK
  deactivate Database
end

Command -> Database: Insert System Log Record
activate Database
Database -> Command: OK
deactivate Database

Command -> Kernel: Command finished (SUCCESS)
deactivate Command
Kernel -> Cron: Execution Complete
deactivate Kernel
deactivate Cron
@enduml''',

    r'@startuml\ntitle Sequence Diagram - Edit Status Absensi \(Admin\).*?@enduml': '''@startuml
title Sequence Diagram - Edit Status Absensi (Admin)

autonumber
actor Admin
boundary "Browser"
control "LaporanController" as Controller
entity "Database"

activate Admin
Admin -> Browser: Klik ikon Edit Status
activate Browser
Browser -> Admin: Tampilkan Modal Edit

Admin -> Browser: Ubah pilihan status & klik Simpan
Browser -> Controller: PUT /laporan/absensi/{id}/status
activate Controller
Controller -> Controller: Validate request ('hadir','alpa', dll)
Controller -> Database: UPDATE absensi SET status_kehadiran = {status} WHERE id = {id}
activate Database
Database -> Controller: OK
deactivate Database

Controller -> Browser: Redirect back() with success
deactivate Controller
Browser -> Admin: Tampilkan halaman Laporan ter-update
deactivate Browser
deactivate Admin
@enduml'''
}

for pattern, repl in replacements.items():
    text, count = re.subn(pattern, repl, text, flags=re.DOTALL)
    print(f"Replaced {count} occurrences of a pattern.")

with open(path, 'w', encoding='utf-8') as f:
    f.write(text)
