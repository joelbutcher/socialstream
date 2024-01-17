<?php

use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Http\Controllers\OAuthController;

Route::post('/login', fn () => 'login')->name('login');
Route::post('/register', fn () => 'register')->name('register');

Route::group(['middleware' => config('socialstream.middleware', ['web'])], function () use ($router) {
    Route::get('/oauth/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::match(['get', 'post'], '/oauth/{provider}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});
