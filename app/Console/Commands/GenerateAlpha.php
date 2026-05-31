<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\KalenderLibur;
use App\Models\Karyawan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateAlpha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:generate-alpha {--date= : Tanggal spesifik YYYY-MM-DD (default: hari ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis membuat record absensi dengan status ALPA untuk karyawan yang tidak hadir dan tidak cuti/libur.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateStr = $this->option('date');
        $tanggal = $dateStr ? Carbon::parse($dateStr) : Carbon::today();
        
        $this->info("Menjalankan pengecekan Alpha untuk tanggal: " . $tanggal->format('Y-m-d'));

        // Cek apakah hari ini adalah hari libur nasional
        if (KalenderLibur::isHariLibur($tanggal)) {
            $this->info("Hari ini ({$tanggal->format('Y-m-d')}) adalah hari libur. Tidak ada Alpha yang di-generate.");
            return Command::SUCCESS;
        }

        // Cek apakah hari ini adalah weekend (Sabtu/Minggu)? 
        // Tergantung kebijakan perusahaan. Asumsikan hari Senin-Jumat saja atau semua hari.
        // Untuk absensi shift, bisa jadi weekend tetap masuk. Kita biarkan saja.

        $karyawans = Karyawan::where('status_aktif', true)->get();
        $count = 0;

        foreach ($karyawans as $karyawan) {
            // Cek apakah sudah ada absen hari ini
            $sudahAbsen = Absensi::where('karyawan_id', $karyawan->id)
                ->whereDate('tanggal', $tanggal)
                ->exists();

            if ($sudahAbsen) {
                continue;
            }

            // Cek apakah ada cuti yang disetujui hari ini
            $sedangCuti = Cuti::where('karyawan_id', $karyawan->id)
                ->where('status', 'approved')
                ->whereDate('tanggal_mulai', '<=', $tanggal)
                ->whereDate('tanggal_selesai', '>=', $tanggal)
                ->exists();

            if ($sedangCuti) {
                continue;
            }

            // Jika tidak absen dan tidak cuti, maka ALPA
            Absensi::create([
                'karyawan_id'      => $karyawan->id,
                'tanggal'          => $tanggal->format('Y-m-d'),
                'status_kehadiran' => 'alpa',
            ]);

            $count++;
            $this->line("- Meng-generate Alpha untuk NIP: {$karyawan->nip} - {$karyawan->nama_lengkap}");
        }

        Log::info("Command absensi:generate-alpha selesai dijalankan.", [
            'tanggal' => $tanggal->format('Y-m-d'),
            'total_alpha_dibuat' => $count
        ]);

        $this->info("Selesai! Berhasil men-generate {$count} record Alpha.");
        return Command::SUCCESS;
    }
}
