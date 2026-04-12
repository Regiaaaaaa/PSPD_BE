<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
        .summary p { margin: 2px 0; }
    </style>
</head>
<body>

<h2>Laporan Transaksi Perbulan</h2>

<p>
    Periode: {{ $periode['bulan'] }} {{ $periode['tahun'] }}
    ({{ $periode['dari'] }} s/d {{ $periode['sampai'] }})
</p>

<div class="summary">
    <p>Total Transaksi: {{ $summary['transaksi']['total'] }}</p>
    <p>Kembali: {{ $summary['transaksi']['kembali'] }}</p>
    <p>Ditolak: {{ $summary['transaksi']['ditolak'] }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Peminjam</th>
            <th>Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Deadline</th>
            <th>Tgl Kembali</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $i => $t)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $t->user->name }}</td>
            <td>{{ $t->buku->judul }}</td>
            <td>{{ $t->tgl_pinjam ? \Carbon\Carbon::parse($t->tgl_pinjam)->format('d M Y') : '-' }}</td>
            <td>{{ $t->tgl_deadline ? \Carbon\Carbon::parse($t->tgl_deadline)->format('d M Y') : '-' }}</td>
            <td>{{ $t->tgl_kembali ? \Carbon\Carbon::parse($t->tgl_kembali)->format('d M Y') : '-' }}</td>
            <td>{{ ucfirst($t->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>