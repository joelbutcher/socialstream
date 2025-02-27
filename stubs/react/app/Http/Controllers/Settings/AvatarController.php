<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    /**
     * Update the user's avatar.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'string', 'exists:connected_accounts,avatar'],
        ]);

        $request->user()->fill([
            'avatar' => $request->avatar,
        ]);

        $request->user()->save();

        return to_route('linked-accounts')->with('status', 'Avatar updated!');
    }
}
