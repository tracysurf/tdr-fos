<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class APIRollController
 * @package App\Http\Controllers
 */
class APIRollController extends Controller
{

    /**
     * List rolls on a given order
     *
     * @param Request $request
     * @param $order_id
     * @return array
     */
    public function index(Request $request, $order_id)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

        if( $order->customer_id !== $user->ID)
        {
            return [
                'message'   => 'Order not found', // Purposefully obtuse here as to not confirm that the order exists
                'success'   => false,
            ];
        }

        if( ! $order)
        {
            return [
                'message'   => 'Order not found',
                'success'   => false
            ];
        }

        $photos = \App\Photo::where('order_id', $order_id)->orderBy('roll', 'asc')->get();

        $rolls = [];
        foreach($photos as $photo)
        {
            if( ! isset($rolls[$photo->roll]))
                $rolls[$photo->roll] = 1;
        }

        foreach($rolls as $roll_id => $nothing)
        {
            $roll_photos = \App\Photo::where('order_id', $order_id)->whereNull('deleted_at')->where('roll', $roll_id)->orderBy('filename', 'asc')->get();

            $roll_photos_return = [];

            foreach($roll_photos as $roll_photo)
            {
                $image_urls = [
                    'sq'    => $roll_photo->thumbnailURL('_sq'),
                    'sm'    => $roll_photo->thumbnailURL('_sm'),
                    'lg'    => $roll_photo->thumbnailURL('_lg'),
                    'social'=> $roll_photo->thumbnailURL('_social'),
                ];

                $roll_photos_return[] = [
                    'id'            => $roll_photo->id,
                    'image_urls'    => $image_urls,
                    'liked'         => $roll_photo->favorite ? true : false,
                    'updated_at'    => $roll_photo->updated_at
                ];

                $roll_name = $roll_photo->roll_name;
            }

            $return_array['data'][] = [
                'id'    => $roll_id,
                'name'  => $roll_name,
                'images'=> $roll_photos_return
            ];
        }

        $return_array['success'] = true;

        return $return_array;
    }

    /**
     * Update roll name
     *
     * @param Request $request
     * @param $order_id
     * @param $roll_id
     * @return array
     */
    public function update(Request $request, $order_id, $roll_id)
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

        $roll_name  = $request->get('name');

        $photos = \App\Photo::where('order_id', $order_id)->where('roll', $roll_id)->get();

        if( ! $photos)
        {
            $return_array['message'] = 'No roll with that id found';
            return $return_array;
        }

        foreach($photos as $photo)
        {
            $photo->roll_name = $roll_name;
            $photo->save();
        }

        $return_array['data']       = 'successful update';
        $return_array['success']    = true;

        return $return_array;
    }
}
