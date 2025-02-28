<?php

use App\Http\Controllers\Settings\AvatarController;
use App\Http\Controllers\Settings\LinkedAccountController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('settings/profile/avatar', [AvatarController::class, 'update'])->name('profile.avatar.update');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');

    Route::get('settings/linked-accounts', [LinkedAccountController::class, 'show'])->name('linked-accounts');
    Route::delete('settings/linked-accounts/{account}', [LinkedAccountController::class, 'destroy'])->name('linked-accounts.destroy');
});
