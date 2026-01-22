<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KelolaUserController extends Controller
{
    // Daftar user 
    public function index()
    {
        $users = User::whereIn('role', ['operator', 'staff', 'siswa'])->get();

        return response()->json([
            'message' => 'List user berhasil diambil',
            'data' => $users
        ]);
    }

    // Daftar detail user
    public function show($id)
    {
        $user = User::whereIn('role', ['operator', 'staff', 'siswa'])
                    ->findOrFail($id);

        return response()->json([
            'message' => 'Detail user',
            'data' => $user
        ]);
    }

    // Buat akun operator
    public function createOperator(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'operator',
        ]);

        return response()->json([
            'message' => 'Akun operator berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Buat akun Staff
    public function createStaff(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|string|unique:users,nomor_induk',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'jabatan' => 'required|string'
        ]);

        $user = User::create([
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'jabatan' => $request->jabatan
        ]);

        return response()->json([
            'message' => 'Akun staff berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Buat akun siswa
    public function createSiswa(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|string|unique:users,nomor_induk',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'kelas' => 'required|string'
        ]);

        $user = User::create([
            'nomor_induk' => $request->nomor_induk,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'kelas' => $request->kelas
        ]);

        return response()->json([
            'message' => 'Akun siswa berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Ubah data user
    public function update(Request $request, $id)
    {
        $user = User::whereIn('role', ['operator', 'staff', 'siswa'])
                    ->findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'kelas' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'nomor_induk' => 'nullable|string|unique:users,nomor_induk,' . $user->id
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Khusus siswa
        if ($user->role == 'siswa') {
            $user->kelas = $request->kelas;
            $user->nomor_induk = $request->nomor_induk;
        }

        // Khusus staff
        if ($user->role == 'staff') {
            $user->jabatan = $request->jabatan;
        }

        $user->save();

        return response()->json([
            'message' => 'Data user berhasil diupdate',
            'data' => $user
        ]);
    }

    // Hapus user
    public function destroy($id)
    {
        $user = User::whereIn('role', ['operator', 'staff', 'siswa'])
                    ->findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }
}
