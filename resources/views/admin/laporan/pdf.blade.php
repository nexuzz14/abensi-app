<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        * { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 0; }
        body { padding: 30px; color: #5c3d1e; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7c4a1e; padding-bottom: 15px; }
        .header h1 { font-size: 18px; font-weight: bold; color: #2c1503; margin-bottom: 5px; }
        .header p { color: #9b7255; font-size: 10px; }

        .filter-info { background: #fdf8f2; border: 1px solid #e8d5bc; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px; }
        .filter-info span { margin-right: 15px; color: #9b7255; }
        .filter-info strong { color: #2c1503; }

        /* DomPDF does not support flexbox well, use table for summary */
        .summary-table { width: 100%; margin-bottom: 15px; border-collapse: separate; border-spacing: 10px 0; }
        .summary-card { background: #fdf8f2; border: 1px solid #e8d5bc; border-radius: 6px; padding: 10px; text-align: center; width: 25%; }
        .summary-card .val { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
        .summary-card .lbl { font-size: 10px; color: #9b7255; text-transform: uppercase; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table thead tr { background: #7c4a1e; color: white; }
        .data-table thead th { padding: 8px; text-align: left; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .data-table tbody tr:nth-child(even) { background: #fdf8f2; }
        .data-table tbody td { padding: 7px 8px; border-bottom: 1px solid #e8d5bc; vertical-align: middle; }

        .badge { display: inline-block; padding: 3px 6px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-hadir    { background: #dcfce7; color: #166534; }
        .badge-terlambat{ background: #fef9c3; color: #854d0e; }
        .badge-alpa     { background: #fee2e2; color: #991b1b; }
        .badge-cuti     { background: #dbeafe; color: #1e40af; }

        .footer { margin-top: 20px; text-align: right; color: #9b7255; font-size: 9px; border-top: 1px solid #e8d5bc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI KARYAWAN</h1>
        <p>Oobake Bakery — Dicetak pada {{ now()->translatedFormat('l, d F Y H:i') }} WIB</p>
    </div>

    <div class="filter-info">
        <span><strong>Periode:</strong>
            @if(!empty($filter['tanggal_dari']) && !empty($filter['tanggal_sampai']))
                {{ \Carbon\Carbon::parse($filter['tanggal_dari'])->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($filter['tanggal_sampai'])->translatedFormat('d M Y') }}
            @elseif(!empty($filter['bulan']))
                {{ \Carbon\Carbon::create(null, $filter['bulan'])->translatedFormat('F') }} {{ $filter['tahun'] ?? now()->year }}
            @else
                Semua Periode
            @endif
        </span>
        @if(!empty($filter['status']))
            <span><strong>Status:</strong> {{ ucfirst($filter['status']) }}</span>
        @endif
        <span><strong>Total Record:</strong> {{ $absensi->count() }}</span>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="val" style="color:#16a34a">{{ $summary['hadir'] }}</div>
                <div class="lbl">Hadir</div>
            </td>
            <td class="summary-card">
                <div class="val" style="color:#d97706">{{ $summary['terlambat'] }}</div>
                <div class="lbl">Terlambat</div>
            </td>
            <td class="summary-card">
                <div class="val" style="color:#dc2626">{{ $summary['alpa'] }}</div>
                <div class="lbl">Alpa</div>
            </td>
            <td class="summary-card">
                <div class="val" style="color:#2563eb">{{ $summary['cuti'] }}</div>
                <div class="lbl">Cuti</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>NIP</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>GPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $index => $a)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $a->karyawan->nip ?? '—' }}</td>
                <td><strong>{{ $a->karyawan->nama_lengkap ?? '—' }}</strong></td>
                <td>{{ $a->karyawan->jabatan ?? '—' }}</td>
                <td>{{ $a->tanggal->format('d/m/Y') }}</td>
                <td>{{ $a->jam_masuk ? substr($a->jam_masuk,0,5) : '—' }}</td>
                <td>{{ $a->jam_keluar ? substr($a->jam_keluar,0,5) : '—' }}</td>
                <td>{{ $a->durasi_kerja_format ?? '—' }}</td>
                <td><span class="badge badge-{{ $a->status_kehadiran }}">{{ ucfirst($a->status_kehadiran) }}</span></td>
                <td>{{ $a->status_fake_gps === 'clean' ? 'Aman' : ($a->status_fake_gps === 'suspicious' ? 'Palsu' : '—') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center;color:#94a3b8;padding:20px">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistem Absensi Karyawan &copy; {{ date('Y') }} — Dokumen ini digenerate otomatis oleh sistem
    </div>
</body>
</html>
