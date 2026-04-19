<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Denda;
use App\Models\Buku;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    // List transaksi yang sedang dipinjam
    public function index()
    {
        $transaksi = Transaksi::with([
            'details.buku',
            'user.siswa',
            'user.staff'
        ])
        ->where('status', 'dipinjam')
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }


    // Terima Pengembalian Per Buku
    public function terima($detailId)
    {
        $detail = DetailTransaksi::with([
            'transaksi',
            'buku'
        ])->findOrFail($detailId);


        if ($detail->status !== 'dipinjam') {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak sedang dipinjam'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $transaksi = $detail->transaksi;
            $tglSekarang = now();
            Buku::where('id', $detail->buku_id)
                ->increment('stok_tersedia');

            // Update detail 
            $detail->update([
                'status' => 'kembali',
                'tgl_kembali' => $tglSekarang
            ]);
            $semuaKembali = $transaksi->details()
                ->where('status', 'dipinjam')
                ->count() == 0;

            if ($semuaKembali) {
                $transaksi->update([
                    'status' => 'kembali',
                    'diterima_oleh' => Auth::id(),
                ]);
            }

            $tglDeadline = Carbon::parse($transaksi->tgl_deadline)->startOfDay();
            $tglKembali  = Carbon::parse($tglSekarang)->startOfDay();

            $message = 'Buku berhasil dikembalikan';

            if ($tglKembali->greaterThan($tglDeadline)) {

                $selisihHari = $tglKembali->diffInDays($tglDeadline);
                $nominal = $selisihHari * 1000;

                Denda::updateOrCreate(
                    [
                        'detail_transaksi_id' => $detail->id
                    ],
                    [
                        'nominal' => $nominal,
                        'status_pembayaran' => 'belum_lunas'
                    ]
                );

                $message .= ', terlambat '
                    . $selisihHari
                    . ' hari, denda Rp '
                    . number_format($nominal);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $detail
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }
}