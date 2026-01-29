<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DendaController extends Controller
{
    /**
     * List semua denda
     */
    public function index(Request $request)
    {
            $query = Denda::with([
            'transaksi.buku',
            'transaksi.user.siswa',
            'transaksi.user.staff'
        ])->latest();


        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        $denda = $query->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $denda
        ]);
    }

    /**
     * Tandai denda sudah dibayar
     */
    public function bayar($id)
    {
        $denda = Denda::findOrFail($id);

        if ($denda->status_pembayaran === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Denda sudah dibayar'
            ], 422);
        }

        $denda->update([
            'status_pembayaran' => 'lunas',
            'tgl_pembayaran' => now(),
            'operator_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Denda berhasil dibayar',
            'data' => $denda
        ]);
    }
}
