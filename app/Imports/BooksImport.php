<?php

namespace App\Imports;

use App\Models\Buku;
use App\Models\Kategori; 
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow; 

class BooksImport implements ToModel, WithValidation, SkipsEmptyRows, WithHeadingRow
{
    public function model(array $row)
    {
        $buku = Buku::create([
            'isbn'            => $row['isbn'],
            'judul'           => $row['judul'],
            'penulis'         => $row['penulis'],
            'penerbit'        => $row['penerbit'],
            'tahun_terbit'    => $row['tahun'],
            'stok_total'      => $row['stok'],
            'stok_tersedia'   => $row['stok'],
            'dalam_perbaikan' => 0,
            'harga_buku'      => $row['harga_buku'] ?? 0,  
        ]);

        if (!empty($row['kategori'])) {
            $namaKategoris = array_map('trim', explode(',', $row['kategori']));
            $kategoriIds = Kategori::whereIn('nama_kategori', $namaKategoris)->pluck('id');
            $buku->kategori()->sync($kategoriIds);
        }

        return $buku;
    }

    public function rules(): array
    {
        return [
            'isbn'      => 'required|digits_between:10,13|unique:buku,isbn',
            'judul'     => 'required|string|max:255',
            'stok'      => 'required|integer|min:0',
            'kategori'  => 'required',
            'harga_buku' => 'nullable|numeric|min:0',  
        ];
    }
    }