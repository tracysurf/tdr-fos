<?php

namespace App\Http\Controllers\API\V1;

use App\Order;
use App\Photo;
use App\TDR\FOSAPI\Client;
use Illuminate\Http\Request;

/**
 * Class OrderController
 * @package App\Http\Controllers
 */
class OrderController extends BaseController
{

    /**
     * List orders
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $orders = Order::where('customer_id', $user->ID)->orderBy('created_at', 'desc')->get();

        $return_array = [
            'message'   => '',
            'success'   => true,
        ];

        $data = [];
        foreach($orders as $order)
        {
            $photos         = Photo::where('order_id', $order->id)->whereNull('deleted_at')->get();
            $rolls          = [];

            // Only return orders with photos
            if($photos->count())
            {
                // Determine number of rolls
                foreach($photos as $photo)
                {
                    if( ! isset($rolls[$photo->roll]))
                        $rolls[$photo->roll] = 1;
                }

                $first_photo = Photo::where('order_id', $order->id)->whereNull('deleted_at')->first();

                $thumbnail_url = $first_photo->thumbnailURL('_lg');

                $data[] = [
                    'id'            => $order->id,
                    'name'          => $order->name,
                    'filmsCount'    => count($rolls),
                    'imagesCount'   => $photos->count(),
                    'date'          => $order->created_at->format('M j'),
                    'imageUrl'      => $thumbnail_url,
                ];
            }
        }

        $return_array['data'] = $data;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }

    /**
     * Update order's (album) name
     *
     * @param Request $request
     * @param $order_id
     * @return array
     */
    public function update(Request $request, $order_id)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $order  = Order::find($order_id);

        $api_request_record->order_id = $order_id;

        if( $order->customer_id !== $user->ID)
        {
            $message = 'Order not found';

            // Purposefully being obtuse here as to not confirm existence of this order id
            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        if( ! $order)
        {
            $message = 'Order not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        // Update the name if applicable
        $name = $request->get('name');
        if($name === null || $name === '')
        {
            $message = 'Name is empty, please supply a valid name.';
            $return_array['message'] = $message;

            return $return_array;
        }
        
        if($order->name !== $name)
        {
            $order->name = $name;
            $order->save();
        }

        // Check for updated rolls...
        /*
        if($request->has('updatedRolls'))
        {
            $updated_rolls = $request->get('updatedRolls');

            foreach($updated_rolls as $updated_roll)
            {
                $updated_roll_id    = $updated_roll->id;
                $updated_roll_name  = $updated_roll->name;

                // Get all photos on this order with this roll ID and change the roll_name on them
                $photos = \App\Photo::where('order_id', $order_id)->where('roll', $updated_roll_id)->get();
                foreach($photos as $photo)
                {
                    $photo->roll_name = $updated_roll_name;
                    $photo->save();
                }
            }
        }
        */

        $return_array['data']       = 'successful update';
        $return_array['success']    = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }

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
     * @return array
     */
    public function create(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();

        // Unpack the order details
        $billing_address    = $request->get('billingAddress');
        $shipping_address   = $request->get('shippingAddress');
        $line_items         = $request->get('lineItems');
        $return_shipping    = $request->get('returnShipping');

        $formatted_billing_address = [];
        foreach($billing_address as $key => $value)
        {
            $formatted_billing_address[$this->camel_to_snake($key)] = $value;
        }

        $formatted_shipping_address = [];
        foreach($shipping_address as $key => $value)
        {
            $formatted_shipping_address[$this->camel_to_snake($key)] = $value;
        }

        $formatted_line_items = [];
        foreach($line_items as $m_key => $line_item)
        {
            foreach($line_item as $key => $value)
            {
                $formatted_line_items[$m_key][$this->camel_to_snake($key)] = $value;
            }
        }

        $formatted_return_shipping = [];
        foreach($return_shipping as $key => $value)
        {
            $formatted_return_shipping[$this->camel_to_snake($key)] = $value;
        }

        // Push the order to the FOS API
        $fos_api_client = new Client();
        $response = $fos_api_client->createOrder($user->id,
                                                    $formatted_shipping_address,
                                                    $formatted_billing_address,
                                                    $formatted_return_shipping,
                                                    $formatted_line_items);

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

        // Get the order_id and totals from the response
        $order_id   = $response_array['order_id'];
        $totals     = $response_array['totals'];

        $formatted_totals = [];
        foreach($totals as $key => $value)
        {
            $formatted_totals[$this->snakeToCamel($key)] = $value;
        }

        // Associate the order_id with this API request
        $api_request_record->order_id = $order_id;

        $return_array['success']            = true;
        $return_array['data']['orderId']    = $order_id;
        $return_array['data']['totals']     = $formatted_totals;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }
}
