<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Update profile
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
            $rules['kelas'] = 'nullable|string|max:50';
            $rules['nomor_induk'] = [
                'nullable',
                'string',
                Rule::unique('users', 'nomor_induk')->ignore($user->id),
            ];
        }

        // khusus staff
        if ($user->role === 'staff') {
            $rules['jabatan'] = 'nullable|string|max:100';
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];

        if ($user->role === 'siswa') {
            $user->kelas = $data['kelas'] ?? $user->kelas;
            $user->nomor_induk = $data['nomor_induk'] ?? $user->nomor_induk;
        }

        if ($user->role === 'staff') {
            $user->jabatan = $data['jabatan'] ?? $user->jabatan;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Ganti password
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // hanya staff & siswa
        if (!in_array($user->role, ['staff', 'siswa'])) {
            abort(403, 'Role tidak diizinkan');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        // cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }
}
