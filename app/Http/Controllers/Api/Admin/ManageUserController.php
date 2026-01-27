<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    // List user 
    public function index()
    {
        $users = User::with(['siswa', 'staff'])
            ->whereIn('role', ['operator', 'staff', 'siswa'])
            ->get();

        return response()->json([
            'message' => 'List user berhasil diambil',
            'data' => $users
        ]);
    }

    // Detail user
    public function show($id)
    {
        $user = User::with(['siswa', 'staff'])
            ->whereIn('role', ['operator', 'staff', 'siswa'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Detail user',
            'data' => $user
        ]);
    }

    // Buat operator
    public function createOperator(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'operator',
        ]);

        return response()->json([
            'message' => 'Akun operator berhasil dibuat',
            'data' => $user
        ], 201);
    }

    // Buat staff
    public function createStaff(Request $request)
    {
        $request->validate([
            'name'                => 'required|string',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'required|min:6',
            'jabatan'             => 'required|string',
            'nomor_induk_pegawai' => 'nullable|digits:18|unique:staff,nomor_induk_pegawai',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'staff',
            ]);

            Staff::create([
                'user_id'             => $user->id,
                'nomor_induk_pegawai' => $request->nomor_induk_pegawai,
                'jabatan'             => $request->jabatan,
            ]);
        });

        return response()->json([
            'message' => 'Akun staff berhasil dibuat',
        ], 201);
    }

    // Buat siswa
    public function createSiswa(Request $request)
    {
        $request->validate([
            'name'              => 'required|string',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|min:6',
            'nomor_induk_siswa' => 'required|digits:10|unique:siswa,nomor_induk_siswa',

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
                'user_id'           => $user->id,
                'nomor_induk_siswa' => $request->nomor_induk_siswa,
                'tingkat'           => $request->tingkat,
                'jurusan'           => $request->jurusan,
                'kelas'             => $request->kelas,
            ]);
        });

        return response()->json([
            'message' => 'Akun siswa berhasil dibuat',
        ], 201);
    }

    // Ubah data user
    public function update(Request $request, $id)
    {
        $user = User::with(['siswa', 'staff'])
            ->whereIn('role', ['operator', 'staff', 'siswa'])
            ->findOrFail($id);

        $request->validate([
            'name'  => 'required|string',

            'tingkat' => 'sometimes|in:X,XI,XII',
            'jurusan' => 'sometimes|in:RPL,ANIMASI,TJKT,TE,PSPT',
            'kelas'   => 'sometimes|integer|min:1|max:5',
            'nomor_induk_siswa' => 'sometimes|digits:10|unique:siswa,nomor_induk_siswa,' . optional($user->siswa)->id,


            'jabatan' => 'sometimes|string',
            'nomor_induk_pegawai' =>
                'sometimes|nullable|digits:18|unique:staff,nomor_induk_pegawai,' . optional($user->staff)->id,
        ]);

        $user->update([
            'name'  => $request->name,
        ]);

        if ($user->role === 'siswa' && $user->siswa) {
            $user->siswa->update($request->only([
                'nomor_induk_siswa',
                'tingkat',
                'jurusan',
                'kelas',
            ]));
        }

        if ($user->role === 'staff' && $user->staff) {
            $user->staff->update($request->only([
                'jabatan',
                'nomor_induk_pegawai',
            ]));
        }

        return response()->json([
            'message' => 'Data user berhasil diupdate',
            'data' => $user->load(['siswa', 'staff']),
        ]);
    }

    // Hapus user
    public function destroy($id)
    {
        $user = User::whereIn('role', ['operator', 'staff', 'siswa'])
            ->findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus',
        ]);
    }

    // Reset password
    public function resetPassword($id)
    {
        $user = User::whereIn('role', ['operator', 'staff', 'siswa'])
            ->findOrFail($id);

        $defaultPassword = 'smktb123';

        $user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
            'default_password' => $defaultPassword,
        ]);
    }
}
