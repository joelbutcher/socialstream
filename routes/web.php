<?php

use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Http\Controllers\OAuthController;

Route::group([
    'prefix' => config('socialstream.prefix', config('socialstream.path')),
    'middleware' => config('socialstream.middleware', ['web'])
], function () {
    Route::get('/oauth/confirm', [OAuthController::class, 'prompt'])->name('oauth.confirm.show');
    Route::post('/oauth/confirm', [OAuthController::class, 'confirm'])->name(
        'oauth.confirm'
    );

    Route::get('/oauth/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::match(['get', 'post'], '/oauth/{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});
