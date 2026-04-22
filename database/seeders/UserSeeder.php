<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin Perpus',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Operator
        User::create([
            'name' => 'Operator Perpus',
            'email' => 'o1@gmail.com',
            'password' => Hash::make('okegas123'),
            'role' => 'operator',
        ]);

        // Siswa
        $siswaUser = User::create([
            'name' => 'Siswa Demo',
            'email' => 's@gmail.com',
            'password' => Hash::make('okegas123'),
            'role' => 'siswa',
        ]);

        Siswa::create([
            'user_id' => $siswaUser->id,
            'nomor_induk_siswa' => '1234567890',
            'tingkat' => 'XI',
            'jurusan' => 'RPL',
            'kelas' => 1,
        ]);

        // Staff
        $staffUser = User::create([
            'name' => 'Staff Perpus',
            'email' => 'st@gmail.com',
            'password' => Hash::make('okegas123'),
            'role' => 'staff',
        ]);

        Staff::create([
            'user_id' => $staffUser->id,
            'nomor_induk_pegawai' => '123456789012345678',
            'jabatan' => 'Staff Perpustakaan',
        ]);
    }
}