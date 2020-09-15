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
        $notifications  = Notification::where('customer_id', $user->ID)
                        ->orderBy('id', 'desc')
                        ->take(50)
                        ->get();

        $notifications_return = [];
        foreach($notifications as $notification)
        {
            $notifications_return[] = [
                'id'        => $notification->id,
                'text'      => $notification->body,
                'date'      => $notification->created_at,
                'seenAt'    => $notification->seen_at,
                'albumId'   => $notification->order_id,
                'downloadId'=> $notification->download_id,
            ];
        }

        // Get count of non-seen notifications
        $unseen_count = Notification::where('customer_id', $user->ID)
                        ->whereNull('seen_at')
                        ->count();

        $return_array['success']    = true;
        $return_array['data']       = $notifications_return;
        $return_array['badge']      = $unseen_count;

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
        /*
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
        */

        // Get all notifications not marked as seen
        $notifications = Notification::where('customer_id', $user->ID)
                            ->whereNull('seen_at')
                            ->get();

        // Mark them all seen
        foreach($notifications as $notification)
        {
            $notification->seen_at = Carbon::now();
            $notification->save();
        }

        $return_array['success']    = true;
        $return_array['data']       = 'updated';

        return $return_array;
    }
}
