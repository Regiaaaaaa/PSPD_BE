<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    /**
     * Katalog buku yang bisa dipinjam
     */
    public function index(Request $request)
    {
        $buku = Buku::with('kategori')
            // hanya buku yang bisa dipinjam
            ->where('stok_tersedia', '>', 0)

            // 🔍 search judul / penulis
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('judul', 'like', '%' . $request->search . '%')
                        ->orWhere('penulis', 'like', '%' . $request->search . '%')
                        ->orWhere('penerbit', 'like', '%' . $request->search . '%');
                });
            })

            // 📅 filter tahun terbit
            ->when($request->tahun, fn ($q) =>
                $q->where('tahun_terbit', $request->tahun)
            )

            // 📂 filter kategori
            ->when($request->kategori_id, fn ($q) =>
                $q->where('kategori_id', $request->kategori_id)
            )

            // 📦 sorting
            ->when($request->sort === 'terbaru', fn ($q) =>
                $q->orderByDesc('tahun_terbit')
            , fn ($q) =>
                $q->orderBy('judul')
            )

            ->get();

        return response()->json([
            'success' => true,
            'data' => $buku
        ]);
    }

    /**
     * Detail buku
     */
    public function show($id)
    {
        $buku = Buku::with('kategori')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $buku
        ]);
    }
}
