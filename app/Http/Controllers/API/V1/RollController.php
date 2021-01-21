<?php

namespace App\Http\Controllers\API\V1;

use App\Download;
use Illuminate\Http\Request;

/**
 * Class RollController
 * @package App\Http\Controllers
 */
class RollController extends BaseController
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

        $photos = \App\Photo::where('order_id', $order_id)->orderBy('roll', 'asc')->get();

        $rolls = [];
        foreach($photos as $photo)
        {
            if( ! isset($rolls[$photo->roll]))
                $rolls[$photo->roll] = 1;
        }

        foreach($rolls as $roll_id => $nothing)
        {
            $roll_photos = \App\Photo::where('order_id', $order_id)
                ->whereNull('deleted_at')
                ->where('roll', $roll_id)
                ->orderBy('filename', 'asc')
                ->get();

            $roll_download = Download::where('order_id', $order_id)
                ->where('roll', $roll_id)
                ->orderBy('id', 'desc')
                ->first();

            $roll_download_return = null;
            if($roll_download)
            {
                // Format Downloads for response
                $roll_download_return = [
                    'downloadId'    => $roll_download->id,
                    'orderId'       => $roll_download->order_id,
                    'rollId'        => $roll_download->roll,
                    'downloadURL'   => $roll_download->url,
                    'failed'        => $roll_download->failed ? true : false,
                    'failedMessage' => $roll_download->failed_message,
                    'date'          => $roll_download->created_at,
                    'seenAt'        => $roll_download->seen_at
                ];
            }

            // Format Photos for response
            $roll_photos_return = [];
            $roll_name          = '';
            foreach($roll_photos as $roll_photo)
            {
                $image_urls = [
                    'sq'            => $roll_photo->thumbnailURL('_sq'),
                    'sm'            => $roll_photo->thumbnailURL('_sm'),
                    'lg'            => $roll_photo->thumbnailURL('_lg'),
                    'social'        => $roll_photo->thumbnailURL('_social'),
                    'lg-original'   => $roll_photo->thumbnailURL('_lg-original')
                ];

                $serialization = null;
                $metadata = $roll_photo->metadata()->first();
                if($metadata)
                {
                    $serialization = $metadata->editor_operations;
                }

                $roll_photos_return[] = [
                    'id'            => $roll_photo->id,
                    'image_urls'    => $image_urls,
                    'liked'         => $roll_photo->favorite ? true : false,
                    'serialization' => $serialization,
                    'updated_at'    => $roll_photo->updated_at
                ];

                $roll_name = $roll_photo->roll_name;
            }

            $return_array['data'][] = [
                'id'        => $roll_id,
                'name'      => $roll_name,
                'images'    => $roll_photos_return,
                'download'  => $roll_download_return
            ];
        }

        $return_array['success'] = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user   = $request->user();
        $order  = \App\Order::find($order_id);

        $api_request_record->order_id = $order_id;

        if( ! $order)
        {
            $message = 'Order not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        if( $order->customer_id !== $user->ID)
        {
            $message = 'Order not found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        $roll_name  = $request->get('name');

        $photos = \App\Photo::where('order_id', $order_id)->where('roll', $roll_id)->get();

        if( ! $photos)
        {
            $message = 'No roll with that id found';

            $return_array['message'] = $message;

            $api_request_record->updateFailed($start, $message);

            return $return_array;
        }

        foreach($photos as $photo)
        {
            $photo->roll_name = $roll_name;
            $photo->save();
        }

        $return_array['data']       = 'successful update';
        $return_array['success']    = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }
}
