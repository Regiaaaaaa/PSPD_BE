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

<h2>Laporan Transaksi</h2>

<p>
    Periode: {{ $periode['bulan'] }} {{ $periode['tahun'] }}
    ({{ $periode['dari'] }} s/d {{ $periode['sampai'] }})
</p>

<div class="summary">
    <p>Total Transaksi: {{ $summary['transaksi']['total'] }}</p>
    <p>Dipinjam: {{ $summary['transaksi']['dipinjam'] }}</p>
    <p>Kembali: {{ $summary['transaksi']['kembali'] }}</p>
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
        @php $no = 1; @endphp
        @foreach ($data as $t)
            @foreach ($t->details as $dt)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ optional($t->user)->name ?? 'User Dihapus' }}</td>
                    <td>{{ optional($dt->buku)->judul ?? 'Buku Dihapus' }}</td>
                    <td>{{ $t->tgl_pinjam ? \Carbon\Carbon::parse($t->tgl_pinjam)->format('d M Y') : '-' }}</td>
                    <td>{{ $t->tgl_deadline ? \Carbon\Carbon::parse($t->tgl_deadline)->format('d M Y') : '-' }}</td>
                    <td>{{ $dt->tgl_kembali ? \Carbon\Carbon::parse($dt->tgl_kembali)->format('d M Y') : '-' }}</td>
                    <td>{{ ucfirst($t->status) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

</body>
</html>