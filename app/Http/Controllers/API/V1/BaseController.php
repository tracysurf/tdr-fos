<?php

namespace App\Http\Controllers\API\V1;

use App\MobileApiRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class BaseController
 * @package App\Http\Controllers
 */
class BaseController extends Controller
{

    /**
     * @param Request $request
     * @param null $order_id
     * @return MobileApiRequest
     */
    public function createApiRequestRecord(Request $request, $order_id = null)
    {
        // Build $controller variable
        $method         = debug_backtrace()[1]['function'];
        $class          = debug_backtrace()[1]['object'];
        $class          = explode('\\', get_class($class));
        $controller     = array_pop($class);
        $controller     = $controller.'@'.$method;

        // Clean endpoint
        $endpoint       = $request->url();
        $endpoint       = explode('/', str_replace('//', '', $endpoint));
        unset($endpoint[0]);
        $endpoint       = implode('/', $endpoint);

        // Get customer_id
        $user           = $request->user();
        $customer_id    = null;
        if($user && isset($user->ID))
        {
            $customer_id = $user->ID;
        }

        // Create record
        $record                 = new MobileApiRequest();
        $record->endpoint       = $endpoint;
        $record->controller     = $controller;
        $record->customer_id    = $customer_id;
        $record->order_id       = $order_id;
        $record->success        = false;
        $record->parameters     = $request->all();
        $record->error_message  = null;

        $record->save();

        return $record;
    }
}
