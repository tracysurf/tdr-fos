<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Notification
 *
 * @property int $id
 * @property int $notification_type_id
 * @property int $customer_id
 * @property string|null $body
 * @property string|null $push_sent_at
 * @property string|null $push_body
 * @property string|null $sms_sent_at
 * @property string|null $sms_body
 * @property \Illuminate\Support\Carbon|null $seen_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $order_id
 * @property int|null $download_id
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDownloadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotificationTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification wherePushBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification wherePushSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSmsBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSmsSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    protected $dates            = [
        'created_at',
        'updated_at',
        'seen_at',
        'sent_at'
    ];
}
