<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Notifications\SystemActivityNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_and_read_notifications(): void
    {
        $admin = $this->admin();
        $admin->notify(new SystemActivityNotification('Top Up berhasil', 'Member melakukan top up.', 'top_up', '/transactions'));
        $notification = $admin->fresh()->unreadNotifications()->firstOrFail();

        $this->actingAs($admin)->get(route('notifications.index'))->assertOk()->assertSee('Top Up berhasil');
        $this->actingAs($admin)->patchJson(route('notifications.read', $notification->id))
            ->assertOk()->assertJsonPath('unread_count', 0);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_change_password_without_being_logged_out(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('settings.password'), [
            'current_password' => 'password123', 'password' => 'new-password-456', 'password_confirmation' => 'new-password-456',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-456', $admin->fresh()->password));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_notification_preferences_are_persisted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('settings.notifications.update'), [
            'enabled' => '1', 'top_up' => '1', 'transaction' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('notification_preferences', [
            'admin_id' => $admin->id, 'enabled' => true, 'top_up' => true, 'nfc_access' => false, 'transaction' => true, 'system' => false,
        ]);
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Admin Test', 'email' => fake()->unique()->safeEmail(), 'password' => 'password123', 'role' => 'admin']);
    }
}
