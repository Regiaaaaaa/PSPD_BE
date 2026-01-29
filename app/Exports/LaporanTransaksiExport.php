<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Transaksi;

class LaporanTransaksiExport implements FromCollection, WithHeadings
{
    protected $dari,$sampai,$status;
    public function __construct($dari,$sampai,$status=null){ $this->dari=$dari; $this->sampai=$sampai; $this->status=$status; }

    public function collection()
    {
        $query = Transaksi::with(['user','buku'])->whereBetween('created_at',[$this->dari,$this->sampai]);
        if($this->status) $query->where('status',$this->status);

        return $query->get()->map(fn($t)=>[
            'Tanggal'=>$t->created_at->format('Y-m-d'),
            'User'=>$t->user->name,
            'Buku'=>$t->buku->judul,
            'Status'=>$t->status
        ]);
    }

    public function headings(): array
    {
        return ['Tanggal','User','Buku','Status'];
    }
}
