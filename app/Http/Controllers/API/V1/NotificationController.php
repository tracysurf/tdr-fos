<?php

namespace App\Http\Controllers\API\V1;

use Carbon\Carbon;
use App\Notification;
use Illuminate\Http\Request;

/**
 * Class NotificationController
 * @package App\Http\Controllers
 */
class NotificationController extends BaseController
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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

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

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function unseen(Request $request)
    {
        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user           = $request->user();

        $return_array['data'] = Notification::where('customer_id', $user->ID)
                        ->whereNull('seen_at')
                        ->count();

        $return_array['success'] = true;

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

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

        $start = microtime(true);
        $api_request_record = $this->createApiRequestRecord($request, null);

        $user               = $request->user();

        /*
         * Previous method included marking specific id's as seen, we've since changed to 'all' as
         * seen when the endpoint is called, leaving this here in case we go back.
         *
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

        // Update $api_request_record
        $api_request_record->updateSuccess($start);

        return $return_array;
    }
}
