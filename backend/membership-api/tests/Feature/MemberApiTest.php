<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Promo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_login_with_otp_and_access_home_without_sensitive_nfc(): void
    {
        $member = Member::create(['member_code' => '202608001', 'full_name' => 'Member Test', 'phone' => '081234567890', 'email' => 'member@example.test', 'nfc_uid' => 'SECRET-UID', 'balance' => 250000, 'status' => 'active', 'expired_at' => now()->addYear()]);
        Promo::create(['title' => 'Promo Aktif', 'description' => 'Diskon member', 'start_date' => today()->subDay(), 'end_date' => today()->addDay(), 'status' => 'active']);

        $otp = $this->postJson('/api/member/login/request-otp', ['phone' => $member->phone])->assertOk()->json('data.debug_otp');
        $token = $this->postJson('/api/member/login/verify-otp', ['phone' => $member->phone, 'otp' => $otp])->assertOk()->assertJsonMissing(['nfc_uid'])->json('data.token');

        $this->withToken($token)->getJson('/api/member/home')->assertOk()->assertJsonPath('data.member.member_code', '202608001')->assertJsonPath('data.promos.0.title', 'Promo Aktif')->assertJsonMissing(['nfc_uid']);
    }

    public function test_member_endpoints_require_a_token(): void
    {
        $this->getJson('/api/member/profile')->assertUnauthorized();
    }
}
