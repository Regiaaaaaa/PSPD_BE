<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;

class MonitoringBukuController extends Controller
{
    public function index(Request $request)
    {
        $buku = Buku::with('kategori')
            ->orderBy('judul', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'isbn' => $item->isbn, 
                    'judul' => $item->judul,
                    'penulis' => $item->penulis,
                    'penerbit' => $item->penerbit,
                    'tahun_terbit' => $item->tahun_terbit,
                    'kategori' => [
                        'id' => $item->kategori->id ?? null,
                        'nama' => $item->kategori->nama_kategori ?? null,
                    ],
                    'stok_total' => $item->stok_total,
                    'stok_tersedia' => $item->stok_tersedia,
                    'dalam_perbaikan' => $item->dalam_perbaikan,
                    'stok_tidak_tersedia' =>
                        $item->stok_total
                        - $item->stok_tersedia
                        - $item->dalam_perbaikan,
                    'cover' => $item->cover,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring buku berhasil diambil',
            'data' => $buku
        ]);
    }
}