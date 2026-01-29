<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilePetugasController extends Controller
{
    // Update profile admin / operator
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'operator'])) {
            abort(403, 'Role tidak diizinkan');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }

    // Ganti password admin / operator
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'operator'])) {
            abort(403, 'Role tidak diizinkan');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama salah'
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