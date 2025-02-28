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
        return Inertia::render('settings/LinkedAccounts', [
            'status' => request()->session()->get('status'),
        ]);
    }

    public function destroy(Request $request, ConnectedAccount $account): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $account->delete();

        event(new ConnectedAccountDeleted($account));

        return redirect()->route('linked-accounts')->with(
            'status', __(':provider account unlinked.', ['provider' => Providers::name($account->provider)])
        );
    }
}
