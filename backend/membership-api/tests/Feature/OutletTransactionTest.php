<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Member;
use App\Models\Outlet;
use App\Models\Room;
use App\Models\Promo;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_outlet_but_cashier_cannot_manage_it(): void
    {
        $admin = $this->admin('admin');
        $this->actingAs($admin)->post(route('outlets.store'), ['outlet_code' => 'RST-001', 'outlet_name' => 'Resto Technolife', 'description' => 'Resto', 'status' => 'active'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('outlets', ['outlet_code' => 'RST-001']);

        $cashier = $this->admin('cashier');
        $this->actingAs($cashier)->post(route('outlets.store'), ['outlet_code' => 'MKT-001', 'outlet_name' => 'Minimarket', 'status' => 'active'])->assertForbidden();
        $this->actingAs($cashier)->get(route('admins.index'))->assertForbidden();
    }

    public function test_successful_outlet_purchase_updates_membership_for_twelve_months(): void
    {
        $admin = $this->admin('admin'); $member = $this->member(150000); $outlet = $this->outlet();
        $response = $this->actingAs($admin)->post(route('outlet-transactions.store'), ['outlet_id' => $outlet->id, 'member_id' => $member->id, 'amount' => 35000]);
        $transaction = Transaction::firstOrFail();

        $response->assertRedirect(route('transactions.show', $transaction));
        $this->assertSame('115000.00', $member->fresh()->balance);
        $member->refresh();
        $this->assertTrue($member->last_used->isToday());
        $this->assertTrue($member->expired_at->isSameDay(now()->addYear()));
        $this->assertSame('150000.00', $transaction->balance_before);
        $this->assertSame('115000.00', $transaction->balance_after);
        $this->assertSame('outlet_purchase', $transaction->transaction_type);
        $this->assertSame('success', $transaction->status);
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{6}$/', $transaction->transaction_code);
    }

    public function test_insufficient_balance_records_failure_without_reducing_balance(): void
    {
        $admin = $this->admin('cashier'); $member = $this->member(10000); $outlet = $this->outlet();
        $lastUsed = now()->subMonths(2); $expiredAt = now()->subMonth();
        $member->update(['last_used' => $lastUsed, 'expired_at' => $expiredAt]);
        $this->actingAs($admin)->post(route('outlet-transactions.store'), ['outlet_id' => $outlet->id, 'member_id' => $member->id, 'amount' => 35000]);
        $transaction = Transaction::firstOrFail();

        $this->assertSame('10000.00', $member->fresh()->balance);
        $this->assertSame('failed', $transaction->status);
        $this->assertSame('10000.00', $transaction->balance_before);
        $this->assertSame('10000.00', $transaction->balance_after);
        $this->assertSame($lastUsed->format('Y-m-d H:i:s'), $member->fresh()->last_used->format('Y-m-d H:i:s'));
        $this->assertSame($expiredAt->format('Y-m-d H:i:s'), $member->fresh()->expired_at->format('Y-m-d H:i:s'));
    }

    public function test_transaction_codes_are_unique(): void
    {
        $admin = $this->admin('admin'); $member = $this->member(200000); $outlet = $this->outlet();
        foreach ([10000, 15000] as $amount) $this->actingAs($admin)->post(route('outlet-transactions.store'), ['outlet_id' => $outlet->id, 'member_id' => $member->id, 'amount' => $amount]);
        $this->assertSame(2, Transaction::distinct('transaction_code')->count('transaction_code'));
    }

    public function test_room_access_reduces_balance_and_extends_membership(): void
    {
        $admin = $this->admin('admin'); $member = $this->member(100000); $member->update(['nfc_uid' => 'NFC-TEST']);
        $room = Room::create(['room_name' => 'Meeting Room', 'access_price' => 25000, 'capacity' => 10, 'status' => 'active']);

        $this->actingAs($admin)->post(route('scan.store'), ['uid' => 'NFC-TEST', 'room_id' => $room->id])->assertSessionHas('success');
        $member->refresh();
        $this->assertSame('75000.00', $member->balance);
        $this->assertTrue($member->last_used->isToday());
        $this->assertTrue($member->expired_at->isSameDay(now()->addYear()));
        $this->assertSame('room_access', Transaction::firstOrFail()->transaction_type);
    }

    public function test_active_promo_discount_is_calculated_and_stored_by_backend(): void
    {
        $admin = $this->admin('cashier'); $member = $this->member(150000); $outlet = $this->outlet();
        $promo = $this->promo('active', today()->subDay(), today()->addDay(), 'percentage', 10);

        $this->actingAs($admin)->post(route('outlet-transactions.store'), ['outlet_id' => $outlet->id, 'member_id' => $member->id, 'amount' => 100000, 'promo_id' => $promo->id])->assertSessionHasNoErrors();
        $transaction = Transaction::firstOrFail();

        $this->assertSame('100000.00', $transaction->original_amount);
        $this->assertSame('10000.00', $transaction->discount_amount);
        $this->assertSame('90000.00', $transaction->amount);
        $this->assertSame('60000.00', $member->fresh()->balance);
        $this->assertSame($promo->id, $transaction->promo_id);
    }

    public function test_expired_and_inactive_promos_cannot_be_used(): void
    {
        $admin = $this->admin('cashier'); $member = $this->member(150000); $outlet = $this->outlet();
        $expired = $this->promo('active', today()->subDays(3), today()->subDay());
        $inactive = $this->promo('inactive', today()->subDay(), today()->addDay());

        foreach ([$expired, $inactive] as $promo) {
            $this->actingAs($admin)->from(route('outlet-transactions.create'))->post(route('outlet-transactions.store'), ['outlet_id' => $outlet->id, 'member_id' => $member->id, 'amount' => 100000, 'promo_id' => $promo->id])->assertSessionHasErrors('promo_id');
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame('150000.00', $member->fresh()->balance);
    }

    private function admin(string $role): Admin { return Admin::create(['name' => ucfirst($role), 'email' => fake()->unique()->safeEmail(), 'password' => 'password123', 'role' => $role]); }
    private function member(float $balance): Member { return Member::create(['member_code' => fake()->unique()->numerify('202608###'), 'full_name' => 'Member Test', 'phone' => fake()->unique()->numerify('0812########'), 'balance' => $balance, 'status' => 'active', 'expired_at' => now()->subMonth()]); }
    private function outlet(): Outlet { return Outlet::create(['outlet_code' => 'RST-001', 'outlet_name' => 'Resto Technolife', 'status' => 'active']); }
    private function promo(string $status, $start, $end, string $type = 'fixed', float $value = 10000): Promo { return Promo::create(['title' => fake()->unique()->words(2, true), 'description' => 'Promo test', 'discount_type' => $type, 'discount_value' => $value, 'start_date' => $start, 'end_date' => $end, 'status' => $status]); }
}
