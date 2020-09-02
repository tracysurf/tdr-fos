<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    /**
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $user = $request->user();

        if( ! $user)
        {
            $return_array['message'] = 'User not found';

            return $return_array;
        }

        $notifications_token = null;
        if($request->has('notificationsToken'))
        {
            $notifications_token = $request->get('notificationsToken');
        }

        $sms_enabled = null;
        if($request->has('smsEnabled'))
        {
            $sms_enabled = $request->get('smsEnabled');
        }

        $phone_number = null;
        if($request->has('phoneNumber'))
        {
            $phone_number = $request->get('phoneNumber');
        }

        if( ! is_null($sms_enabled))
        {
            $user->updateSMSEnabled($sms_enabled);
        }

        if( ! is_null($phone_number))
        {
            // Validate the phone number (Propaganistas/Laravel-Phone package)
            $validator = Validator::make(['phone_number' => $request->get('phoneNumber')],
                [
                    'phone_number' => 'phone:AUTO,US'
                ]);

            if($validator->fails())
            {
                $return_array['message'] = 'Invalid phone number';

                return $return_array;
            }

            $user->updatePhoneNumber($phone_number);
        }

        $return_array['success']    = true;
        $return_array['data']       = 'successful update';

        return $return_array;
    }
}
