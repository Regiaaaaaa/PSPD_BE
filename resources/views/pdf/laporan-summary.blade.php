<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2, h4 { margin: 0; padding: 0; }
    </style>
</head>
<body>
    <h2>Laporan Summary</h2>
    <h4>Periode: {{ $periode['bulan'] }} {{ $periode['tahun'] }} ({{ $periode['dari'] }} s/d {{ $periode['sampai'] }})</h4>

    <h4>Transaksi</h4>
    <table>
        <thead>
            <tr>
                <th>Total Transaksi</th>
                <th>Dipinjam</th>
                <th>Kembali</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $transaksi['total'] }}</td>
                <td>{{ $transaksi['dipinjam'] }}</td>
                <td>{{ $transaksi['kembali'] }}</td>
            </tr>
        </tbody>
    </table>

    <h4>Denda</h4>
    <table>
        <thead>
            <tr>
                <th>Total Nominal</th>
                <th>Lunas</th>
                <th>Belum Lunas</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($denda['total_nominal'], 0, ',', '.') }}</td>
                <td>{{ $denda['lunas'] }}</td>
                <td>{{ $denda['belum_lunas'] }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
