<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Class PhotoController
 * @package App\Http\Controllers
 */
class PhotoController extends BaseController
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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

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

        $photo = \App\Photo::where('order_id', $order_id)->where('id', $photo_id)->first();

        if( ! $photo)
        {
            $message = 'Order not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

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
            $message = 'Photo update failed';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        $return_array['data']       = 'updated';
        $return_array['success']    = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

        $api_request_record->order_id = $order_id;

        if( $order->customer_id !== $user->ID)
        {
            // Purposefully being obtuse here as to not confirm existence of this order id
            $return_array['message'] = 'Order not found';
            return $return_array;
        }

        if( ! $order)
        {
            $message = 'Order not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        $photo = \App\Photo::where('order_id', $order_id)->where('id', $photo_id)->first();

        if( ! $photo)
        {
            $message = 'Photo not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

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

        // Pull the thumbnail data off of the response to put into our response
        $thumbnail_data = $response_array['data'];

        $return_array['data']       = 'updated';
        $return_array['thumbnail']  = $thumbnail_data;
        $return_array['success']    = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

        $api_request_record->order_id = $order_id;

        if( $order->customer_id !== $user->ID)
        {
            $message = 'Order not found';

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

        $delete_image_ids = $request->get('imageIds');
        foreach($delete_image_ids as $delete_image_id)
        {
            $photo = \App\Photo::where('order_id', $order_id)->where('id', $delete_image_id)->first();

            if( ! $photo)
            {
                $message = 'Photo ('.$delete_image_id.') not found';

                $return_array['message'] = $message;

                $api_request_record->updateFailed($start, $message);

                return $return_array;
            }

            // Delete
            $photo->deleted_at = \Carbon\Carbon::now();
            $photo->save();
        }

        $return_array['data']       = 'updated';
        $return_array['success']    = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }
}
