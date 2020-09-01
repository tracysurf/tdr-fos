<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class APINotificationController
 * @package App\Http\Controllers
 */
class APINotificationController extends Controller
{

    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $user   = $request->user();

        $return_array['success']    = true;
        $return_array['data']       = [];

        return $return_array;
    }
}
