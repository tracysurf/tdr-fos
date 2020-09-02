<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    public function show(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $user   = $request->user();

        if( ! $user)
        {
            $return_array['message'] = 'User not found';

            return $return_array;
        }

        // Get user's phone number from WP wp_usermeta table
        $phone_number   = $user->getPhoneNumber();

        // Determine if user has SMS enabled
        $sms_enabled    = $user->hasSMSEnabled();

        // Get user's push notifications token
        $push_token     = $user->getPushNotificationToken($request->bearerToken());

        $return_array['data'] = [
            'notificationsToken'    => $push_token,
            'smsEnabled'            => $sms_enabled,
            'phoneNumber'           => $phone_number
        ];

        $return_array['success'] = true;

        return $return_array;
    }
}
