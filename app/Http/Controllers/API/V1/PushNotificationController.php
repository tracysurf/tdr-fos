<?php

namespace App\Http\Controllers\API\V1;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class PushNotificationController
 * @package App\Http\Controllers\API\V1
 */
class PushNotificationController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customer_id    = $request->get('customer_id');
        $title          = $request->get('title');
        $body           = $request->get('body');
        $image_url      = $request->get('image_url');
        $data_array     = $request->get('data');
        $token          = $request->get('token');

        // Check the request token
        if($token !== getenv('FOS_API_TOKEN'))
        {
            return response('no', 503);
        }

        // Get the customer object
        $customer       = User::find($customer_id);

        // Send the message
        $message_bucket = $customer->sendPushNotification($title, $body, $data_array, $image_url);

        return $message_bucket;
    }
}
