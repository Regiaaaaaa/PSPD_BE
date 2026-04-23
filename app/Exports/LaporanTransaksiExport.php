<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class LaporanTransaksiExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $dari, $sampai, $status;
    protected $rows = [];

    public function __construct($dari, $sampai, $status = null)
    {
        $this->dari   = $dari;
        $this->sampai = $sampai;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Transaksi::with(['user', 'details.buku'])
            ->whereBetween('created_at', [$this->dari, $this->sampai])
            ->whereIn('status', ['dipinjam', 'kembali']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $data = [];
        $no   = 1;

        foreach ($query->get() as $t) {

            $total = $t->details->count();

            foreach ($t->details as $i => $dt) {
                $data[] = [
                    'No'          => $no++,
                    'Peminjam'    => optional($t->user)->name ?? 'User Dihapus',
                    'Judul Buku'  => optional($dt->buku)->judul ?? 'Buku Dihapus',
                    'Tgl Pinjam'  => $t->tgl_pinjam ? Carbon::parse($t->tgl_pinjam)->format('d M Y') : '-',
                    'Deadline'    => $t->tgl_deadline ? Carbon::parse($t->tgl_deadline)->format('d M Y') : '-',
                    'Tgl Kembali' => $dt->tgl_kembali ? Carbon::parse($dt->tgl_kembali)->format('d M Y') : '-',
                    'Status Transaksi' => $i == 0 ? ucfirst($t->status) : '',
                    'Status Detail' => ucfirst(str_replace('_', ' ', $dt->status)),
                    'rowspan' => $i == 0 ? $total : 0,
                ];
            }
        }

        $this->rows = $data;

        return collect($data)->map(function ($item) {
            unset($item['rowspan']);
            return $item;
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Peminjam',
            'Judul Buku',
            'Tgl Pinjam',
            'Deadline',
            'Tgl Kembali',
            'Status Transaksi',
            'Status Detail'
        ];
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

                $startRow = 2;
                $current  = $startRow;

                foreach ($this->rows as $row) {
                    if ($row['rowspan'] > 1) {
                        $end = $current + $row['rowspan'] - 1;
                        $sheet->mergeCells("G{$current}:G{$end}");
                    }
                    $current++;
                }
                $lastRow = $sheet->getHighestRow() + 2;

                $sheet->setCellValue("A{$lastRow}", 'Periode');
                $sheet->setCellValue(
                    "B{$lastRow}",
                    Carbon::parse($this->dari)->translatedFormat('F Y') .
                    ' (' . Carbon::parse($this->dari)->format('Y-m-d') .
                    ' s/d ' . Carbon::parse($this->sampai)->format('Y-m-d') . ')'
                );
            }
        ];
    }
}