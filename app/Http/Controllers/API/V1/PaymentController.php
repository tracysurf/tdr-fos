<?php

namespace App\Http\Controllers\API\V1;

use App\Order;
use App\Photo;
use App\TDR\FOSAPI\Client;
use Illuminate\Http\Request;

/**
 * Class PaymentController
 * @package App\Http\Controllers
 */
class PaymentController extends BaseController
{

    public function camel_to_snake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
    public function snakeToCamel($input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * @param Request $request
     * @param $order_id
     * @return array
     */
    public function create(Request $request, $order_id)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'status'    => null,
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, $order_id);

        $user   = $request->user();

        $payment_gateway    = $request->get('payment_gateway');
        $payment_token      = $request->get('payment_token');

        // Push the payment to the FOS API
        $fos_api_client = new Client();
        $response       = $fos_api_client->createPayment($order_id, $payment_gateway, $payment_token);

        // Check for 5xx type response
        if($response->failed() || $response->serverError())
        {
            $message = 'Unknown api error';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        // Check for a non-success response with error message
        $response_array = $response->json();
        if(
            ($response_array['success'] === false || $response_array['success'] === 'false')
            && $response_array['message'] !== 'payment failed' // If it's 'payment failed' our API request didn't really fail so we won't respond with that
        )
        {
            $return_array['message'] = $response_array['message'];

            $api_request_record->updateFailed($start, $response_array['message']);

            return $return_array;
        }

        $return_array['success']    = true;
        if($response_array['success'])
            $return_array['status']     = 'success';
        else
            $return_array['status']     = 'failed';

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }
}
