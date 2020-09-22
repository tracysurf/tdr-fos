<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Download
 *
 * @property int $id
 * @property int $customer_id
 * @property int $order_id
 * @property string $roll
 * @property string|null $url
 * @property string|null $queue
 * @property int $ready
 * @property int|null $elapsed_time
 * @property int $failed
 * @property string|null $failed_message
 * @property \Illuminate\Support\Carbon|null $seen_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Download newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Download newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Download query()
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereElapsedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereFailedMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereReady($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereRoll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Download whereUrl($value)
 * @mixin \Eloquent
 */
class Download extends Model
{
    protected $dates            = [
        'created_at',
        'updated_at',
        'seen_at'
    ];
}
