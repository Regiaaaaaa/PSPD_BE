<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Buku;
use App\Models\User;
use App\Notifications\NewBorrowingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with(['details.buku'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }
    public function store(Request $request)
    {
        $userId = Auth::id();

        if (Transaksi::where('user_id', $userId)
            ->where('status_denda', 'belum_bayar')
            ->where('total_denda', '>', 0)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Selesaikan pembayaran denda terlebih dahulu.'
            ], 422);
        }

        $request->validate([
            'books' => 'required|array|min:1|max:3',
            'books.*' => 'exists:buku,id',
            'tgl_deadline' => 'required|date|after_or_equal:today',
            'kepentingan' => 'nullable|string'
        ]);

        if (Transaksi::where('user_id', $userId)->whereIn('status', ['dipinjam', 'menunggu'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada buku yang belum dikembalikan.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'user_id' => $userId,
                'kepentingan' => $request->kepentingan,
                'tgl_deadline' => $request->tgl_deadline,
                'status' => 'menunggu',
                'status_denda' => null
            ]);

            foreach ($request->books as $bukuId) {
                $buku = Buku::findOrFail($bukuId);
                if ($buku->stok_tersedia < 1) throw new \Exception("Stok {$buku->judul} habis.");

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'buku_id' => $buku->id,
                    'status' => 'menunggu'
                ]);
                $buku->decrement('stok_tersedia');
            }

            foreach (User::where('role', 'operator')->get() as $operator) {
                $operator->notify(new NewBorrowingRequest($transaksi));
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Peminjaman berhasil diajukan.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    public function show($id)
    {
        $transaksi = Transaksi::with(['details.buku', 'disetujuiOleh', 'diterimaOleh', 'ditolakOleh'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $transaksi]);
    }

    
    public function cekDenda()
    {
        $userId = Auth::id();

        $transaksiDenda = Transaksi::where('user_id', $userId)
            ->where('status_denda', 'belum_bayar')
            ->where('total_denda', '>', 0)
            ->first();

        $transaksiTelat = Transaksi::where('user_id', $userId)
            ->where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->first();

        if ($transaksiDenda || $transaksiTelat) {
            return response()->json([
                'ada_denda' => true,
                'nominal' => $transaksiDenda ? $transaksiDenda->total_denda : ($transaksiTelat ? $transaksiTelat->denda_berjalan : 0),
                'message' => 'Anda memiliki denda yang harus diselesaikan.'
            ]);
        }

        return response()->json(['ada_denda' => false]);
    }

    public function cancel($id)
    {
        $transaksi = Transaksi::where('user_id', Auth::id())->where('status', 'menunggu')->findOrFail($id);

        DB::beginTransaction();
        try {
            $transaksi->update(['status' => 'dibatalkan']);
            foreach ($transaksi->details as $detail) {
                $detail->update(['status' => 'dibatalkan']);
                Buku::where('id', $detail->buku_id)->increment('stok_tersedia');
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pengajuan dibatalkan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan.'], 400);
        }
    }
    // Notifikasi
    public function getNotifications()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $user->notifications()->limit(10)->get()
        ]);
    }

    public function markAsRead($id)
    {
        $notif = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notif) {
            $notif->markAsRead();
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true
        ]);
    }
}