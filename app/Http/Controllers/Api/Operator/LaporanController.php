<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Denda;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // 1. Laporan transaksi
    public function transaksi(Request $request)
    {
        $query = Transaksi::with(['user', 'buku']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->dari && $request->sampai) {
            $query->whereBetween('created_at', [
                $request->dari,
                $request->sampai
            ]);
        }

        return response()->json([
            'message' => 'Laporan transaksi',
            'data' => $query->orderBy('created_at', 'desc')->get()
        ]);
    }

    // 2. Laporan denda
    public function denda(Request $request)
    {
        $query = Denda::with(['transaksi.user', 'transaksi.buku']);

        if ($request->status_pembayaran) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        return response()->json([
            'message' => 'Laporan denda',
            'data' => $query->get()
        ]);
    }

    // 3. Statistik ringkas (buat summary laporan)
    public function summary()
    {
        return response()->json([
            'total_transaksi' => Transaksi::count(),
            'dipinjam'        => Transaksi::where('status', 'dipinjam')->count(),
            'kembali'         => Transaksi::where('status', 'kembali')->count(),
            'terlambat'       => Denda::where('status_pembayaran', 'belum_lunas')->count(),
        ]);
    }
}