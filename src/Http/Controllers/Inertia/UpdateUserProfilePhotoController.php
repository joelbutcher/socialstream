<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UpdateUserProfilePhotoController extends Controller
{
    /**
     * Update the users profile picture from the connected account's avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $account = ($user = $request->user())->connectedAccounts
            ->where('user_id', $user->getAuthIdentifier())
            ->where('id', $request->id)
            ->first();

        if (is_callable([$user, 'setProfilePhotoFromUrl']) && isset($account->avatar_path)) {
            $user->setProfilePhotoFromUrl($account->avatar_path);
        }

        return back(303);
    }
}
