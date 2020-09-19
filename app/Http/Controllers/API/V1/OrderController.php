<?php

namespace App\Http\Controllers\API\V1;

use App\Order;
use App\Photo;
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
}
