<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use App\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class NotificationController
 * @package App\Http\Controllers
 */
class NotificationController extends Controller
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

        $user           = $request->user();
        $notifications  = Notification::where('customer_id', $user->ID)->orderBy('id', 'desc')->take(50)->get();

        $notifications_return = [];
        foreach($notifications as $notification)
        {
            $notifications_return[] = [
                'id'        => $notification->id,
                'text'      => $notification->body,
                'date'      => $notification->created_at->format('n/j/Y'),
                'seenAt'    => is_null($notification->seen_at) ? $notification->seen_at : $notification->seen_at->format('n/j/Y'),
                'albumId'   => $notification->order_id,
                'downloadId'=> $notification->download_id,
            ];
        }

        $return_array['success']    = true;
        $return_array['data']       = $notifications_return;

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

        $user               = $request->user();
        $notification_ids   = $request->get('notificationIds');

        foreach($notification_ids as $notification_id)
        {
            // Confirm that the notification's submitted here belong to the user
            $notification = Notification::where('customer_id', $user->ID)
                ->where('id', $notification_id)
                ->first();
            if( ! $notification)
            {
                $return_array['message'] = 'Notification not found';

                return $return_array;
            }

            $notification->seen_at = Carbon::now();
            $notification->save();
        }

        $return_array['success']    = true;
        $return_array['data']       = 'updated';

        return $return_array;
    }
}
