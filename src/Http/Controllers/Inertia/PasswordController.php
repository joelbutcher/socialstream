<?php

namespace JoelButcher\Socialstream\Http\Controllers\Inertia;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;

class PasswordController extends Controller
{
    /**
     * Set the password for the user.
     */
    public function store(Request $request, SetsUserPasswords $setter): RedirectResponse
    {
        $setter->set($request->user(), $request->only(['password', 'password_confirmation']));

        Session::flash('flash.banner', __('Your password has been set.'));

        return back(303);
    }
}
