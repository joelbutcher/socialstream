<?php

namespace App\Http\Controllers\Auth;

use App\Models\ConnectedAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

        ConnectedAccount::query()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('status', 'connected-account-removed');
    }
}
