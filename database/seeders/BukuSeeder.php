<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [

            // Tekhnologi
            [1, 'The Innovators', 'Walter Isaacson', 'Simon & Schuster', 2014, 10],
            [1, 'Artificial Intelligence Basics', 'Tom Taulli', 'Apress', 2019, 8],
            [1, 'Life 3.0', 'Max Tegmark', 'Knopf', 2017, 7],
            [1, 'The Age of AI', 'Henry A. Kissinger', 'Little, Brown', 2021, 6],
            [1, 'Clean Architecture', 'Robert C. Martin', 'Pearson', 2017, 9],

            // Pemomgraman
            [2, 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 12],
            [2, 'The Pragmatic Programmer', 'Andrew Hunt', 'Addison-Wesley', 1999, 10],
            [2, 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 8],
            [2, 'Laravel: Up & Running', 'Matt Stauffer', 'O’Reilly Media', 2019, 7],
            [2, 'You Don’t Know JS', 'Kyle Simpson', 'O’Reilly Media', 2015, 9],

            // Novel
            [3, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 14],
            [3, 'Bumi', 'Tere Liye', 'Gramedia', 2014, 12],
            [3, 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia', 2009, 10],
            [3, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 2004, 8],
            [3, 'Harry Potter and the Philosopher’s Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 6],

            // Sejarah
            [4, 'Sapiens', 'Yuval Noah Harari', 'Harper', 2011, 10],
            [4, 'Homo Deus', 'Yuval Noah Harari', 'Harper', 2015, 8],
            [4, 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 7],
            [4, 'The Silk Roads', 'Peter Frankopan', 'Bloomsbury', 2015, 6],
            [4, 'Sejarah Dunia', 'E.H. Gombrich', 'Kompas', 2016, 9],

            // Pendidikan
            [5, 'Atomic Habits', 'James Clear', 'Avery', 2018, 15],
            [5, 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 1989, 12],
            [5, 'Think and Grow Rich', 'Napoleon Hill', 'The Ralston Society', 1937, 10],
            [5, 'Mindset', 'Carol S. Dweck', 'Random House', 2006, 9],
            [5, 'Deep Work', 'Cal Newport', 'Grand Central Publishing', 2016, 8],
            [5, 'Grit', 'Angela Duckworth', 'Scribner', 2016, 7],
            [5, 'How to Win Friends & Influence People', 'Dale Carnegie', 'Simon & Schuster', 1936, 10],
            [5, 'The Power of Habit', 'Charles Duhigg', 'Random House', 2012, 11],
            [5, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Plata Publishing', 1997, 9],
            [5, 'Start With Why', 'Simon Sinek', 'Portfolio', 2009, 8],
        ];

        foreach ($data as $buku) {
            Buku::create([
                'kategori_id'     => $buku[0],
                'judul'           => $buku[1],
                'penulis'         => $buku[2],
                'penerbit'        => $buku[3],
                'tahun_terbit'    => $buku[4],
                'stok_total'      => $buku[5],
                'stok_tersedia'   => $buku[5],
                'dalam_perbaikan' => 0,
            ]);
        }
    }
}
