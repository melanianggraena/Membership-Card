<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function profile(): View { return view('settings.profile'); }
    public function security(): View { return view('settings.security'); }

    public function notifications(Request $request): View
    {
        $preference = $request->user()->notificationPreference()->firstOrCreate();
        return view('settings.notifications', compact('preference'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $admin->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('admins')->ignore($admin)],
        ]));
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);
        $request->user()->update(['password' => $data['password']]);
        return back()->with('success', 'Password berhasil diperbarui.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $request->user()->notificationPreference()->updateOrCreate([], [
            'enabled' => $request->boolean('enabled'),
            'top_up' => $request->boolean('top_up'),
            'nfc_access' => $request->boolean('nfc_access'),
            'transaction' => $request->boolean('transaction'),
            'system' => $request->boolean('system'),
        ]);
        return back()->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }
}
