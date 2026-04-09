<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Buku;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    // Daftar Transaksi User ( Login )
    public function index()
    {
        $transaksi = Transaksi::with(['buku', 'denda'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'buku' => $t->buku,
                    'jumlah' => $t->jumlah,
                    'status' => $t->status,
                    'tgl_deadline' => $t->tgl_deadline,
                    'pesan_ditolak' => $t->pesan_ditolak,
                    'denda' => $t->denda,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Cek denda aktif
    private function getDendaAktif(int $userId): ?Denda
    {
        return Denda::whereHas('transaksi', fn($q) => $q->where('user_id', $userId))
            ->where('status_pembayaran', 'belum_lunas')
            ->with('transaksi.buku')
            ->first();
    }

    // Ajukan peminjaman
    public function store(Request $request, $bukuId)
    {
        $request->validate([
            'jumlah'       => 'required|integer|min:1',
            'kepentingan'  => 'nullable|string',
            'tgl_deadline' => 'required|date|after_or_equal:today',
        ]);

        $dendaAktif = $this->getDendaAktif(Auth::id());
        if ($dendaAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu masih memiliki denda yang belum lunas. Selesaikan denda terlebih dahulu sebelum meminjam buku.',
                'denda'   => [
                    'id'           => $dendaAktif->id,
                    'nominal'      => $dendaAktif->nominal,
                    'judul_buku'   => $dendaAktif->transaksi?->buku?->judul,
                ],
            ], 422);
        }
       
        $buku = Buku::findOrFail($bukuId);

        if ($request->jumlah > $buku->stok_tersedia) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku tidak mencukupi'
            ], 422);
        }

        $transaksi = Transaksi::create([
            'user_id'      => Auth::id(),
            'buku_id'      => $buku->id,
            'jumlah'       => $request->jumlah,
            'kepentingan'  => $request->kepentingan,
            'tgl_deadline' => $request->tgl_deadline,
            'status'       => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan',
            'data'    => $transaksi
        ], 201);
    }

    // Detail Transaksi
    public function show($id)
    {
        $transaksi = Transaksi::with(['buku', 'denda'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Batalkan Pengajuan
    public function cancel($id)
    {
        $transaksi = Transaksi::where('user_id', Auth::id())
            ->where('status', 'menunggu')
            ->findOrFail($id);

        $transaksi->update(['status' => 'dibatalkan']);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman berhasil dibatalkan'
        ]);
    }

    // Cek denda
    public function cekDenda()
    {
        $denda = $this->getDendaAktif(Auth::id());

        if ($denda) {
            return response()->json([
                'ada_denda'  => true,
                'denda'      => [
                    'id'         => $denda->id,
                    'nominal'    => $denda->nominal,
                    'judul_buku' => $denda->transaksi?->buku?->judul,
                ],
            ]);
        }

        return response()->json(['ada_denda' => false]);
    }
}