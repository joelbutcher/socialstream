<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ConnectedAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JoelButcher\Socialstream\Events\ConnectedAccountDeleted;
use JoelButcher\Socialstream\Providers;

class LinkedAccountController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('settings/linked-accounts', [
            'status' => request()->session()->get('status'),
        ]);
    }

    public function destroy(Request $request, ConnectedAccount $account): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        event(new ConnectedAccountDeleted($account));

        $account->delete();

        return redirect()->route('linked-accounts')->with(
            'status', __('Your :provider account has been unlinked.', ['provider' => Providers::name($account->provider)])
        );
    }
}
