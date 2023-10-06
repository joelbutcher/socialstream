<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use JoelButcher\Socialstream\Socialstream;

class ConnectedAccountController extends Controller
{
    /**
     * Delete a connected account.
     */
    public function destroy(Request $request, string|int $id): RedirectResponse
    {
        $request->validateWithBag('connectedAccountRemoval', [
            'password' => ['required', 'current_password'],
        ]);

        if (! Hash::check($request->password, $request->user()->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
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
