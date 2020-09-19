<?php

namespace App\Http\Controllers\API\V1;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends BaseController
{
    /**
     * @param Request $request
     * @return array
     */
    public function signIn(Request $request)
    {
        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $request->validate([
            'email'         => 'required|email',
            'password'      => 'required',
            'device_name'   => 'required',
        ]);

        $user = User::where('user_email', $request->email)->first();

        $api_request_record->customer_id = $user->ID;

        if (! $user || ! Auth::validate(['email' => $request->email, 'password' => $request->password])) {

            $message = 'The username or password is incorrect.';

            // Update $api_request_record
            $api_request_record->updateFailed($start, $message);

            return [
                'message'   => $message,
                'success'   => false,
                'data'      => null
            ];
        }

        // Create auth/bearer token
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return [
            'message'   => '',
            'success'   => true,
            'data'      => $token
        ];
    }
}
