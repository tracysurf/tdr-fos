<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends BaseController
{

    /**
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        /** @var \App\User $user */
        $user   = $request->user();

        if( ! $user)
        {
            $message = 'User not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        // Get user's phone number from WP wp_usermeta table
        $phone_number   = $user->getPhoneNumber();

        // Determine if user has SMS enabled
        $sms_enabled    = $user->hasSMSEnabled();

        // Get user's push notifications token
        $push_token     = $user->getDevicePushNotificationTokenFromBearer($request->bearerToken());

        // Get user's shipping and billing addresses
        $shipping_address   = $user->getShippingAddress();
        $billing_address    = $user->getBillingAddress();
        $formatted_billing_address = [];
        foreach($billing_address as $key => $value)
        {
            $formatted_billing_address[$this->snakeToCamel($key)] = $value;
        }
        $formatted_shipping_address = [];
        foreach($shipping_address as $key => $value)
        {
            $formatted_shipping_address[$this->snakeToCamel($key)] = $value;
        }

        // Get the user's saved payment methods
        $saved_tokens = $user->getPaymentMethods();
        $formatted_payment_methods = [];
        foreach($saved_tokens as $key => $token)
        {
            $formatted_payment_methods[$this->snakeToCamel($key)] = $value;
        }

        $return_array['data'] = [
            'notificationsToken'    => $push_token,
            'smsEnabled'            => $sms_enabled,
            'phoneNumber'           => $phone_number,
            'email'                 => $user->user_email,
            'billingAddress'        => $formatted_billing_address,
            'shippingAddress'       => $formatted_shipping_address,
            'paymentMethods'        => $formatted_payment_methods
        ];

        $return_array['success'] = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user = $request->user();

        if( ! $user)
        {
            $message = 'User not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        // Check for a new notifications token
        $notifications_token = null;
        if($request->has('notificationsToken'))
        {
            $notifications_token = $request->get('notificationsToken');
        }

        // Check for an updated sms enable flag
        $sms_enabled = null;
        if($request->has('smsEnabled'))
        {
            $sms_enabled = $request->get('smsEnabled');
        }

        // Check for an updated phone number
        $phone_number = null;
        if($request->has('phoneNumber'))
        {
            $phone_number = $request->get('phoneNumber');
        }

        // If we've got an sms enabled flag let's update
        if( ! is_null($sms_enabled))
        {
            $user->updateSMSEnabled($sms_enabled);
        }

        // If we've got an updated phone number let's update
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

        // If we've got an updated notifications token let's update
        if( ! is_null($notifications_token))
        {
            $user->updatePushNotificationToken($request->bearerToken(), $notifications_token);
        }

        $return_array['success']    = true;
        $return_array['data']       = 'successful update';

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }

    public function showAddresses(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();

        if( ! $user)
        {
            $message = 'User not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        $shipping_address   = $user->getShippingAddress();
        $billing_address    = $user->getBillingAddress();

        $return_array['success']    = true;
        $return_array['data']       = [
            'shipping_address'  => $shipping_address,
            'billing_address'   => $billing_address
        ];

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }

    public function updateAddresses(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();

        if( ! $user)
        {
            $message = 'User not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        // Update $api_request_record
        $api_request_record->updateSuccess($start);
    }

    public function camel_to_snake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
    public function snakeToCamel($input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}
