<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
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
        $transaksi = Transaksi::with(['buku', 'user.siswa', 'user.staff'])
            ->where('status', 'dipinjam')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Terima pengembalian
    public function terima($id)
    {
        $transaksi = Transaksi::with('buku')->findOrFail($id);

        if ($transaksi->status !== 'dipinjam') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak bisa diterima karena statusnya bukan dipinjam'
            ], 422);
        }

        DB::beginTransaction();
        try {
            Buku::where('id', $transaksi->buku_id)->increment('stok_tersedia', 1);
            $tglSekarang = now();
            $transaksi->update([
                'tgl_kembali' => $tglSekarang,
                'diterima_oleh' => Auth::id(),
                'status' => 'kembali',
            ]);
            $tglDeadline = Carbon::parse($transaksi->tgl_deadline)->startOfDay();
            $tglKembali = Carbon::parse($tglSekarang)->startOfDay();
            
            $message = 'Pengembalian berhasil diterima';
            if ($tglKembali->greaterThan($tglDeadline)) {
                $selisihHari = $tglKembali->diffInDays($tglDeadline);
                $nominal = $selisihHari * 1000; 

                Denda::create([
                    'transaksi_id' => $transaksi->id,
                    'nominal' => $nominal,
                    'status_pembayaran' => 'belum_lunas',
                ]);
                
                $message .= ', terlambat ' . $selisihHari . ' hari, denda Rp ' . number_format($nominal);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $transaksi
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