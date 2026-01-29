<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        Buku::create([
            'kategori_id' => 1,
            'judul' => 'Pengantar Teknologi Informasi',
            'penulis' => 'Abdul Kadir',
            'penerbit' => 'Andi',
            'tahun_terbit' => 2020,
            'stok_tersedia' => 5,
        ]);

        Buku::create([
            'kategori_id' => 2,
            'judul' => 'Laravel untuk Pemula',
            'penulis' => 'Taylor Otwell',
            'penerbit' => 'Laravel Press',
            'tahun_terbit' => 2022,
            'stok_tersedia' => 8,
        ]);

        Buku::create([
            'kategori_id' => 2,
            'judul' => 'PHP OOP Lanjutan',
            'penulis' => 'Rizki Ramadhan',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2019,
            'stok_tersedia' => 6,
        ]);

        Buku::create([
            'kategori_id' => 3,
            'judul' => 'Laskar Pelangi',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2005,
            'stok_tersedia' => 7,
        ]);

        Buku::create([
            'kategori_id' => 3,
            'judul' => 'Bumi',
            'penulis' => 'Tere Liye',
            'penerbit' => 'Gramedia',
            'tahun_terbit' => 2014,
            'stok_tersedia' => 9,
        ]);

        Buku::create([
            'kategori_id' => 4,
            'judul' => 'Sejarah Indonesia Modern',
            'penulis' => 'M.C. Ricklefs',
            'penerbit' => 'Serambi',
            'tahun_terbit' => 2018,
            'stok_tersedia' => 4,
        ]);

        Buku::create([
            'kategori_id' => 4,
            'judul' => 'Sejarah Dunia',
            'penulis' => 'E.H. Gombrich',
            'penerbit' => 'Kompas',
            'tahun_terbit' => 2016,
            'stok_tersedia' => 5,
        ]);

        Buku::create([
            'kategori_id' => 5,
            'judul' => 'Matematika SMA Kelas X',
            'penulis' => 'Tim Edukasi',
            'penerbit' => 'Erlangga',
            'tahun_terbit' => 2020,
            'stok_tersedia' => 12,
        ]);

        Buku::create([
            'kategori_id' => 5,
            'judul' => 'Matematika SMA Kelas XI',
            'penulis' => 'Tim Edukasi',
            'penerbit' => 'Erlangga',
            'tahun_terbit' => 2021,
            'stok_tersedia' => 10,
        ]);

        Buku::create([
            'kategori_id' => 5,
            'judul' => 'Fisika SMA',
            'penulis' => 'Sukardi',
            'penerbit' => 'Yudhistira',
            'tahun_terbit' => 2019,
            'stok_tersedia' => 8,
        ]);

        Buku::create([
            'kategori_id' => 1,
            'judul' => 'Dasar Jaringan Komputer',
            'penulis' => 'Onno W. Purbo',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2017,
            'stok_tersedia' => 6,
        ]);

        Buku::create([
            'kategori_id' => 1,
            'judul' => 'Keamanan Sistem Informasi',
            'penulis' => 'Budi Rahardjo',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2018,
            'stok_tersedia' => 5,
        ]);

        Buku::create([
            'kategori_id' => 2,
            'judul' => 'JavaScript Modern',
            'penulis' => 'Evan You',
            'penerbit' => 'Frontend Press',
            'tahun_terbit' => 2021,
            'stok_tersedia' => 9,
        ]);

        Buku::create([
            'kategori_id' => 2,
            'judul' => 'React untuk Pemula',
            'penulis' => 'Dan Abramov',
            'penerbit' => 'Frontend Press',
            'tahun_terbit' => 2022,
            'stok_tersedia' => 7,
        ]);

        Buku::create([
            'kategori_id' => 3,
            'judul' => 'Negeri 5 Menara',
            'penulis' => 'Ahmad Fuadi',
            'penerbit' => 'Gramedia',
            'tahun_terbit' => 2009,
            'stok_tersedia' => 6,
        ]);
    }
}
