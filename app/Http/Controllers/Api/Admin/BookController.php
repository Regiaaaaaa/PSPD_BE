<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Kategori; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    // Daftar buku
    public function index()
    {
        $buku = Buku::with('kategori')->get();

        return response()->json([
            'status' => true,
            'message' => 'List semua buku',
            'data' => $buku
        ], 200);
    }

    // Buat buku
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id'   => 'required|exists:kategori,id', 
            'judul'         => 'required|string|max:255',
            'penulis'       => 'nullable|string|max:255',
            'penerbit'      => 'nullable|string|max:255',
            'tahun_terbit'  => 'nullable|digits:4',
            'stok_tersedia' => 'required|integer|min:0', 
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

        $buku = Buku::create([
            'kategori_id'    => $request->kategori_id,
            'judul'          => $request->judul,
            'penulis'        => $request->penulis,
            'penerbit'       => $request->penerbit,
            'tahun_terbit'   => $request->tahun_terbit,
            'stok_tersedia'  => $request->stok_tersedia,
            'cover'          => $coverPath
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil ditambahkan',
            'data' => $buku
        ], 201);
    }

    // Daftar detail buku
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
            'message' => 'Detail buku',
            'data' => $buku
        ], 200);
    }

    // Ubah data buku
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
            'kategori_id'     => 'required|exists:kategori,id',
            'judul'           => 'required|string|max:255',
            'penulis'         => 'nullable|string|max:255',
            'penerbit'        => 'nullable|string|max:255',
            'tahun_terbit'    => 'nullable|digits:4',
            'stok_tersedia'   => 'sometimes|integer|min:0', // Jadi sometimes agar fleksibel
            'dalam_perbaikan' => 'sometimes|integer|min:0',
            'cover'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // --- LOGIC PERHITUNGAN STOK (DELTA/SELISIH) ---
        $perbaikanLama = $buku->dalam_perbaikan;
        $perbaikanBaru = $request->has('dalam_perbaikan') ? $request->dalam_perbaikan : $perbaikanLama;
        
        // Hitung selisih perubahan buku rusak
        $selisih = $perbaikanLama - $perbaikanBaru;

        // Tentukan stok tersedia akhir
        if ($request->has('stok_tersedia')) {
            // Jika admin input stok_tersedia (misal beli buku baru), pakai inputan itu
            $stokAkhir = $request->stok_tersedia - $perbaikanBaru;
        } else {
            // Jika tidak input stok (cuma update perbaikan), pakai logic selisih otomatis
            $stokAkhir = $buku->stok_tersedia + $selisih;
        }

        // Proteksi agar tidak minus
        if ($stokAkhir < 0) {
            return response()->json([
                'status' => false,
                'message' => 'Stok tersedia tidak mencukupi untuk jumlah perbaikan ini!',
            ], 422);
        }

        // --- HANDLE COVER ---
        if ($request->hasFile('cover')) {
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $buku->cover = $request->file('cover')->store('covers', 'public');
        }

        // --- PROSES UPDATE ---
        $buku->update([
            'kategori_id'     => $request->kategori_id,
            'judul'           => $request->judul,
            'penulis'         => $request->penulis,
            'penerbit'        => $request->penerbit,
            'tahun_terbit'    => $request->tahun_terbit,
            'stok_tersedia'   => $stokAkhir,
            'dalam_perbaikan' => $perbaikanBaru,      
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Buku berhasil diupdate',
            'data' => $buku
        ], 200);
    }

    // Hapus buku
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
        ], 200);
    }
}