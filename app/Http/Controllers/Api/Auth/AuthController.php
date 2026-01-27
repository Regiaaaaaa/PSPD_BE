<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with(['siswa', 'staff'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,

                // data relasi
                'siswa' => $user->siswa,
                'staff' => $user->staff,
            ],
        ]);
    }

    // Register siswa
    public function registerSiswa(Request $request)
    {
        $request->validate([
            'nomor_induk_siswa' => 'required|digits:10|unique:siswas,nomor_induk_siswa',
            'name'              => 'required|string',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|min:6',
            'tingkat'           => 'required|in:X,XI,XII',
            'jurusan'           => 'required|in:RPL,ANIMASI,TJKT,TE,PSPT',
            'kelas'             => 'required|integer|min:1|max:5',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'siswa',
            ]);

            Siswa::create([
                'user_id'            => $user->id,
                'nomor_induk_siswa'  => $request->nomor_induk_siswa,
                'tingkat'            => $request->tingkat,
                'jurusan'            => $request->jurusan,
                'kelas'              => $request->kelas,
            ]);
        });

        return response()->json([
            'message' => 'Registrasi siswa berhasil',
        ], 201);
    }

    // Register staff
    public function registerStaff(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|min:6',
            'jabatan'              => 'required|string',
            'nomor_induk_pegawai'  => 'nullable|digits:18|unique:staff,nomor_induk_pegawai',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'staff',
            ]);

            Staff::create([
                'user_id'               => $user->id,
                'nomor_induk_pegawai'   => $request->nomor_induk_pegawai,
                'jabatan'               => $request->jabatan,
            ]);
        });

        return response()->json([
            'message' => 'Registrasi staff berhasil',
        ], 201);
    }

    // Logout 
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }
}
