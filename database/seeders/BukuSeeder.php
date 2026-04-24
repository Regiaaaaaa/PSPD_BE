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
            [[2], '9780000000003', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 12, 175000],
            [[2], '9780000000004', 'Refactoring', 'Martin Fowler', 'Addison-Wesley', 1999, 7, 180000],
            [[1,2], '9780000000005', 'The Pragmatic Programmer', 'Andrew Hunt', 'Addison-Wesley', 1999, 9, 170000],

            [[3], '9780000000006', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 14, 85000],
            [[3,5], '9780000000007', 'Bumi', 'Tere Liye', 'Gramedia', 2014, 10, 95000],
            [[3], '9780000000008', 'Dilan 1990', 'Pidi Baiq', 'Pastel Books', 2014, 11, 90000],

            [[4,5], '9780000000009', 'Sapiens', 'Yuval Noah Harari', 'Harper', 2011, 10, 130000],
            [[4], '9780000000010', 'Sejarah Dunia', 'H.G. Wells', 'Gramedia', 1920, 6, 120000],
            [[4,1], '9780000000011', 'Guns, Germs, and Steel', 'Jared Diamond', 'W.W. Norton', 1997, 8, 140000],

            [[5], '9780000000012', 'Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 13, 98000],
            [[5,3], '9780000000013', 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia', 2009, 9, 92000],
            [[1,5], '9780000000014', 'Deep Learning', 'Ian Goodfellow', 'MIT Press', 2016, 5, 220000],
            [[2,5], '9780000000015', 'Laravel Up & Running', 'Matt Stauffer', "O'Reilly Media", 2019, 7, 185000],
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
                'harga_buku'      => $item[7], 
            ]);
            
            $buku->kategori()->attach($item[0]);
        }
    }
}