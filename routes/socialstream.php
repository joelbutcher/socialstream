<?php

use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Http\Controllers\Inertia\PasswordController;
use JoelButcher\Socialstream\Http\Controllers\Inertia\RemoveConnectedAccountsController;
use JoelButcher\Socialstream\Http\Controllers\OAuthController;

Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
    Route::get('/oauth/{provider}', [OAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
    Route::get('/oauth/{provider}/callback', [OAuthController::class, 'handleProviderCallback'])->name('oauth.callback');

    if (config('jetstream.stack') === 'inertia') {
        Route::delete('/user/connected-account/{id}', [RemoveConnectedAccountsController::class, 'destroy'])
            ->name('connected-accounts.destroy');

        Route::put('/user/set-password', [PasswordController::class, 'store'])->name('user-password.set');
    }
});
