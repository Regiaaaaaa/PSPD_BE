<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Imports\BooksImport; 
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    public function index()
    {
        $buku = Buku::with('kategori')->get();

        return response()->json([
            'status' => true,
            'message' => 'List semua buku',
            'data' => $buku
        ]);
    }
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'isbn'          => 'required|digits_between:10,13|unique:buku,isbn',
        'kategori_id'   => 'required|array',
        'kategori_id.*' => 'exists:kategori,id',
        'judul'         => 'required|string|max:255',
        'penulis'       => 'nullable|string|max:255',
        'penerbit'      => 'nullable|string|max:255',
        'tahun_terbit'  => 'nullable|digits:4',
        'stok_total'    => 'required|integer|min:0',
        'harga_buku'    => 'nullable|numeric|min:0',
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
        'judul'            => $request->judul,
        'penulis'          => $request->penulis,
        'penerbit'         => $request->penerbit,
        'tahun_terbit'     => $request->tahun_terbit,
        'stok_total'       => $request->stok_total,
        'stok_tersedia'    => $request->stok_total,
        'dalam_perbaikan'  => 0,
        'harga_buku'      => $request->harga_buku ?? 0,
        'cover'            => $coverPath
    ]);
    $buku->kategori()->sync($request->kategori_id);

    return response()->json([
        'status' => true,
        'message' => 'Buku berhasil ditambahkan',
        'data' => $buku->load('kategori')
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

    $dipinjam = DetailTransaksi::where('buku_id', $buku->id)
        ->whereIn('status', ['menunggu', 'dipinjam'])
        ->count();

    $perbaikan = $request->dalam_perbaikan ?? $buku->dalam_perbaikan;
    $minStok = $dipinjam + $perbaikan;

    $validator = Validator::make($request->all(), [
        'isbn'            => 'required|digits_between:10,13|unique:buku,isbn,' . $buku->id,
        'kategori_id'     => 'required|array',
        'kategori_id.*'   => 'exists:kategori,id',
        'judul'           => 'required|string|max:255',
        'penulis'         => 'nullable|string|max:255',
        'penerbit'        => 'nullable|string|max:255',
        'tahun_terbit'    => 'nullable|digits:4',
        'stok_total'      => 'sometimes|integer|min:' . $minStok,
        'dalam_perbaikan' => 'sometimes|integer|min:0',
        'harga_buku'      => 'nullable|numeric|min:0', 
        'cover'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $stokTotal     = $request->stok_total ?? $buku->stok_total;
    $stokPerbaikan = $request->dalam_perbaikan ?? $buku->dalam_perbaikan;
    $stokTersedia  = $stokTotal - $stokPerbaikan - $dipinjam;

    if ($stokTersedia < 0) {
        return response()->json([
            'status' => false,
            'message' => 'Stok tidak mencukupi'
        ], 422);
    }

    if ($request->hasFile('cover')) {
        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        $buku->cover = $request->file('cover')->store('covers', 'public');
    }

    $buku->update([
        'isbn'            => $request->isbn,
        'judul'           => $request->judul,
        'penulis'         => $request->penulis,
        'penerbit'        => $request->penerbit,
        'tahun_terbit'    => $request->tahun_terbit,
        'stok_total'      => $stokTotal,
        'stok_tersedia'   => $stokTersedia,
        'dalam_perbaikan' => $stokPerbaikan,
        'harga_buku'      => $request->harga_buku ?? $buku->harga_buku,
    ]);

    // 🔥 UPDATE PIVOT
    $buku->kategori()->sync($request->kategori_id);

    return response()->json([
        'status' => true,
        'message' => 'Buku berhasil diperbarui',
        'data'    => $buku->load('kategori')
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
    public function downloadTemplate()
    {
        $path = storage_path('app/public/templates/template_buku.xlsx');
        
        if (!file_exists($path)) {
            return response()->json(['status' => false, 'message' => 'Template tidak ditemukan'], 404);
        }

        return response()->download($path);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Excel::import(new BooksImport, $request->file('file'));
            return response()->json(['status' => true, 'message' => 'Data buku berhasil diimport!'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Gagal import: ' . $e->getMessage()], 400);
        }
    }
}