<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Buku;
use App\Notifications\TransaksiNotification; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    // Daftar pengajuan Menunggu
    public function index()
    {
        $transaksi = Transaksi::with(['buku', 'user.siswa', 'user.staff'])
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $transaksi]);
    }

    // Approve Peminjaman
    public function approve($id)
    {
        $transaksi = Transaksi::with(['buku', 'user'])->findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah diverifikasi'], 422);
        }

        $transaksi->update([
            'status' => 'dipinjam',
            'tgl_pinjam' => now(),
            'disetujui_oleh' => Auth::id(),
        ]);

        $transaksi->user->notify(new TransaksiNotification($transaksi));

        return response()->json(['success' => true, 'message' => 'Berhasil disetujui', 'data' => $transaksi]);
    }

    // Reject Peminjaman
    public function reject(Request $request, $id)
    {
        $request->validate(['pesan_ditolak' => 'required|string|max:255']);
        
        $transaksi = Transaksi::with(['buku', 'user'])->findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Sudah diverifikasi'], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status' => 'ditolak',
                'pesan_ditolak' => $request->pesan_ditolak,
                'ditolak_oleh' => Auth::id(), 
            ]);

            Buku::where('id', $transaksi->buku_id)->increment('stok_tersedia');
            $transaksi->user->notify(new TransaksiNotification($transaksi));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Berhasil ditolak & stok kembali.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal reject.'], 400);
        }
    }

    // List Notifikasi
    public function getNotifications()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $user->notifications()->limit(10)->get()
        ]);
    }

    // Tandai Di Baca
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Notifikasi dibaca']);
    }

    // Tandai Semua Notif
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true, 'message' => 'Semua notifikasi dibaca']);
    }
}