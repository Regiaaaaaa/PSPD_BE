<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifikasiController extends Controller
{
    /**
     * List transaksi menunggu untuk operator
     */
    public function index()
    {
        $transaksi = Transaksi::with([
            'buku',
            'user.siswa',
            'user.staff' 
        ])
        ->where('status', 'menunggu')
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    /**
     * Approve peminjaman
     */
    public function approve($id)
    {
        $transaksi = Transaksi::with('buku')->findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah diverifikasi'
            ], 422);
        }

        $buku = $transaksi->buku;

        // cek stok
        if ($transaksi->jumlah > $buku->stok_tersedia) {
            return response()->json([
                'success' => false,
                'message' => 'Stok buku tidak mencukupi'
            ], 422);
        }

        // update stok
        $buku->stok_tersedia -= $transaksi->jumlah;
        $buku->save();

        // update transaksi
        $transaksi->update([
            'status' => 'dipinjam',
            'tgl_pinjam' => now(),
            'disetujui_oleh' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil disetujui',
            'data' => $transaksi
        ]);
    }

    /**
     * Tolak peminjaman
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'pesan_ditolak' => 'required|string|max:255',
        ]);

        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah diverifikasi'
            ], 422);
        }

        $transaksi->update([
            'status' => 'ditolak',
            'pesan_ditolak' => $request->pesan_ditolak,
            'ditolak_oleh' => Auth::id(), 
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditolak',
            'data' => $transaksi
        ]);
    }
}
