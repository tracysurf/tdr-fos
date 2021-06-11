<?php

namespace App\Http\Controllers\API\V1;

use App\TDR\FOSAPI\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Class ShopController
 * @package App\Http\Controllers
 */
class ShopController extends BaseController
{

    /**
     * @param Request $request
     * @return array
     */
    public function products(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();

        // Make request to FOS api get the WooCommerce products
        $fos_api_client = new Client();
        $response = $fos_api_client->getProducts();

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
        if($response_array['success'] === false || $response_array['success'] === 'false')
        {
            $return_array['message'] = $response_array['message'];

            $api_request_record->updateFailed($start, $response_array['message']);

            return $return_array;
        }

        $products_data = $response_array['data'];

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        $return_array['success']    = true;
        $return_array['data']       = $products_data;

        return $return_array;
    }

}
