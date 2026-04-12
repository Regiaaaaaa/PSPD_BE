<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Transaksi;
use App\Models\Denda;

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
        // Transaksi
        $transaksiQuery = Transaksi::whereBetween('created_at', [$this->dari, $this->sampai]);
        if ($this->statusTransaksi) $transaksiQuery->where('status', $this->statusTransaksi);
        $transaksi = $transaksiQuery->get();

        $totalKembali    = $transaksi->where('status', 'kembali')->count();
        $totalDitolak    = $transaksi->where('status', 'ditolak')->count();

        // Total transaksi 
        $totalTransaksi  = $totalKembali + $totalDitolak;

        // Denda 
        $dendaQuery = Denda::whereBetween('created_at', [$this->dari, $this->sampai]);
        if ($this->statusDenda) $dendaQuery->where('status_pembayaran', $this->statusDenda);
        $denda = $dendaQuery->get();

        $totalDenda        = $denda->count();
        $totalNominalDenda = $denda->sum('nominal');
        $totalLunas        = $denda->where('status_pembayaran', 'lunas')->count();
        $totalBelumLunas   = $denda->where('status_pembayaran', 'belum_lunas')->count();
        $nominalLunas      = $denda->where('status_pembayaran', 'lunas')->sum('nominal');
        $nominalBelumLunas = $denda->where('status_pembayaran', 'belum_lunas')->sum('nominal');

        $periodeLabel = \Carbon\Carbon::parse($this->dari)->translatedFormat('F Y')
            . ' (' . \Carbon\Carbon::parse($this->dari)->format('Y-m-d')
            . ' s/d ' . \Carbon\Carbon::parse($this->sampai)->format('Y-m-d') . ')';

        return collect([
            // Transaksi 
            ['REKAP TRANSAKSI', '', ''],
            ['Keterangan', 'Jumlah', ''],
            ['Total Transaksi (Kembali + Ditolak)', $totalTransaksi, ''],
            ['Status Kembali',                      $totalKembali,   ''],
            ['Status Ditolak',                      $totalDitolak,   ''],

            // Spacer
            ['', '', ''],

            // Header 
            ['REKAP DENDA', '', ''],
            ['Keterangan', 'Jumlah', 'Total Nominal (Rp)'],
            ['Total Denda',       $totalDenda,      'Rp ' . number_format($totalNominalDenda, 0, ',', '.')],
            ['Denda Lunas',       $totalLunas,      'Rp ' . number_format($nominalLunas, 0, ',', '.')],
            ['Denda Belum Lunas', $totalBelumLunas, 'Rp ' . number_format($nominalBelumLunas, 0, ',', '.')],

            // Spacer
            ['', '', ''],

            // Periode 
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