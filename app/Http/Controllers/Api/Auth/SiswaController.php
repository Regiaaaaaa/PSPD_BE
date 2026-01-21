<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    // Register Siswa
    public function register(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|string|unique:users,nomor_induk',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'kelas' => 'required|string',
        ]);

        $user = User::create([
            'nomor_induk' => $request->nomor_induk,
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => 'siswa',
            'kelas'       => $request->kelas,
        ]);

        return response()->json([
            'message' => 'Registrasi siswa berhasil',
            'data' => $user
        ], 201);
    }

    // Login Siswa
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)
                    ->where('role', 'siswa')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Hapus token lama
        $user->tokens()->delete();

        // Buat token 
        $token = $user->createToken('siswa-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
