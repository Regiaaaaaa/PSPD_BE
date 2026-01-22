<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    // Daftar buku
    public function index()
    {
        $books = Book::with('category')->get();

        return response()->json([
            'status' => true,
            'message' => 'List semua buku',
            'data' => $books
        ], 200);
    }

    // Buat buku
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id'   => 'required|exists:categories,id',
            'judul'         => 'required|string|max:255',
            'penulis'       => 'nullable|string|max:255',
            'penerbit'      => 'nullable|string|max:255',
            'tahun_terbit'  => 'nullable|digits:4',
            'stok'          => 'required|integer|min:0',
            'kondisi'       => 'required|in:bagus,rusak,perbaikan',
            'cover'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create([
            'category_id'  => $request->category_id,
            'judul'        => $request->judul,
            'penulis'      => $request->penulis,
            'penerbit'     => $request->penerbit,
            'tahun_terbit' => $request->tahun_terbit,
            'stok'         => $request->stok,
            'kondisi'      => $request->kondisi,
            'cover'        => $coverPath
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data' => $book
        ], 201);
    }

    // Daftar detail buku
    public function show($id)
    {
        $book = Book::with('category')->find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail buku',
            'data' => $book
        ], 200);
    }

    // Ubah data kategori
    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id'   => 'required|exists:categories,id',
            'judul'         => 'required|string|max:255',
            'penulis'       => 'nullable|string|max:255',
            'penerbit'      => 'nullable|string|max:255',
            'tahun_terbit'  => 'nullable|digits:4',
            'stok'          => 'required|integer|min:0',
            'kondisi'       => 'required|in:bagus,rusak,perbaikan',
            'cover'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle cover baru
        if ($request->hasFile('cover')) {
            if ($book->cover && Storage::disk('public')->exists($book->cover)) {
                Storage::disk('public')->delete($book->cover);
            }

            $book->cover = $request->file('cover')->store('covers', 'public');
        }

        $book->update([
            'category_id'  => $request->category_id,
            'judul'        => $request->judul,
            'penulis'      => $request->penulis,
            'penerbit'     => $request->penerbit,
            'tahun_terbit' => $request->tahun_terbit,
            'stok'         => $request->stok,
            'kondisi'      => $request->kondisi,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil diupdate',
            'data' => $book
        ], 200);
    }

    // Hapus buku
    public function destroy($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        // Hapus cover
        if ($book->cover && Storage::disk('public')->exists($book->cover)) {
            Storage::disk('public')->delete($book->cover);
        }

        $book->delete();

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil dihapus'
        ], 200);
    }
}
