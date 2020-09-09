<?php

namespace App\Http\Controllers\API\V1;

use App\Download;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                'date'          => $download->created_at->format('n/j/Y')
            ];
        }

        $return_array['success']    = true;
        $return_array['data']       = $downloads_return;

        return $return_array;
    }
}
