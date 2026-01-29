<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Transaksi;
use App\Models\Denda;
use Illuminate\Support\Collection;

class LaporanSummaryExport implements FromCollection, WithHeadings
{
    protected $dari, $sampai, $statusTransaksi, $statusDenda;

    public function __construct($dari, $sampai, $statusTransaksi = null, $statusDenda = null)
    {
        $this->dari = $dari;
        $this->sampai = $sampai;
        $this->statusTransaksi = $statusTransaksi;
        $this->statusDenda = $statusDenda;
    }

    public function collection()
    {
        // Ambil transaksi
        $transaksi = Transaksi::with(['user','buku'])
            ->whereBetween('created_at', [$this->dari, $this->sampai]);

        if ($this->statusTransaksi) $transaksi->where('status', $this->statusTransaksi);

        $transaksiData = $transaksi->get();

        // Ambil denda, filter status pembayaran jika ada
        $dendaQuery = Denda::whereBetween('created_at', [$this->dari, $this->sampai]);
        if ($this->statusDenda) $dendaQuery->where('status_pembayaran', $this->statusDenda);

        $dendaAll = $dendaQuery->get()->groupBy('transaksi_id');

        $summary = collect();

        foreach ($transaksiData as $t) {
            // Skip transaksi yang ga punya denda sama sekali saat filter status pembayaran
            if ($this->statusDenda && !isset($dendaAll[$t->id])) {
                continue; // lewati
            }

            if (isset($dendaAll[$t->id])) {
                $totalNominal = $dendaAll[$t->id]->sum('nominal');
                $statusPembayaran = $dendaAll[$t->id]->pluck('status_pembayaran')->unique()->implode(', ');
            } else {
                $totalNominal = 0;
                $statusPembayaran = '-';
            }

            $summary->push([
                'Tanggal' => $t->created_at->format('Y-m-d'),
                'User' => $t->user->name,
                'Buku' => $t->buku->judul,
                'Status' => $t->status,
                'Nominal Denda' => $totalNominal,
                'Status Pembayaran' => $statusPembayaran,
            ]);
        }

        return $summary;
    }


    public function headings(): array
    {
        return ['Tanggal','User','Buku','Status','Nominal Denda','Status Pembayaran'];
    }
}
