<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Denda</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin: 0 0 4px 0; }
        h4 { margin: 8px 0 4px 0; }
    </style>
</head>
<body>
    <h2>Laporan Denda</h2>
    <h4>Periode: {{ $periode['bulan'] }} {{ $periode['tahun'] }} ({{ $periode['dari'] }} s/d {{ $periode['sampai'] }})</h4>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Kondisi</th>
                <th>Denda Telat</th>
                <th>Denda Rusak</th>
                <th>Denda Hilang</th>
                <th>Total Denda</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Lunas Pada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $dt)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($dt->transaksi?->user)->name ?? 'User Dihapus' }}</td>
                    <td>{{ optional($dt->buku)->judul ?? 'Buku Dihapus' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $dt->status ?? '-')) }}</td>
                    <td>Rp {{ number_format($dt->denda_telat ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($dt->denda_kerusakan ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($dt->denda_hilang ?? 0, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($dt->total_denda_item ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $dt->tgl_kembali ? \Carbon\Carbon::parse($dt->tgl_kembali)->format('d M Y') : '-' }}</td>
                    <td>{{ $dt->transaksi?->status_denda === 'lunas' ? 'Lunas' : 'Belum Lunas' }}</td>
                    <td>{{ $dt->transaksi?->tgl_lunas ? \Carbon\Carbon::parse($dt->transaksi->tgl_lunas)->format('d M Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>