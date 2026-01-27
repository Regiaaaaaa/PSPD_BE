<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // Daftar kategori
    public function index()
    {
        $kategori = Kategori::all();

        return response()->json([
            'status' => true,
            'message' => 'List semua kategori',
            'data' => $kategori
        ], 200);
    }

    // Buat kategori
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $kategori = Kategori::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }

    // Daftar detail kategori
    public function show($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail kategori',
            'data' => $kategori
        ], 200);
    }

    // Ubah data kategori
    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $kategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil diupdate',
            'data' => $kategori
        ], 200);
    }

    // Hapus kategori
    public function destroy($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $kategori->delete();

        return response()->json([
            'status' => true,
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}