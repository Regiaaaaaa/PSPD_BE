<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Transaksi;

class LaporanTransaksiExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $dari, $sampai, $status;

    public function __construct($dari, $sampai, $status = null)
    {
        $this->dari = $dari;
        $this->sampai = $sampai;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Transaksi::with(['user', 'buku'])
            ->whereBetween('created_at', [$this->dari, $this->sampai]);

        if ($this->status) $query->where('status', $this->status);

        return $query->get()->map(function ($t, $index) {
            return [
                'No'          => $index + 1,
                'Peminjam'    => $t->user->name,
                'Judul Buku'  => $t->buku->judul,
                'Tgl Pinjam'  => $t->tgl_pinjam ? \Carbon\Carbon::parse($t->tgl_pinjam)->format('d M Y') : '-',
                'Deadline'    => $t->tgl_deadline ? \Carbon\Carbon::parse($t->tgl_deadline)->format('d M Y') : '-',
                'Tgl Kembali' => $t->tgl_kembali ? \Carbon\Carbon::parse($t->tgl_kembali)->format('d M Y') : '-',
                'Status'      => ucfirst($t->status),
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Peminjam', 'Judul Buku', 'Tgl Pinjam', 'Deadline', 'Tgl Kembali', 'Status'];
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
                $sheet = $event->sheet->getDelegate();
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