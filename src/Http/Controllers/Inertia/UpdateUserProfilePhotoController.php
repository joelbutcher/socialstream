<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;

class UpdateUserProfilePhotoController extends Controller
{
    /**
     * Update the users profile picture from the connected account's avatar.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $account = ($user = $request->user())->connectedAccounts
            ->where('user_id', $user->getAuthIdentifier())
            ->where('id', $request->id)
            ->first();

        if (is_callable([$user, 'setProfilePhotoFromUrl']) && isset($account->avatar_path)) {
            $user->setProfilePhotoFromUrl($account->avatar_path);

            Session::flash('flash.banner', __('Profile photo updated'));
        }

        return back(303);
    }
}
