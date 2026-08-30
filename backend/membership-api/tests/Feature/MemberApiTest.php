<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Promo;
use App\Models\Outlet;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_transaction_api_returns_code_and_distinguishes_room_and_outlet(): void
    {
        $member = Member::create(['member_code' => '202608009', 'full_name' => 'API Member', 'phone' => '081200000009', 'balance' => 100000, 'status' => 'active']);
        $room = Room::create(['room_name' => 'Room A', 'access_price' => 10000, 'capacity' => 5, 'status' => 'active']);
        $outlet = Outlet::create(['outlet_code' => 'RST-001', 'outlet_name' => 'Resto', 'status' => 'active']);
        Transaction::create(['transaction_code' => 'TRX-20260830-000001', 'member_id' => $member->id, 'room_id' => $room->id, 'transaction_type' => 'room_access', 'reference_id' => 1, 'amount' => 10000, 'balance_before' => 100000, 'balance_after' => 90000, 'status' => 'success']);
        Transaction::create(['transaction_code' => 'TRX-20260830-000002', 'member_id' => $member->id, 'outlet_id' => $outlet->id, 'transaction_type' => 'outlet_purchase', 'reference_id' => 1, 'amount' => 15000, 'balance_before' => 90000, 'balance_after' => 75000, 'status' => 'success']);

        Sanctum::actingAs($member, ['member:read']);
        $this->getJson('/api/member/transactions')->assertOk()
            ->assertJsonFragment(['transaction_code' => 'TRX-20260830-000001', 'transaction_type' => 'room_access'])
            ->assertJsonFragment(['room_name' => 'Room A'])
            ->assertJsonFragment(['transaction_code' => 'TRX-20260830-000002', 'transaction_type' => 'outlet_purchase'])
            ->assertJsonFragment(['outlet_name' => 'Resto']);
    }
}
