<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
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
        $transaksi = Transaksi::with(['buku', 'denda'])
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
        return Denda::whereHas('transaksi', fn($q) => $q->where('user_id', $userId))
            ->where('status_pembayaran', 'belum_lunas')
            ->first();
    }

    // Pinjam Buku
    public function store(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'books' => 'required|array|min:1',
            'books.*.buku_id' => 'required|exists:buku,id', // pastiin nama tabel 'bukus' atau 'buku' sesuai migrasi
            'books.*.tgl_deadline' => 'required|date|after_or_equal:today',
            'books.*.kepentingan' => 'nullable|string',
        ]);

        $adaBukuTelat = Transaksi::where('user_id', $userId)
            ->whereIn('status', ['dipinjam', 'menunggu'])
            ->whereNull('tgl_kembali')
            ->where('tgl_deadline', '<', now()->toDateString())
            ->exists();

        if ($this->getDendaAktif($userId) || $adaBukuTelat) {
            return response()->json([
                'success' => false,
                'message' => 'Gak bisa pinjam dulu, Bro! Selesaikan urusan denda atau balikin buku yang telat.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $createdTransactions = [];

            foreach ($request->books as $item) {
                $buku = Buku::findOrFail($item['buku_id']);

                if ($buku->stok_tersedia < 1) {
                    throw new \Exception("Maaf, stok buku '{$buku->judul}' habis!");
                }
                $isPinjam = Transaksi::where('user_id', $userId)
                    ->where('buku_id', $buku->id)
                    ->whereIn('status', ['dipinjam', 'menunggu'])
                    ->whereNull('tgl_kembali')
                    ->exists();

                if ($isPinjam) {
                    throw new \Exception("Buku '{$buku->judul}' masih dalam status pinjam/menunggu.");
                }

                $transaksi = Transaksi::create([
                    'user_id'      => $userId,
                    'buku_id'      => $buku->id,
                    'kepentingan'  => $item['kepentingan'] ?? null,
                    'tgl_deadline' => $item['tgl_deadline'],
                    'status'       => 'menunggu',
                ]);

                $buku->decrement('stok_tersedia');
                $transaksi->load(['user', 'buku']);
                $createdTransactions[] = $transaksi;
            }

            // Trigger Notifikasi
            $operators = User::where('role', 'operator')->get();
            foreach ($operators as $operator) {
                foreach ($createdTransactions as $t) {
                    $operator->notify(new NewBorrowingRequest($t));
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil diajukan! Notifikasi sudah dikirim ke Operator.',
                'data'    => $createdTransactions
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // Detail Transaksi
    public function show($id)
    {
        $transaksi = Transaksi::with(['buku', 'denda', 'disetujuiOleh', 'diterimaOleh', 'ditolakOleh'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    // Batalkan Pengajuan
    public function cancel($id)
    {
        $transaksi = Transaksi::where('user_id', Auth::id())
            ->where('status', 'menunggu')
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            $transaksi->update(['status' => 'dibatalkan']);
            Buku::where('id', $transaksi->buku_id)->increment('stok_tersedia');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan peminjaman berhasil dibatalkan dan stok dikembalikan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan.'], 400);
        }
    }

    // Cek Denda & Keterlambatan
    public function cekDenda()
    {
        $userId = Auth::id();
        $denda = $this->getDendaAktif($userId);

        $transaksiTelat = Transaksi::with('buku')
            ->where('user_id', $userId)
            ->whereIn('status', ['dipinjam', 'menunggu'])
            ->whereNull('tgl_kembali')
            ->where('tgl_deadline', '<', now()->toDateString())
            ->first();

        if ($denda) {
            return response()->json([
                'ada_denda'    => true,
                'tipe'         => 'denda_tercatat',
                'denda'        => [
                    'id'         => $denda->id,
                    'nominal'    => $denda->nominal,
                    'judul_buku' => $denda->transaksi?->buku?->judul,
                ],
            ]);
        }

        if ($transaksiTelat) {
            return response()->json([
                'ada_denda'    => true,
                'tipe'         => 'buku_telat',
                'denda'        => [
                    'id'         => null,
                    'nominal'    => $transaksiTelat->denda_berjalan, // pastiin ada attribute ini di model Transaksi
                    'judul_buku' => $transaksiTelat->buku?->judul,
                ],
            ]);
        }

        return response()->json(['ada_denda' => false]);
    }

    // Ambil Notifikasi
    public function getNotifications()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user->notifications()->limit(10)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications
        ]);
    }

    // Tandai Baca
    public function markAsRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notifikasi dibaca']);
        }

        return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);
    }

    // Tandai Semua Baca
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true, 'message' => 'Semua ditandai dibaca']);
    } 
}