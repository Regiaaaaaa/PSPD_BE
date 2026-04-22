<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 16px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin: 0 0 4px 0; padding: 0; }
        h4 { margin: 12px 0 4px 0; padding: 0; }
    </style>
</head>
<body>
    <h2>Laporan Summary</h2>
    <h4>Periode: {{ $periode['bulan'] }} {{ $periode['tahun'] }} ({{ $periode['dari'] }} s/d {{ $periode['sampai'] }})</h4>

    <h4>Transaksi</h4>
    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Transaksi</td>
                <td>{{ $transaksi['total'] }}</td>
            </tr>
            <tr>
                <td>Status Dipinjam</td>
                <td>{{ $transaksi['dipinjam'] }}</td>
            </tr>
            <tr>
                <td>Status Kembali</td>
                <td>{{ $transaksi['kembali'] }}</td>
            </tr>
        </tbody>
    </table>

    <h4>Denda</h4>
    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Total Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Denda</td>
                <td>{{ $denda['lunas'] + $denda['belum_lunas'] }}</td>
                <td>Rp {{ number_format($denda['total_nominal'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Denda Lunas</td>
                <td>{{ $denda['lunas'] }}</td>
                <td>Rp {{ number_format($denda['nominal_lunas'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Denda Belum Lunas</td>
                <td>{{ $denda['belum_lunas'] }}</td>
                <td>Rp {{ number_format($denda['nominal_belum_lunas'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>