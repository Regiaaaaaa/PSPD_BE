<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    /**
     * List transaksi user login
     */
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
                    'denda' => $t->denda,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    /**
     * Ajukan peminjaman
     */
    public function store(Request $request, $bukuId)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'kepentingan' => 'nullable|string',
            'tgl_deadline' => 'required|date|after_or_equal:today',
        ]);

        $buku = Buku::findOrFail($bukuId);

        // Cek stok awal (belum dikurangin)
        if ($request->jumlah > $buku->stok_tersedia) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku tidak mencukupi'
            ], 422);
        }

        $transaksi = Transaksi::create([
            'user_id' => Auth::id(),
            'buku_id' => $buku->id,
            'jumlah' => $request->jumlah,
            'kepentingan' => $request->kepentingan,
            'tgl_deadline' => $request->tgl_deadline,
            'status' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan',
            'data' => $transaksi
        ], 201);
    }

    /**
     * Detail transaksi
     */
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

    /**
     * Batalkan pengajuan (ubah status ke dibatalkan)
     */
    public function cancel($id)
    {
        $transaksi = Transaksi::where('user_id', Auth::id())
            ->where('status', 'menunggu')
            ->findOrFail($id);

        $transaksi->update([
            'status' => 'dibatalkan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peminjaman berhasil dibatalkan'
        ]);
    }
}
