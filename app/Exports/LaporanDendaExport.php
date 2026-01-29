<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\Denda;

class LaporanDendaExport implements FromCollection, WithHeadings
{
    protected $dari,$sampai,$status;
    public function __construct($dari,$sampai,$status=null){ $this->dari=$dari; $this->sampai=$sampai; $this->status=$status; }

    public function collection()
    {
        $query = Denda::with(['transaksi.user','transaksi.buku'])->whereBetween('created_at',[$this->dari,$this->sampai]);
        if($this->status) $query->where('status_pembayaran',$this->status);

        return $query->get()->map(fn($d)=>[
            'Tanggal'=>$d->created_at->format('Y-m-d'),
            'User'=>$d->transaksi->user->name,
            'Buku'=>$d->transaksi->buku->judul,
            'Nominal'=>$d->nominal,
            'Status Pembayaran'=>$d->status_pembayaran
        ]);
    }

    public function headings(): array
    {
        return ['Tanggal','User','Buku','Nominal','Status Pembayaran'];
    }
}
