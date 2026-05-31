<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Cuti Karyawan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .header h1 { font-size: 18px; color: #2563eb; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #64748b; }
        .filter-info { background: #f1f5f9; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; font-size: 10px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2563eb; color: white; padding: 8px 6px; text-align: left; font-size: 10px; font-weight: 600; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .status-approved { color: #166534; font-weight: 600; }
        .status-pending { color: #92400e; font-weight: 600; }
        .status-rejected { color: #991b1b; font-weight: 600; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Oobake Bakery</h1>
        <p>Laporan Data Cuti Karyawan</p>
    </div>

    <div class="filter-info">
        Tanggal cetak: {{ now()->format('d F Y H:i') }}
        @if(!empty($filters['status']))
            | Status: {{ ucfirst($filters['status']) }}
        @endif
        @if(!empty($filters['bulan']))
            | Bulan: {{ $filters['bulan'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Tgl Mulai</th>
                <th>Tgl Selesai</th>
                <th>Hari</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Catatan Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cutiList as $index => $cuti)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $cuti->karyawan->nip ?? '-' }}</td>
                <td>{{ $cuti->karyawan->nama_lengkap ?? '-' }}</td>
                <td>{{ $cuti->jenis_cuti_label }}</td>
                <td>{{ $cuti->tanggal_mulai->format('d/m/Y') }}</td>
                <td>{{ $cuti->tanggal_selesai->format('d/m/Y') }}</td>
                <td>{{ $cuti->jumlah_hari }}</td>
                <td>{{ Str::limit($cuti->alasan, 50) }}</td>
                <td class="status-{{ $cuti->status }}">{{ ucfirst($cuti->status) }}</td>
                <td>{{ Str::limit($cuti->catatan_admin ?? '-', 40) }}</td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;padding:20px;color:#94a3b8">Tidak ada data cuti</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh sistem pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
