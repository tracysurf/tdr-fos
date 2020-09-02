<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class OrderController
 * @package App\Http\Controllers
 */
class OrderController extends Controller
{

    /**
     * List orders
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $orders = \App\Order::where('customer_id', $user->ID)->orderBy('created_at', 'desc')->get();

        $return_array = [
            'message'   => '',
            'success'   => true,
        ];

        $data = [];
        foreach($orders as $order)
        {
            $photos         = \App\Photo::where('order_id', $order->id)->whereNull('deleted_at')->get();
            $rolls          = [];
            $thumbnail_url  = '';

            if($photos->count())
            {
                // Determine number of rolls
                foreach($photos as $photo)
                {
                    if( ! isset($rolls[$photo->roll]))
                        $rolls[$photo->roll] = 1;
                }

                $first_photo = \App\Photo::where('order_id', $order->id)->whereNull('deleted_at')->first();

                $thumbnail_url = $first_photo->thumbnailURL('_md');
            }

            $data[] = [
                'id'            => $order->id,
                'name'          => $order->name,
                'filmsCount'    => count($rolls),
                'imagesCount'   => $photos->count(),
                'date'          => $order->created_at->format('M j'),
                'imageUrl'      => $thumbnail_url,
            ];
        }

        $return_array['data'] = $data;

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

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

        if( $order->customer_id !== $user->ID)
        {
            // Purposefully being obtuse here as to not confirm existence of this order id
            $return_array['message'] = 'Order not found';
            return $return_array;
        }

        if( ! $order)
        {
            $return_array['message'] = 'Order not found';
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

        return $return_array;
    }
}
