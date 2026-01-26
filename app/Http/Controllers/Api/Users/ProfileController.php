<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Ubah profile 
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!in_array($user->role, ['staff', 'siswa'])) {
            abort(403, 'Role tidak diizinkan');
        }

        $rules = [
            'name' => 'required|string|max:255',
        ];

        // khusus siswa
        if ($user->role === 'siswa') {
            $rules['kelas'] = 'nullable|integer|min:1|max:5';
            $rules['tingkat'] = 'nullable|in:X,XI,XII';
            $rules['jurusan'] = 'nullable|in:RPL,ANIMASI,TJKT,TE,PSPT';
            $rules['nomor_induk_siswa'] = [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('siswas', 'nomor_induk_siswa')
                    ->ignore(optional($user->siswa)->id),
            ];
        }

        // khusus staff
        if ($user->role === 'staff') {
            $rules['jabatan'] = 'nullable|string|max:100';
            $rules['nomor_induk_pegawai'] = [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('staff', 'nomor_induk_pegawai')
                    ->ignore(optional($user->staff)->id),
            ];
        }

        $data = $request->validate($rules);

        // update tabel users 
        $user->update([
            'name' => $data['name'],
        ]);

        // update tabel siswa
        if ($user->role === 'siswa' && $user->siswa) {
            $user->siswa->update([
                'kelas' => $data['kelas'] ?? $user->siswa->kelas,
                'tingkat' => $data['tingkat'] ?? $user->siswa->tingkat,
                'jurusan' => $data['jurusan'] ?? $user->siswa->jurusan,
                'nomor_induk_siswa' => $data['nomor_induk_siswa'] ?? $user->siswa->nomor_induk_siswa,
            ]);
        }

        // update tabel staff
        if ($user->role === 'staff' && $user->staff) {
            $user->staff->update([
                'jabatan' => $data['jabatan'] ?? $user->staff->jabatan,
                'nomor_induk_pegawai' => $data['nomor_induk_pegawai'] ?? $user->staff->nomor_induk_pegawai,
            ]);
        }

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'data' => $user->load(['siswa', 'staff']),
        ]);
    }

    // Ganti password
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!in_array($user->role, ['staff', 'siswa'])) {
            abort(403, 'Role tidak diizinkan');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}