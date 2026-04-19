<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\User;
use App\Notifications\NewBorrowingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // List Riwayat Peminjaman User
    public function index()
    {
        $transaksi = Transaksi::with([
            'details.buku',
            'details.denda'
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Cek Denda Belum Lunas
    private function getDendaAktif(int $userId): ?Denda
    {
        return Denda::with('transaksiDetail.buku')
            ->whereHas('transaksiDetail.transaksi', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('status_pembayaran', 'belum_lunas')
            ->first();
    }

    // Pinjam Buku
    public function store(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'books' => 'required|array|min:1|max:3',
            'books.*' => 'exists:buku,id',
            'tgl_deadline' => 'required|date|after_or_equal:today',
            'kepentingan' => 'nullable|string'
        ]);

        // Cek pinjaman aktif
        $masihAdaPinjaman = Transaksi::where('user_id', $userId)
            ->whereIn('status', ['dipinjam', 'menunggu'])
            ->exists();

        if ($masihAdaPinjaman) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada buku yang belum dikembalikan.'
            ], 422);
        }

        // Cek buku telat
        $adaBukuTelat = Transaksi::where('user_id', $userId)
            ->where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->whereHas('details', function ($q) {
                $q->where('status', 'dipinjam');
            })
            ->exists();

        if ($this->getDendaAktif($userId) || $adaBukuTelat) {
            return response()->json([
                'success' => false,
                'message' => 'Selesaikan denda atau kembalikan buku telat terlebih dahulu'
            ], 422);
        }

        DB::beginTransaction();

        try {

            // Buat transaksi utama
            $transaksi = Transaksi::create([
                'user_id' => $userId,
                'kepentingan' => $request->kepentingan,
                'tgl_deadline' => $request->tgl_deadline,
                'status' => 'menunggu'
            ]);

            foreach ($request->books as $bukuId) {

                $buku = Buku::find($bukuId);

                if (!$buku) {
                    throw new \Exception("Buku tidak ditemukan");
                }

                if ($buku->stok_tersedia < 1) {
                    throw new \Exception("Stok buku {$buku->judul} habis");
                }

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'buku_id' => $buku->id,
                    'status' => 'menunggu'
                ]);

                $buku->decrement('stok_tersedia');
            }

            // Notifikasi Operator
            $operators = User::where('role', 'operator')->get();

            $transaksi->load('details.buku', 'user');

            foreach ($operators as $operator) {
                $operator->notify(new NewBorrowingRequest($transaksi));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil diajukan',
                'data' => $transaksi->load('details.buku')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Detail Transaksi
    public function show($id)
    {
        $transaksi = Transaksi::with([
            'details.buku',
            'details.denda',
            'disetujuiOleh',
            'diterimaOleh',
            'ditolakOleh'
        ])
        ->where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Batalkan
    // Batalkan
public function cancel($id)
{
    $transaksi = Transaksi::where('user_id', Auth::id())
        ->where('status', 'menunggu')
        ->with('details')
        ->findOrFail($id);

    DB::beginTransaction();

    try {

        $transaksi->update([
            'status' => 'dibatalkan'
        ]);

        foreach ($transaksi->details as $detail) {

            // Update status detail
            $detail->update([
                'status' => 'dibatalkan'
            ]);

            // Kembalikan stok buku
            Buku::where('id', $detail->buku_id)
                ->increment('stok_tersedia');
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan dibatalkan'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Gagal membatalkan'
        ], 400);
    }
}

    // Cek Denda
    public function cekDenda()
    {
        $userId = Auth::id();

        $denda = $this->getDendaAktif($userId);

        $transaksiTelat = Transaksi::with('details.buku')
            ->where('user_id', $userId)
            ->where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->whereHas('details', function ($q) {
                $q->where('status', 'dipinjam');
            })
            ->first();

        // Denda tercatat
        if ($denda) {

            $judul = null;

            if ($denda->transaksiDetail && $denda->transaksiDetail->buku) {
                $judul = $denda->transaksiDetail->buku->judul;
            }

            return response()->json([
                'ada_denda' => true,
                'tipe' => 'denda_tercatat',
                'denda' => [
                    'id' => $denda->id,
                    'nominal' => $denda->nominal,
                    'judul_buku' => $judul,
                ],
            ]);
        }

        // Buku telat
        if ($transaksiTelat) {

            $judul = null;

            if ($transaksiTelat->details->count() > 0) {
                $detail = $transaksiTelat->details->first();

                if ($detail->buku) {
                    $judul = $detail->buku->judul;
                }
            }

            return response()->json([
                'ada_denda' => true,
                'tipe' => 'buku_telat',
                'denda' => [
                    'id' => null,
                    'nominal' => $transaksiTelat->denda_berjalan,
                    'judul_buku' => $judul,
                ],
            ]);
        }

        return response()->json([
            'ada_denda' => false
        ]);
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