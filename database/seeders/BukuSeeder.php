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
            [1, '9780000000001', 'The Innovators', 'Walter Isaacson', 'Simon & Schuster', 2014, 10],
            [1, '9780000000002', 'Artificial Intelligence Basics', 'Tom Taulli', 'Apress', 2019, 8],
            [1, '9780000000003', 'Life 3.0', 'Max Tegmark', 'Knopf', 2017, 7],
            [1, '9780000000004', 'The Age of AI', 'Henry A. Kissinger', 'Little, Brown', 2021, 6],
            [1, '9780000000005', 'Clean Architecture', 'Robert C. Martin', 'Pearson', 2017, 9],

            // Pemrograman 
            [2, '9780000000006', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 12],
            [2, '9780000000007', 'The Pragmatic Programmer', 'Andrew Hunt', 'Addison-Wesley', 1999, 10],
            [2, '9780000000008', 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 8],
            [2, '9780000000009', 'Laravel: Up & Running', 'Matt Stauffer', 'O’Reilly Media', 2019, 7],
            [2, '9780000000010', 'You Don’t Know JS', 'Kyle Simpson', 'O’Reilly Media', 2015, 9],

            // Novel 
            [3, '9780000000011', 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 14],
            [3, '9780000000012', 'Bumi', 'Tere Liye', 'Gramedia', 2014, 12],
            [3, '9780000000013', 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia', 2009, 10],
            [3, '9780000000014', 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 2004, 8],
            [3, '9780000000015', 'Harry Potter and the Philosopher’s Stone', 'J.K. Rowling', 'Bloomsbury', 1997, 6],

            // Sejarah 
            [4, '9780000000016', 'Sapiens', 'Yuval Noah Harari', 'Harper', 2011, 10],
            [4, '9780000000017', 'Homo Deus', 'Yuval Noah Harari', 'Harper', 2015, 8],
            [4, '9780000000018', 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 7],
            [4, '9780000000019', 'The Silk Roads', 'Peter Frankopan', 'Bloomsbury', 2015, 6],
            [4, '9780000000020', 'Sejarah Dunia', 'E.H. Gombrich', 'Kompas', 2016, 9],

            // Pendidikan 
            [5, '9780000000021', 'Atomic Habits', 'James Clear', 'Avery', 2018, 15],
            [5, '9780000000022', 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 1989, 12],
            [5, '9780000000023', 'Think and Grow Rich', 'Napoleon Hill', 'The Ralston Society', 1937, 10],
            [5, '9780000000024', 'Mindset', 'Carol S. Dweck', 'Random House', 2006, 9],
            [5, '9780000000025', 'Deep Work', 'Cal Newport', 'Grand Central Publishing', 2016, 8],
            [5, '9780000000026', 'Grit', 'Angela Duckworth', 'Scribner', 2016, 7],
            [5, '9780000000027', 'How to Win Friends & Influence People', 'Dale Carnegie', 'Simon & Schuster', 1936, 10],
            [5, '9780000000028', 'The Power of Habit', 'Charles Duhigg', 'Random House', 2012, 11],
            [5, '9780000000029', 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Plata Publishing', 1997, 9],
            [5, '9780000000030', 'Start With Why', 'Simon Sinek', 'Portfolio', 2009, 8],
        ];

        foreach ($data as $buku) {
            Buku::create([
                'kategori_id'     => $buku[0],
                'isbn'            => $buku[1], 
                'judul'           => $buku[2],
                'penulis'         => $buku[3],
                'penerbit'        => $buku[4],
                'tahun_terbit'    => $buku[5],
                'stok_total'      => $buku[6],
                'stok_tersedia'   => $buku[6],
                'dalam_perbaikan' => 0,
            ]);
        }
    }
}