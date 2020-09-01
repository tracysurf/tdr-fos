<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Class APIPhotoController
 * @package App\Http\Controllers
 */
class APIPhotoController extends Controller
{

    /**
     * Like/unlike photo
     *
     * @param Request $request
     * @param $order_id
     * @param $roll_id
     * @param $photo_id
     * @return array
     */
    public function update(Request $request, $order_id, $roll_id, $photo_id)
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

        $photo = \App\Photo::where('order_id', $order_id)->where('id', $photo_id)->first();

        if( ! $photo)
        {
            $return_array['message'] = 'Photo not found';
            return $return_array;
        }

        $liked = $request->get('liked');

        if( $liked === 'true' || $liked === true)
        {
            $liked = true;
        }
        else
        {
            $liked = false;
        }

        $photo->favorite = $liked;
        $updated = $photo->save();
        if( ! $updated)
        {
            $return_array['message'] = 'Photo update failed';
            return $return_array;
        }

        $return_array['data']       = 'updated';
        $return_array['success']    = true;

        return $return_array;
    }

    /**
     * Rotate a photo
     *
     * @param Request $request
     * @param $order_id
     * @param $roll_id
     * @param $photo_id
     * @return array
     */
    public function rotate(Request $request, $order_id, $roll_id, $photo_id)
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

        $photo = \App\Photo::where('order_id', $order_id)->where('id', $photo_id)->first();

        if( ! $photo)
        {
            $return_array['message'] = 'Photo not found';
            return $return_array;
        }

        // Make request to FOS api to trigger the rotation
        $url = getenv('FOS_API_URL').'/api/mobile/photo/'.$photo_id.'/rotate';
        $response = Http::post($url, [
            'token' => getenv('FOS_API_TOKEN')
        ]);

        // Check for 5xx type response
        if($response->failed() || $response->serverError())
        {
            $return_array['message'] = 'Unknown api error';

            return $return_array;
        }

        // Check for a non-success response with error message
        $response_array = $response->json();
        if($response_array['success'] === false || $response_array['success'] === 'false')
        {
            $return_array['message'] = $response_array['message'];

            return $return_array;
        }

        // Pull the thumbnail data off of the response to put into our response
        $thumbnail_data = $response_array['data'];

        $return_array['data']       = 'updated';
        $return_array['thumbnail']  = $thumbnail_data;
        $return_array['success']    = true;

        return $return_array;
    }

    /**
     * Delete image(s) by id ('imageIds' request param)
     *
     * @param Request $request
     * @param $order_id
     * @param $roll_id
     * @return array
     */
    public function delete(Request $request, $order_id, $roll_id)
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

        $delete_image_ids = $request->get('imageIds');
        foreach($delete_image_ids as $delete_image_id)
        {
            $photo = \App\Photo::where('order_id', $order_id)->where('id', $delete_image_id)->first();

            if( ! $photo)
            {
                $return_array['message'] = 'Photo ('.$delete_image_id.') not found';
                return $return_array;
            }

            // Delete
            $photo->deleted_at = \Carbon\Carbon::now();
            $photo->save();
        }

        $return_array['data']       = 'updated';
        $return_array['success']    = true;

        return $return_array;
    }
}
