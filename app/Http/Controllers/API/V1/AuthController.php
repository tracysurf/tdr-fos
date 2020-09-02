<?php

namespace App\Http\Controllers\API\V1;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller
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
