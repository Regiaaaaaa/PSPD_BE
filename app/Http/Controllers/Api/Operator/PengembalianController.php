<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Denda;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    // List transaksi yang sedang di pinjam
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
                'message' => 'Transaksi tidak bisa diterima'
            ], 422);
        }

        // Update stok buku
        $buku = $transaksi->buku;
        $buku->stok_tersedia += $transaksi->jumlah;
        $buku->save();

        // Update transaksi
        $transaksi->update([
            'tgl_kembali' => now(),
            'diterima_oleh' => Auth::id(),
            'status' => 'kembali',
        ]);

        // Hitung keterlambatan
        $tglDeadline = Carbon::parse($transaksi->tgl_deadline);
        $tglKembali = Carbon::parse($transaksi->tgl_kembali);
        $selisihHari = $tglKembali->diffInDays($tglDeadline);

        $message = 'Pengembalian berhasil diterima';

        if ($tglKembali->greaterThan($tglDeadline)) {
            $nominal = $selisihHari * 1000;
            Denda::create([
                'transaksi_id' => $transaksi->id,
                'nominal' => $nominal,
                'status_pembayaran' => 'belum_lunas',
            ]);
            $message .= ', terlambat ' . $selisihHari . ' hari, denda Rp ' . number_format($nominal);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $transaksi
        ]);
    }
}
