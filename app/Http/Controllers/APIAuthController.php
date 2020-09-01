<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

/**
 * Class APIAuthController
 * @package App\Http\Controllers
 */
class APIAuthController extends Controller
{

    /**
     * @param Request $request
     * @return array
     */
    public function signIn(Request $request)
    {
        $request->validate([
            'email'         => 'required|email',
            'password'      => 'required',
            'device_name'   => 'required',
        ]);

        $user = User::where('user_email', $request->email)->first();

        if (! $user || ! Auth::validate(['email' => $request->email, 'password' => $request->password])) {

            return [
                'message'   => 'The username or password is incorrect.',
                'success'   => false,
                'data'      => null
            ];
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return [
            'message'   => '',
            'success'   => true,
            'data'      => $token
        ];
    }
}
