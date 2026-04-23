<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with([
            'user.siswa',
            'user.staff',
            'details.buku',
        ])
        ->where('status', 'dipinjam')
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peminjaman aktif',
            'data' => $transaksi
        ]);
    }

    public function terima(Request $request, $detailId)
    {
        $request->validate([
            'status' => 'required|in:kembali_normal,kembali_rusak_ringan,kembali_rusak_sedang,kembali_rusak_berat,hilang',
        ]);

        $detail = DetailTransaksi::with(['transaksi', 'buku'])->findOrFail($detailId);
        $buku = $detail->buku;
        $transaksi = $detail->transaksi;

        if ($detail->status !== 'dipinjam') {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak sedang dipinjam'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $tglSekarang = Carbon::now();
            $tglDeadline = Carbon::parse($transaksi->tgl_deadline);
            $dendaTelat = 0;
            if ($tglSekarang->gt($tglDeadline)) {
                $selisihHari = $tglSekarang->diffInDays($tglDeadline);
                $dendaTelat = $selisihHari * 1000;
            }
            $dendaKerusakan = 0;
            $dendaHilang = 0;
            $totalItem = 0;

            if ($request->status === 'hilang') {
                $dendaTelat = 0;
                $dendaKerusakan = 0;
                $dendaHilang = $buku->harga_buku;

                $totalItem = $dendaHilang;

            } else {

                $persen = 0;
                switch ($request->status) {
                    case 'kembali_rusak_ringan': $persen = $buku->persen_rusak_ringan / 100; break;
                    case 'kembali_rusak_sedang': $persen = $buku->persen_rusak_sedang / 100; break;
                    case 'kembali_rusak_berat':  $persen = $buku->persen_rusak_berat / 100; break;
                }

                $dendaKerusakan = $buku->harga_buku * $persen;
                $dendaHilang = 0;

                $totalItem = $dendaTelat + $dendaKerusakan;
            }
            $detail->update([
                'status'          => $request->status,
                'denda_telat'     => $dendaTelat,
                'denda_kerusakan' => $dendaKerusakan,
                'denda_hilang'    => $dendaHilang,
                'total_denda_item'=> $totalItem,
                'tgl_kembali'     => $tglSekarang,
            ]);
            if ($request->status === 'kembali_normal') {
                $buku->increment('stok_tersedia');

            } elseif (in_array($request->status, [
                'kembali_rusak_ringan',
                'kembali_rusak_sedang',
                'kembali_rusak_berat'
            ])) {
                $buku->increment('dalam_perbaikan');

            } elseif ($request->status === 'hilang') {
                $buku->decrement('stok_total');
            }
            $total = $transaksi->details()->sum('total_denda_item');

            $transaksi->update([
                'total_denda' => $total
            ]);
            $sisaDipinjam = $transaksi->details()
                ->where('status', 'dipinjam')
                ->count();

            if ($sisaDipinjam == 0) {
                $transaksi->update([
                    'status'        => 'kembali',
                    'diterima_oleh' => Auth::id(),
                    'status_denda'  => $total > 0 ? 'belum_bayar' : null,
                ]);
            } else {
                if ($total > 0) {
                    $transaksi->update([
                        'status_denda' => 'belum_bayar'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian diproses, status buku: ' . $request->status
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