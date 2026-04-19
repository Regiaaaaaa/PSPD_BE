<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBorrowingRequest extends Notification
{
    use Queueable;

    protected $transaksi;

    public function __construct($transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
{
    $judulBuku = $this->transaksi->details
        ->map(fn($d) => $d->buku->judul ?? '-')
        ->join(', ');

    return [
        'transaksi_id' => $this->transaksi->id,
        'user_id'      => $this->transaksi->user_id,
        'nama_peminjam'=> $this->transaksi->user->name,
        'judul_buku'   => $judulBuku,
        'message'      => "Permintaan pinjam baru: {$this->transaksi->user->name} ingin meminjam buku {$judulBuku}",
        'type'         => 'request_approval',
        'status'       => 'pending'
    ];
}
}