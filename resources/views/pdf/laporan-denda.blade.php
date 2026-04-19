<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Denda</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2, h4 { margin: 0; padding: 0; }
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
                <th>Tgl Pinjam</th>
                <th>Deadline</th>
                <th>Nominal</th>
                <th>Status Pembayaran</th>
                <th>Dibayar Pada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($d->transaksiDetail?->transaksi?->user)->name ?? 'User Dihapus' }}</td>
                    <td>{{ optional($d->transaksiDetail?->buku)->judul ?? 'Buku Dihapus' }}</td>
                    <td>{{ $d->transaksiDetail?->transaksi?->tgl_pinjam ? \Carbon\Carbon::parse($d->transaksiDetail->transaksi->tgl_pinjam)->format('d M Y') : '-' }}</td>
                    <td>{{ $d->transaksiDetail?->transaksi?->tgl_deadline ? \Carbon\Carbon::parse($d->transaksiDetail->transaksi->tgl_deadline)->format('d M Y') : '-' }}</td>
                    <td>Rp {{ number_format($d->nominal, 0, ',', '.') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $d->status_pembayaran)) }}</td>
                    <td>{{ $d->tgl_pembayaran ? \Carbon\Carbon::parse($d->tgl_pembayaran)->format('d M Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>