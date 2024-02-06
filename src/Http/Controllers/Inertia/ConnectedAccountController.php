<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Fortify\Actions\ConfirmPassword;
use JoelButcher\Socialstream\Socialstream;

class ConnectedAccountController extends Controller
{
    /**
     * Delete a connected account.
     */
    public function destroy(Request $request, StatefulGuard $guard, string|int $id): RedirectResponse
    {
        $confirmed = app(ConfirmPassword::class)(
            $guard, $request->user(), $request->password
        );

        if (! $confirmed) {
            throw ValidationException::withMessages([
                'password' => __('The password is incorrect.'),
            ]);
        }

        Socialstream::connectedAccountModel()::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        session()->flash('flash.banner', __('Account removed'));

        return back()->with('status', 'connected-account-removed');
    }
}
