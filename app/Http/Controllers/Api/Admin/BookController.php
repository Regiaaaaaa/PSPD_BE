<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    // Daftar Buku
    public function index()
    {
        $buku = Buku::with('kategori')->get();

        return response()->json([
            'status' => true,
            'message' => 'List semua buku',
            'data' => $buku
        ]);
    }

    // Tambah Buku
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isbn'          => 'required|digits_between:10,13|unique:buku,isbn',
            'kategori_id'   => 'required|exists:kategori,id',
            'judul'         => 'required|string|max:255',
            'penulis'       => 'nullable|string|max:255',
            'penerbit'      => 'nullable|string|max:255',
            'tahun_terbit'  => 'nullable|digits:4',
            'stok_total'    => 'required|integer|min:0',
            'cover'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $buku = Buku::create([
            'isbn'             => $request->isbn,
            'kategori_id'      => $request->kategori_id,
            'judul'            => $request->judul,
            'penulis'          => $request->penulis,
            'penerbit'         => $request->penerbit,
            'tahun_terbit'     => $request->tahun_terbit,
            'stok_total'       => $request->stok_total,
            'stok_tersedia'    => $request->stok_total,
            'dalam_perbaikan'  => 0,
            'cover'            => $coverPath
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data' => $buku
        ], 201);
    }

    // Detail Buku
    public function show($id)
    {
        $buku = Buku::with('kategori')->find($id);

        if (!$buku) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $buku
        ]);
    }

    // Perbarui Buku
    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'isbn'             => 'required|digits_between:10,13|unique:buku,isbn,' . $buku->id,
            'kategori_id'      => 'required|exists:kategori,id',
            'judul'            => 'required|string|max:255',
            'penulis'          => 'nullable|string|max:255',
            'penerbit'         => 'nullable|string|max:255',
            'tahun_terbit'     => 'nullable|digits:4',
            'stok_total'       => 'sometimes|integer|min:' . $buku->stok_total,
            'dalam_perbaikan'  => 'sometimes|integer|min:0',
            'cover'            => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Logika Stok
        $stokTotal = $request->stok_total ?? $buku->stok_total;
        $perbaikan = $request->dalam_perbaikan ?? $buku->dalam_perbaikan;

        $dipinjam = $buku->transaksi()
            ->whereIn('status', ['disetujui', 'dipinjam'])
            ->sum('jumlah');

        $stokTersedia = $stokTotal - $perbaikan - $dipinjam;

        if ($stokTersedia < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Stok tidak mencukupi (perbaikan / peminjaman terlalu banyak)'
            ], 422);
        }

        if ($request->hasFile('cover')) {
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $buku->cover = $request->file('cover')->store('covers', 'public');
        }

        // Perbarui Buku
        $buku->update([
            'isbn'             => $request->isbn,
            'kategori_id'      => $request->kategori_id,
            'judul'            => $request->judul,
            'penulis'          => $request->penulis,
            'penerbit'         => $request->penerbit,
            'tahun_terbit'     => $request->tahun_terbit,
            'stok_total'       => $stokTotal,
            'stok_tersedia'    => $stokTersedia,
            'dalam_perbaikan'  => $perbaikan,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil diperbarui',
            'data' => $buku
        ]);
    }

    // Hapus Buku
    public function destroy($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        $buku->delete();

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil dihapus'
        ]);
    }
}