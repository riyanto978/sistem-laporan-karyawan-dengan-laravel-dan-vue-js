<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdateProfile extends Controller
{
    /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'name' => 'required',
            'username' => 'required|unique:users,'.$user->id,
        ]);

        return tap($user)->update($request->only('name', 'username'));
    }
}
