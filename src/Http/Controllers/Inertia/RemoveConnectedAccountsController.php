<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class RemoveConnectedAccountsController extends Controller
{
    /**
     * Delete a connected account.
     */
    public function destroy(Request $request, string|int $connectedAccountId): RedirectResponse
    {
        $this->removeConnectedAccount($request, $connectedAccountId);

        return back(303);
    }

    /**
     * Remove a connected account.
     */
    public function removeConnectedAccount(Request $request, string|int $id): void
    {
        DB::table('connected_accounts')
            ->where('user_id', $request->user()->getKey())
            ->where('id', $id)
            ->delete();
    }
}
