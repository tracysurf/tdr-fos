<?php

namespace App\Http\Controllers\API\V1;

use App\Download;
use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

/**
 * Class DownloadController
 * @package App\Http\Controllers
 */
class DownloadController extends Controller
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

        $downloads = Download::where('customer_id', $user->ID)
            ->orderBy('created_at', 'desc')
            ->get();

        $downloads_return = [];
        foreach($downloads as $download)
        {
            $downloads_return[] = [
                'downloadId'    => $download->id,
                'orderId'       => $download->order_id,
                'rollId'        => $download->roll,
                'downloadURL'   => $download->url,
                'failed'        => $download->failed ? true : false,
                'failedMessage' => $download->failed_message,
                'seenAt'        => $download->seen_at,
                'date'          => $download->created_at
            ];
        }

        $return_array['success']    = true;
        $return_array['data']       = $downloads_return;

        return $return_array;
    }

    /**
     * @param Request $request
     * @param $order_id
     * @param $roll_id
     * @return array
     */
    public function create(Request $request, $order_id, $roll_id)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $user   = $request->user();

        // Check that this order belongs to this customer
        $check_ownership = Order::where('customer_id', $user->ID)
                                ->where('id', $order_id)
                                ->first();
        if( ! $check_ownership)
        {
            $return_array['message'] = 'You don\'t have access to this Album.';

            return $return_array;
        }

        // Check for any existing download record for this object that hasn't failed/succeeded
        $existing_download = Download::where('customer_id', $user->ID)
                                ->where('order_id', $order_id)
                                ->where('roll', $roll_id)
                                ->where('ready', 1)
                                ->first();
        if($existing_download)
        {
            $return_array['message'] = 'Completed download for this roll already exists.';

            return $return_array;
        }

        // Check for a pending download record for this object that hasn't completed or failed
        // TODO: This is potentially going to be a problem for download jobs that fail but don't mark the download
        // as 'failed', as this response will not allow the creation of new downloads nor will the 'pending' download
        // ever get finished. Should potentially keep a time-relative where clause (for example, within the last 3 hours)
        // so that _eventually_ the user can issue a request for a new download record. (That might fail like the
        // previous one causing the above problem to repeat)
        $pending_download = Download::where('customer_id', $user->ID)
            ->where('order_id', $order_id)
            ->where('roll', $roll_id)
            ->where('ready', 0)
            ->where('failed', 0)
            ->first();
        if($pending_download)
        {
            $return_array['message'] = 'Download for this roll is already pending.';

            return $return_array;
        }

        // Check for multiple failed download attempts and return a response
        $failed_downloads = Download::where('customer_id', $user->ID)
            ->where('order_id', $order_id)
            ->where('roll', $roll_id)
            ->where('failed', 1)
            ->count();
        if($failed_downloads > 2)
        {
            $order = Order::find($order_id);
            $woo_id = $order->woo_id;

            $return_array['message'] = "There's been a problem creating your download, please contact support. Order: ".$woo_id." Roll: ".$roll_id;

            return $return_array;
        }

        // Create new Download record
        $download = new Download();
        $download->customer_id  = $user->ID;
        $download->order_id     = $order_id;
        $download->roll         = $roll_id;
        $download->queue        = 'priority_downloads';
        $download->save();

        // Push the download task to the queue
        $url = getenv('FOS_API_URL').'/api/mobile/download/'.$download->id.'/queue';
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

        $return_array['success']    = true;
        $return_array['data']       = 'successful update';

        return $return_array;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $user           = $request->user();
        $download_ids   = $request->get('downloadIds');

        foreach($download_ids as $download_id)
        {
            // Confirm that the download's submitted here belong to the user
            $download = Download::where('customer_id', $user->ID)
                            ->where('id', $download_id)
                            ->first();
            if( ! $download)
            {
                $return_array['message'] = 'Download not found';

                return $return_array;
            }

            $download->seen_at = Carbon::now();
            $download->save();
        }

        $return_array['success']    = true;
        $return_array['data']       = 'updated';

        return $return_array;
    }
}
