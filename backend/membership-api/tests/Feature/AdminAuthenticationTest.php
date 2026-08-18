<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_logout(): void
    {
        $admin = Admin::create(['name' => 'Admin Test', 'email' => 'admin@test.local', 'password' => 'password123', 'role' => 'admin']);

        $this->post(route('login.process'), ['email' => $admin->email, 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_cashier_cannot_open_admin_management(): void
    {
        $cashier = Admin::create(['name' => 'Kasir', 'email' => 'kasir@test.local', 'password' => 'password123', 'role' => 'cashier']);
        $this->actingAs($cashier)->get(route('admins.index'))->assertForbidden();
    }
}
