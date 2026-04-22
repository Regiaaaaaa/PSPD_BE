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
    public function index()
    {
        $transaksi = Transaksi::with(['details.buku', 'user.siswa', 'user.staff'])
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        return response()->json([
            'success' => true, 
            'data' => $transaksi
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'tgl_deadline' => 'nullable|date|after_or_equal:today',
            'pesan_diterima' => 'nullable|string|max:255'
        ]);

        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status transaksi tidak valid'], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status' => 'dipinjam',
                'tgl_pinjam' => now(),
                'tgl_deadline' => $request->tgl_deadline ?? $transaksi->tgl_deadline,
                'pesan_diterima' => $request->pesan_diterima,
                'disetujui_oleh' => Auth::id(),
            ]);

            $transaksi->details()->update(['status' => 'dipinjam']);

            $transaksi->user->notify(new TransaksiNotification($transaksi));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Peminjaman disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal approve: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['pesan_ditolak' => 'required|string|max:255']);
        
        $transaksi = Transaksi::with('details')->findOrFail($id);

        if ($transaksi->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status transaksi tidak valid'], 422);
        }

        DB::beginTransaction();
        try {
            $transaksi->update([
                'status' => 'ditolak',
                'pesan_ditolak' => $request->pesan_ditolak,
                'ditolak_oleh' => Auth::id(), 
            ]);

            foreach ($transaksi->details as $detail) {
                $detail->update(['status' => 'ditolak']);
                Buku::where('id', $detail->buku_id)->increment('stok_tersedia');
            }

            $transaksi->user->notify(new TransaksiNotification($transaksi));

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pengajuan ditolak & stok dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal reject: ' . $e->getMessage()], 500);
        }
    }

    // List Notifikasi
    public function getNotifications()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $user->notifications()->limit(10)->get()
        ]);
    }

    // Tandai Dibaca
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi dibaca'
        ]);
    }

    // Tandai Semua
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi dibaca'
        ]);
    }
}