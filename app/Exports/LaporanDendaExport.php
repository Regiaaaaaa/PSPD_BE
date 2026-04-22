<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\DetailTransaksi;

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
        $query = DetailTransaksi::with(['buku', 'transaksi.user'])
            ->where('total_denda_item', '>', 0)
            ->whereHas('transaksi', function ($q) {
                $q->whereBetween('created_at', [$this->dari, $this->sampai]);
            });

        if ($this->status) {
            $query->whereHas('transaksi', function ($q) {
                $q->where('status_denda', $this->status);
            });
        }

        return $query->orderBy('created_at', 'desc')->get()->map(function ($dt, $index) {
            return [
                'No'               => $index + 1,
                'Peminjam'         => optional($dt->transaksi?->user)->name ?? 'User Dihapus',
                'Buku'             => optional($dt->buku)->judul ?? 'Buku Dihapus',
                'Kondisi'          => ucfirst(str_replace('_', ' ', $dt->status ?? '-')),
                'Denda Telat'      => 'Rp ' . number_format($dt->denda_telat ?? 0, 0, ',', '.'),
                'Denda Kerusakan'  => 'Rp ' . number_format($dt->denda_kerusakan ?? 0, 0, ',', '.'),
                'Denda Hilang'     => 'Rp ' . number_format($dt->denda_hilang ?? 0, 0, ',', '.'),
                'Total Denda'      => 'Rp ' . number_format($dt->total_denda_item ?? 0, 0, ',', '.'),
                'Tgl Kembali'      => $dt->tgl_kembali
                                        ? \Carbon\Carbon::parse($dt->tgl_kembali)->format('d M Y') : '-',
                'Status'           => $dt->transaksi?->status_denda === 'lunas' ? 'Lunas' : 'Belum Lunas',
                'Lunas Pada'       => $dt->transaksi?->tgl_lunas
                                        ? \Carbon\Carbon::parse($dt->transaksi->tgl_lunas)->format('d M Y') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Peminjam', 'Buku', 'Kondisi', 'Denda Telat', 'Denda Kerusakan', 'Denda Hilang', 'Total Denda', 'Tgl Kembali', 'Status', 'Lunas Pada'];
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