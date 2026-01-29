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
    <h4>Periode: {{ $periode['dari'] }} s/d {{ $periode['sampai'] }}</h4>

    <table>
        <thead>
            <tr>
                <th>Tanggal Denda</th>
                <th>User</th>
                <th>Buku</th>
                <th>Nominal</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td>{{ $d->created_at->format('Y-m-d') }}</td>
                    <td>{{ $d->transaksi->user->name }}</td>
                    <td>{{ $d->transaksi->buku->judul }}</td>
                    <td>{{ number_format($d->nominal, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($d->status_pembayaran) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
