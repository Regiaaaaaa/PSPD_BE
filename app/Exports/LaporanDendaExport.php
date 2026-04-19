<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Denda;

class LaporanDendaExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $dari, $sampai, $status;

    public function __construct($dari, $sampai, $status = null)
    {
        $this->dari   = $dari;
        $this->sampai = $sampai;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Denda::with([
            'transaksiDetail.transaksi.user',
            'transaksiDetail.buku'
        ])
        ->whereBetween('created_at', [$this->dari, $this->sampai]);

        if ($this->status) {
            $query->where('status_pembayaran', $this->status);
        }

        return $query->get()->map(function ($d, $index) {
            $trx = $d->transaksiDetail?->transaksi;

            return [
                'No'                => $index + 1,
                'User'              => optional($d->transaksiDetail?->transaksi?->user)->name ?? 'User Dihapus',
                'Buku'              => optional($d->transaksiDetail?->buku)->judul ?? 'Buku Dihapus',
                'Tgl Pinjam'        => $trx?->tgl_pinjam
                                        ? \Carbon\Carbon::parse($trx->tgl_pinjam)->format('d M Y') : '-',
                'Deadline'          => $trx?->tgl_deadline
                                        ? \Carbon\Carbon::parse($trx->tgl_deadline)->format('d M Y') : '-',
                'Nominal'           => 'Rp ' . number_format($d->nominal, 0, ',', '.'),
                'Status Pembayaran' => ucfirst(str_replace('_', ' ', $d->status_pembayaran)),
                'Tgl Pembayaran'    => $d->tgl_pembayaran
                                        ? \Carbon\Carbon::parse($d->tgl_pembayaran)->format('d M Y') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'User', 'Buku', 'Tgl Pinjam', 'Deadline', 'Nominal', 'Status Pembayaran', 'Tgl Pembayaran'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow() + 2;

                $sheet->setCellValue("A{$lastRow}", 'Periode');
                $sheet->setCellValue("B{$lastRow}",
                    \Carbon\Carbon::parse($this->dari)->translatedFormat('F Y') .
                    ' (' . \Carbon\Carbon::parse($this->dari)->format('Y-m-d') .
                    ' s/d ' . \Carbon\Carbon::parse($this->sampai)->format('Y-m-d') . ')'
                );
            }
        ];
    }
}