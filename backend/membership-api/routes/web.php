<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminPanelController;

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
    Route::get('/access-history', [AdminPanelController::class, 'accesses'])->name('accesses.index');
    Route::middleware('role:admin')->group(function () {
        Route::get('/admins', [AdminPanelController::class, 'admins'])->name('admins.index');
        Route::post('/admins', [AdminPanelController::class, 'storeAdmin'])->name('admins.store');
    });
    Route::get('/settings', [AdminPanelController::class, 'settings'])->name('settings.index');
    Route::put('/settings/profile', [AdminPanelController::class, 'updateProfile'])->name('settings.profile');
});
