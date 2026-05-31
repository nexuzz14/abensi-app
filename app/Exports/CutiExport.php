<?php

namespace App\Exports;

use App\Models\Cuti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CutiExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return Cuti::with('karyawan')
            ->when($this->filters['status'] ?? null, fn($q, $s) => $q->where('status', $s))
            ->when($this->filters['bulan'] ?? null, fn($q, $b) => $q->whereMonth('tanggal_mulai', $b))
            ->orderBy('tanggal_mulai', 'desc')
            ->get()
            ->map(function ($cuti, $index) {
                return [
                    'No'              => $index + 1,
                    'NIP'             => $cuti->karyawan->nip ?? '-',
                    'Nama Karyawan'   => $cuti->karyawan->nama_lengkap ?? '-',
                    'Jenis Cuti'      => $cuti->jenis_cuti_label,
                    'Tanggal Mulai'   => $cuti->tanggal_mulai->format('d/m/Y'),
                    'Tanggal Selesai' => $cuti->tanggal_selesai->format('d/m/Y'),
                    'Jumlah Hari'     => $cuti->jumlah_hari . ' hari',
                    'Alasan'          => $cuti->alasan,
                    'Status'          => ucfirst($cuti->status),
                    'Catatan Admin'   => $cuti->catatan_admin ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return ['No', 'NIP', 'Nama Karyawan', 'Jenis Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Jumlah Hari', 'Alasan', 'Status', 'Catatan Admin'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Laporan Cuti';
    }
}
