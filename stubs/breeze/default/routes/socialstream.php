<?php

use App\Http\Controllers\Auth\ConnectedAccountController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::delete('/user/connected-account/{id}', [ConnectedAccountController::class, 'destroy'])
        ->name('connected-accounts.destroy');

    Route::post('password', [PasswordController::class, 'store'])
        ->name('password.set');
});

