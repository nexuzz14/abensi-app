<?php

namespace App\Exports;

use App\Services\AbsensiService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

/**
 * Export Absensi ke file Excel
 * Menggunakan Maatwebsite/Excel package
 */
class AbsensiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private array $filter,
        private AbsensiService $absensiService
    ) {}

    /**
     * Ambil data absensi dari database sesuai filter
     */
    public function collection()
    {
        $absensi = $this->absensiService->getDataLaporan($this->filter);

        // Format data untuk Excel
        return $absensi->map(function ($item, $index) {
            return [
                'No'              => $index + 1,
                'NIP'             => $item->karyawan->nip ?? '-',
                'Nama Karyawan'   => $item->karyawan->nama_lengkap ?? '-',
                'Jabatan'         => $item->karyawan->jabatan ?? '-',
                'Tanggal'         => $item->tanggal ? $item->tanggal->format('d/m/Y') : '-',
                'Jam Masuk'       => $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : '-',
                'Jam Keluar'      => $item->jam_keluar ? substr($item->jam_keluar, 0, 5) : '-',
                'Durasi Kerja'    => $item->durasi_kerja_format,
                'Status'          => ucfirst($item->status_kehadiran),
                'Status Liveness' => ucfirst($item->status_liveness),
                'Status GPS'      => ucfirst($item->status_fake_gps),
                'Koordinat Masuk' => $item->lat_masuk
                    ? "{$item->lat_masuk}, {$item->lng_masuk}"
                    : '-',
                'Keterangan'      => $item->keterangan ?? '-',
            ];
        });
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Karyawan',
            'Jabatan',
            'Tanggal',
            'Jam Masuk',
            'Jam Keluar',
            'Durasi Kerja',
            'Status Kehadiran',
            'Status Liveness',
            'Status GPS',
            'Koordinat Masuk',
            'Keterangan',
        ];
    }

    /**
     * Styling untuk worksheet Excel
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Baris header (baris 1) diberi warna biru gelap dengan teks putih
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Nama sheet pada file Excel
     */
    public function title(): string
    {
        return 'Laporan Absensi';
    }
}
