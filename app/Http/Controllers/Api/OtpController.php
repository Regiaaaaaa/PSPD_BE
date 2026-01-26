<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Mail\OtpMail;

class OtpController extends Controller
{
    // Kirim OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar'
            ], 404);
        }

        // generate 6 digit OTP
        $otp = rand(100000, 999999);

        // hapus OTP lama kalau ada
        Otp::where('email', $request->email)->delete();

        // simpan OTP baru
        Otp::create([
            'email' => $request->email,
            'otp' => $otp,
            'expired_at' => Carbon::now()->addMinutes(5),
        ]);

        // kirim email
        Mail::to($request->email)->send(new OtpMail($otp, $user->name));

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dikirim ke email'
        ]);
    }

    // Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required'
        ]);

        $otpData = Otp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->whereNull('verified_at')
            ->first();

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid'
            ], 400);
        }

        if (Carbon::now()->gt($otpData->expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kadaluarsa'
            ], 400);
        }

        // tandai OTP sudah dipakai
        $otpData->update([
            'verified_at' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP valid'
        ]);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        // cek apakah ada OTP yang sudah diverifikasi
        $otpData = Otp::where('email', $request->email)
            ->whereNotNull('verified_at')
            ->latest()
            ->first();

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP belum diverifikasi'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // hapus OTP setelah dipakai
        Otp::where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }
}
