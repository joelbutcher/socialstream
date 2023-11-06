<?php

use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Http\Controllers\Inertia\ConnectedAccountController;
use JoelButcher\Socialstream\Http\Controllers\Inertia\PasswordController;
use JoelButcher\Socialstream\Http\Controllers\Inertia\UpdateUserProfilePhotoController;
use JoelButcher\Socialstream\Http\Controllers\OAuthController;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;

Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
    Route::get('/oauth/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::match(['get', 'post'], '/oauth/{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');

    Route::delete('/user/connected-account/{id}', [ConnectedAccountController::class, 'destroy'])
        ->middleware(['auth'])
        ->name('connected-accounts.destroy');

    Route::post('/user/set-password', [PasswordController::class, 'store'])
        ->middleware(['auth'])
        ->name('user-password.set');

    if (Socialstream::hasProviderAvatarsFeature() && Jetstream::managesProfilePhotos()) {
        Route::put('/user/profile-photo', [UpdateUserProfilePhotoController::class, '__invoke'])
            ->middleware(['auth'])
            ->name('user-profile-photo.set');
    }
});
