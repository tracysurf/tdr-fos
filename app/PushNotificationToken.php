<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PushNotificationToken
 *
 * @property int $id
 * @property int $customer_id
 * @property string|null $device_name
 * @property int|null $error_count
 * @property mixed $errors
 * @property string|null $last_error_at
 * @property string $token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereErrorCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereLastErrorAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PushNotificationToken extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'last_failed_at'
    ];

    /**
     * @param $value
     * @return mixed
     */
    public function getErrorsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setErrorsAttribute($value)
    {
        $this->attributes['errors'] = json_encode($value);
    }

    /**
     * @param string $error
     * @return bool
     */
    public function addError(string $error)
    {
        $errors         = $this->errors;
        $errors[]       = [
                'error_msg' => $error,
                'timestamp' => Carbon::now()->toDateTimeString()
        ];
        $this->errors           = $errors;
        $this->error_count      += 1;
        $this->last_error_at    = now();

        return $this->save();
    }
}
