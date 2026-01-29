<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Teknologi'],
            ['nama_kategori' => 'Pemrograman'],
            ['nama_kategori' => 'Novel'],
            ['nama_kategori' => 'Sejarah'],
            ['nama_kategori' => 'Pendidikan'],
        ];

        foreach ($kategori as $item) {
            Kategori::create($item);
        }
    }
}
