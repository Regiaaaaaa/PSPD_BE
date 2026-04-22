<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\User;
use App\Models\Transaksi;
use App\Models\Siswa;
use App\Models\Staff;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role == 'admin') return $this->adminDashboard();
        if ($user->role == 'operator') return $this->operatorDashboard();
        if ($user->role == 'staff' || $user->role == 'siswa') return $this->userDashboard();

        return response()->json(['message' => 'Dashboard tidak tersedia'], 403);
    }

    private function adminDashboard()
    {
        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $transaksiFilter = Transaksi::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        $dendaBerjalan = Transaksi::where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->with('details')
            ->get()
            ->sum('denda_berjalan');

        $dendaBelumLunas = Transaksi::where('status_denda', 'belum_bayar')
            ->where('total_denda', '>', 0)
            ->count();

        return response()->json([
            'total_buku'         => Buku::count(),
            'total_user'         => User::count(),
            'total_operator'     => User::where('role', 'operator')->count(),
            'total_siswa'        => Siswa::count(),
            'total_staff'        => Staff::count(),

            'denda_belum_lunas'  => $dendaBelumLunas,
            'denda_berjalan'     => $dendaBerjalan,

            'buku_stok_menipis'  => Buku::where('stok_tersedia', '<=', 3)->count(),

            'total_transaksi'    => (clone $transaksiFilter)->count(),
            'total_dipinjam'     => (clone $transaksiFilter)->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => (clone $transaksiFilter)->where('status', 'kembali')->count(),
            'total_menunggu'     => (clone $transaksiFilter)->where('status', 'menunggu')->count(),
            'total_ditolak'      => (clone $transaksiFilter)->where('status', 'ditolak')->count(),
            'total_dibatalkan'   => (clone $transaksiFilter)->where('status', 'dibatalkan')->count(),

            'filter' => [
                'bulan' => (int) $bulan,
                'tahun' => (int) $tahun,
            ],
        ]);
    }

    private function userDashboard()
    {
        $user  = auth()->user();
        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $transaksiFilter = Transaksi::where('user_id', $user->id)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        $dendaBerjalan = Transaksi::where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->with('details')
            ->get()
            ->sum('denda_berjalan');

        $dendaFilter = Transaksi::where('user_id', $user->id)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('total_denda', '>', 0);

        return response()->json([
            'pinjaman_aktif'       => (clone $transaksiFilter)->where('status', 'dipinjam')->count(),
            'dikembalikan'         => (clone $transaksiFilter)->where('status', 'kembali')->count(),
            'menunggu_persetujuan' => (clone $transaksiFilter)->where('status', 'menunggu')->count(),
            'dibatalkan'           => (clone $transaksiFilter)->where('status', 'dibatalkan')->count(),
            'ditolak'              => (clone $transaksiFilter)->where('status', 'ditolak')->count(),

            'total_transaksimu'    => (clone $transaksiFilter)->count(),

            'total_denda'          => (clone $dendaFilter)->count(),
            'denda_lunas'          => (clone $dendaFilter)->where('status_denda', 'lunas')->count(),
            'denda_belum_lunas'    => (clone $dendaFilter)->where('status_denda', 'belum_bayar')->count(),
            'denda_berjalan'       => $dendaBerjalan,

            'filter' => [
                'bulan' => (int) $bulan,
                'tahun' => (int) $tahun,
            ],
        ]);
    }

    private function operatorDashboard()
    {
        $bulan = request('bulan', now()->month);
        $tahun = request('tahun', now()->year);

        $transaksiFilter = Transaksi::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        $dendaBerjalan = Transaksi::where('status', 'dipinjam')
            ->where('tgl_deadline', '<', now())
            ->with('details')
            ->get()
            ->sum('denda_berjalan');

        $dendaFilter = Transaksi::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('total_denda', '>', 0);

        return response()->json([
            'menunggu_persetujuan'   => (clone $transaksiFilter)->where('status', 'menunggu')->count(),
            'sedang_dipinjam'        => (clone $transaksiFilter)->where('status', 'dipinjam')->count(),
            'pengembalian_bulan_ini' => (clone $transaksiFilter)->where('status', 'kembali')->count(),
            'total_ditolak'          => (clone $transaksiFilter)->where('status', 'ditolak')->count(),
            'total_dibatalkan'       => (clone $transaksiFilter)->where('status', 'dibatalkan')->count(),

            'total_transaksi'        => (clone $transaksiFilter)->count(),

            'total_denda'            => (clone $dendaFilter)->count(),
            'denda_belum_lunas'      => (clone $dendaFilter)->where('status_denda', 'belum_bayar')->count(),
            'denda_lunas'            => (clone $dendaFilter)->where('status_denda', 'lunas')->count(),
            'denda_berjalan'         => $dendaBerjalan,

            'filter' => [
                'bulan' => (int) $bulan,
                'tahun' => (int) $tahun,
            ],
        ]);
    }
}