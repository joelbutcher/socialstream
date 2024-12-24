<?php

use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Http\Controllers\OAuthController;


Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () {
    Route::get('/oauth/{provider}/callback/prompt', [OAuthController::class, 'prompt'])->name('oauth.callback.prompt');
    Route::post('/oauth/{provider}/callback/confirm', [OAuthController::class, 'confirm'])->name(
        'oauth.callback.confirm'
    );
});
