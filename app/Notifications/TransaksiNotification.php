<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransaksiNotification extends Notification
{
    use Queueable;

    protected $transaksi;

    public function __construct($transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }

    public function toArray($notifiable)
    {
        $status = $this->transaksi->status;
        $judul = $this->transaksi->buku->judul; 
        
        $pesan = "";
        if ($status === 'dipinjam') {
            $pesan = "Peminjaman buku '$judul' telah DISETUJUI.";
        } elseif ($status === 'ditolak') {
            $pesan = "Peminjaman buku '$judul' DITOLAK. Alasan: " . ($this->transaksi->pesan_ditolak ?? '-');
        }

        return [
            'transaksi_id' => $this->transaksi->id,
            'judul'        => $judul,
            'status'       => $status,
            'message'      => $pesan,
        ];
    }
}