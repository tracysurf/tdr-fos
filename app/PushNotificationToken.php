<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
