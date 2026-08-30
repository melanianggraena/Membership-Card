<?php

namespace App\Services;

use App\Models\Admin;
use App\Notifications\SystemActivityNotification;
use Illuminate\Support\Facades\Notification;

class AdminNotificationService
{
    public function send(string $category, string $title, string $description, ?string $url = null): void
    {
        $admins = Admin::with('notificationPreference')->get()->filter(function (Admin $admin) use ($category): bool {
            $preference = $admin->notificationPreference;
            return ! $preference || ($preference->enabled && (bool) $preference->{$category});
        });

        Notification::send($admins, new SystemActivityNotification($title, $description, $category, $url));
    }
}
