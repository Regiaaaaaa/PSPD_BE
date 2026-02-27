<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    // Katalog Buku
    public function index(Request $request)
    {
        $buku = Buku::with('kategori')
            ->where('stok_tersedia', '>', 0)
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('judul', 'like', "%{$request->search}%")
                        ->orWhere('penulis', 'like', "%{$request->search}%")
                        ->orWhere('penerbit', 'like', "%{$request->search}%")
                        ->orWhere('isbn', 'like', "%{$request->search}%"); 
                });
            })

            ->when($request->tahun, fn ($q) =>
                $q->where('tahun_terbit', $request->tahun)
            )

            ->when($request->kategori_id, fn ($q) =>
                $q->where('kategori_id', $request->kategori_id)
            )

            ->when(
                $request->sort === 'terbaru',
                fn ($q) => $q->orderByDesc('tahun_terbit'),
                fn ($q) => $q->orderBy('judul')
            )

            ->get()
            ->map(function ($b) {
                return [
                    'id'             => $b->id,
                    'isbn'           => $b->isbn, 
                    'judul'          => $b->judul,
                    'penulis'        => $b->penulis,
                    'penerbit'       => $b->penerbit,
                    'tahun_terbit'   => $b->tahun_terbit,
                    'stok_tersedia'  => $b->stok_tersedia,
                    'cover'          => $b->cover,
                    'kategori'       => $b->kategori,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $buku
        ]);
    }

    // Detail Buku
    public function show($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'            => $buku->id,
                'isbn'          => $buku->isbn, 
                'judul'         => $buku->judul,
                'penulis'       => $buku->penulis,
                'penerbit'      => $buku->penerbit,
                'tahun_terbit'  => $buku->tahun_terbit,
                'stok_tersedia' => $buku->stok_tersedia,
                'cover'         => $buku->cover,
                'kategori'      => $buku->kategori,
            ]
        ]);
    }
}