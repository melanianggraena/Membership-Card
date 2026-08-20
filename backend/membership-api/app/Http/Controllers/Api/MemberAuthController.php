<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\OtpVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberAuthController extends Controller
{
    public function requestOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);
        $member = Member::where('phone', $data['phone'])->where('status', 'active')->first();
        if (! $member) return response()->json(['success' => false, 'message' => 'Nomor handphone tidak terdaftar atau membership tidak aktif.'], 404);
        $otp = (string) random_int(100000, 999999);
        OtpVerification::where('member_id', $member->id)->whereNull('verified_at')->delete();
        OtpVerification::create(['member_id' => $member->id, 'otp_code' => Hash::make($otp), 'expired_at' => now()->addMinutes(5), 'created_at' => now()]);
        $response = ['success' => true, 'message' => 'OTP berhasil dikirim.', 'data' => ['expires_in' => 300]];
        if (app()->environment(['local', 'testing'])) $response['data']['debug_otp'] = $otp;
        return response()->json($response);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string'], 'otp' => ['required', 'digits:6']]);
        $member = Member::where('phone', $data['phone'])->first();
        $record = $member?->otpVerifications()->whereNull('verified_at')->where('expired_at', '>', now())->latest('created_at')->first();
        if (! $record || ! Hash::check($data['otp'], $record->otp_code)) return response()->json(['success' => false, 'message' => 'OTP tidak valid atau sudah kedaluwarsa.'], 422);
        $record->update(['verified_at' => now()]);
        $member->tokens()->delete();
        $token = $member->createToken('member-mobile', ['member:read', 'member:profile'], now()->addDays(30))->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Login berhasil.', 'data' => ['token' => $token, 'member' => [
            'id' => $member->id, 'member_code' => $member->member_code, 'full_name' => $member->full_name,
            'phone' => $member->phone, 'email' => $member->email, 'balance' => (float) $member->balance,
            'status' => $member->status, 'last_used' => $member->last_used, 'expired_at' => $member->expired_at,
        ]]]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil.', 'data' => null]);
    }
}
