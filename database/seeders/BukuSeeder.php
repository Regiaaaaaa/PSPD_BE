<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [[1,2], '9780000000001', 'The Innovators', 'Walter Isaacson', 'Simon & Schuster', 2014, 10, 150000],
            [[1,2], '9780000000002', 'Artificial Intelligence Basics', 'Tom Taulli', 'Apress', 2019, 8, 200000],
            [[2], '9780000000006', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 12, 175000],
            [[3], '9780000000011', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 14, 85000],
            [[4,5], '9780000000016', 'Sapiens', 'Yuval Noah Harari', 'Harper', 2011, 10, 130000],
        ];

        foreach ($data as $item) {
            $buku = Buku::create([
                'isbn'            => $item[1], 
                'judul'           => $item[2],
                'penulis'         => $item[3],
                'penerbit'        => $item[4],
                'tahun_terbit'    => $item[5],
                'stok_total'      => $item[6],
                'stok_tersedia'   => $item[6],
                'dalam_perbaikan' => 0,
                'harga_buku'           => $item[7], // Ini kolom harga yang baru ditambah
            ]);
            
            $buku->kategori()->attach($item[0]);
        }
    }
}