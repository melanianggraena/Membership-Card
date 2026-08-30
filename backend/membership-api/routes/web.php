<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\OutletTransactionController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [AdminAuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AdminAuthController::class, 'login'])
    ->name('login.process');

Route::post('/logout', [AdminAuthController::class, 'logout'])
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/members', [AdminPanelController::class, 'members'])->name('members.index');
    Route::get('/members/create', [AdminPanelController::class, 'createMember'])->name('members.create');
    Route::post('/members', [AdminPanelController::class, 'storeMember'])->name('members.store');
    Route::get('/members/{member}/edit', [AdminPanelController::class, 'editMember'])->name('members.edit');
    Route::put('/members/{member}', [AdminPanelController::class, 'updateMember'])->name('members.update');
    Route::get('/rooms', [AdminPanelController::class, 'rooms'])->name('rooms.index');
    Route::post('/rooms', [AdminPanelController::class, 'storeRoom'])->middleware('role:admin')->name('rooms.store');
    Route::put('/rooms/{room}', [AdminPanelController::class, 'updateRoom'])->middleware('role:admin')->name('rooms.update');
    Route::get('/top-ups', [AdminPanelController::class, 'topUps'])->name('topups.index');
    Route::post('/top-ups', [AdminPanelController::class, 'storeTopUp'])->name('topups.store');
    Route::get('/scan-nfc', [AdminPanelController::class, 'scan'])->name('scan.index');
    Route::post('/scan-nfc', [AdminPanelController::class, 'scanStore'])->name('scan.store');
    Route::get('/transactions', [AdminPanelController::class, 'transactions'])->name('transactions.index');
    Route::get('/transactions/outlet/create', [OutletTransactionController::class, 'create'])->name('outlet-transactions.create');
    Route::post('/transactions/outlet', [OutletTransactionController::class, 'store'])->name('outlet-transactions.store');
    Route::get('/transactions/{transaction}', [AdminPanelController::class, 'transaction'])->name('transactions.show');
    Route::get('/access-history', [AdminPanelController::class, 'accesses'])->name('accesses.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/outlets', [OutletController::class, 'index'])->name('outlets.index');
    Route::get('/outlets/{outlet}', [OutletController::class, 'show'])->name('outlets.show');
    Route::middleware('role:admin')->group(function () {
        Route::resource('promos', PromoController::class)->except('show');
        Route::post('/outlets', [OutletController::class, 'store'])->name('outlets.store');
        Route::put('/outlets/{outlet}', [OutletController::class, 'update'])->name('outlets.update');
    });
    Route::middleware('role:admin')->group(function () {
        Route::get('/admins', [AdminPanelController::class, 'admins'])->name('admins.index');
        Route::post('/admins', [AdminPanelController::class, 'storeAdmin'])->name('admins.store');
    });
    Route::get('/settings', [SettingsController::class, 'profile'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::put('/settings/security', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
});
