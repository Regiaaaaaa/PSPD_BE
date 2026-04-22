<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Transaksi;

class LaporanSummaryExport implements FromCollection, WithHeadings, WithStyles
{
    protected $dari, $sampai, $statusTransaksi, $statusDenda;

    public function __construct($dari, $sampai, $statusTransaksi = null, $statusDenda = null)
    {
        $this->dari            = $dari;
        $this->sampai          = $sampai;
        $this->statusTransaksi = $statusTransaksi;
        $this->statusDenda     = $statusDenda;
    }

    public function collection()
    {
        $transaksiQuery = Transaksi::whereBetween('created_at', [$this->dari, $this->sampai])
            ->whereIn('status', ['dipinjam', 'kembali']);

        if ($this->statusTransaksi) {
            $transaksiQuery->where('status', $this->statusTransaksi);
        }

        $transaksi = $transaksiQuery->get();

        $totalDipinjam  = $transaksi->where('status', 'dipinjam')->count();
        $totalKembali   = $transaksi->where('status', 'kembali')->count();
        $totalTransaksi = $totalDipinjam + $totalKembali;

        $dendaQuery = Transaksi::where('total_denda', '>', 0)
            ->whereBetween('created_at', [$this->dari, $this->sampai]);

        if ($this->statusDenda) {
            $dendaQuery->where('status_denda', $this->statusDenda);
        }

        $denda = $dendaQuery->get();

        $totalDenda        = $denda->count();
        $totalNominalDenda = $denda->sum('total_denda');
        $totalLunas        = $denda->where('status_denda', 'lunas')->count();
        $totalBelumLunas   = $denda->where('status_denda', 'belum_bayar')->count();
        $nominalLunas      = $denda->where('status_denda', 'lunas')->sum('total_denda');
        $nominalBelumLunas = $denda->where('status_denda', 'belum_bayar')->sum('total_denda');

        $periodeLabel = \Carbon\Carbon::parse($this->dari)->translatedFormat('F Y')
            . ' (' . \Carbon\Carbon::parse($this->dari)->format('Y-m-d')
            . ' s/d ' . \Carbon\Carbon::parse($this->sampai)->format('Y-m-d') . ')';

        return collect([
            ['REKAP TRANSAKSI', '', ''],
            ['Keterangan', 'Jumlah', ''],
            ['Total Transaksi', $totalTransaksi, ''],
            ['Status Dipinjam', $totalDipinjam, ''],
            ['Status Kembali',  $totalKembali,  ''],

            ['', '', ''],

            ['REKAP DENDA', '', ''],
            ['Keterangan', 'Jumlah', 'Total Nominal (Rp)'],
            ['Total Denda',       $totalDenda,      'Rp ' . number_format($totalNominalDenda, 0, ',', '.')],
            ['Denda Lunas',       $totalLunas,      'Rp ' . number_format($nominalLunas, 0, ',', '.')],
            ['Denda Belum Lunas', $totalBelumLunas, 'Rp ' . number_format($nominalBelumLunas, 0, ',', '.')],

            ['', '', ''],

            ['Periode', $periodeLabel, ''],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true, 'size' => 12]],
            8 => ['font' => ['bold' => true]],
        ];
    }
}