<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['user.siswa', 'user.staff', 'details.buku'])
            ->where('total_denda', '>', 0);

        if ($request->filled('status')) {
            $query->where('status_denda', $request->status);
        } else {
            $query->where('status_denda', 'belum_bayar');
        }

        $transaksi = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data denda berhasil dimuat.',
            'data' => $transaksi
        ]);
    }
    public function bayar($id)
    {
        $transaksi = Transaksi::with('details')->findOrFail($id);

        if ($transaksi->status_denda === 'lunas') {
            return response()->json([
                'success' => false, 
                'message' => 'Denda sudah lunas sebelumnya.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status_denda' => 'lunas',
                'tgl_lunas' => now(),
                'penerima_denda_id' => Auth::id(), 
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran denda berhasil diverifikasi.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}