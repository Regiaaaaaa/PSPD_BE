<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelolaTransaksiController extends Controller
{
    public function index()
    {
        $data = Transaksi::with([
            'user',
            'details.buku'
        ])
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function updateDeadline(Request $request, $id)
    {
        $request->validate([
            'tgl_deadline' => 'required|date'
        ]);

        $trx = Transaksi::with('details')->findOrFail($id);

        DB::beginTransaction();
        try {

            $trx->update([
                'tgl_deadline' => $request->tgl_deadline
            ]);
            foreach ($trx->details as $detail) {
                if (!$detail->tgl_kembali) continue;
                if ($detail->status !== 'hilang') {

                    if ($detail->tgl_kembali > $trx->tgl_deadline) {

                        $hari = Carbon::parse($detail->tgl_kembali)
                            ->diffInDays($trx->tgl_deadline);

                        $detail->denda_telat = $hari * 1000;

                    } else {
                        $detail->denda_telat = 0;
                    }
                    $detail->total_denda_item =
                        $detail->denda_telat +
                        $detail->denda_kerusakan;

                    $detail->save();
                }
            }
            $total = $trx->details()->sum('total_denda_item');

            $trx->update([
                'total_denda' => $total,
                'status_denda' => $total > 0 ? 'belum_bayar' : null,
                'tgl_lunas' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deadline berhasil diupdate & denda direcalculate'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
    public function overrideKembaliNormal($detailId)
    {
        $detail = DetailTransaksi::with(['transaksi', 'buku'])->findOrFail($detailId);
        $trx = $detail->transaksi;
        $buku = $detail->buku;

        // guard
        if ($detail->status !== 'hilang') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya bisa override dari status hilang'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $buku->increment('stok_total');
            $buku->increment('stok_tersedia'); 
            $detail->update([
                'status' => 'kembali_normal',
                'denda_telat' => 0,
                'denda_kerusakan' => 0,
                'denda_hilang' => 0,
                'total_denda_item' => 0,
            ]);
            $total = $trx->details()->sum('total_denda_item');

            $trx->update([
                'total_denda' => $total,
                'status_denda' => $total > 0 ? 'belum_bayar' : null,
                'tgl_lunas' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil override ke kembali normal, denda dihapus & stok diperbaiki'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}