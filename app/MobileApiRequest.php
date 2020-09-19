<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MobileApiRequest
 * @package App
 */
class MobileApiRequest extends Model
{
    protected $guarded = ['id', 'created_at'];

    /**
     * @param $value
     * @return mixed
     */
    public function getParametersAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setParametersAttribute($value)
    {
        if(isset($value['password']))
        {
            $value['password'] = 'redacted';
        }

        $this->attributes['parameters'] = json_encode($value);
    }

    /**
     * @param $start_time
     * @return mixed
     */
    public function updateSuccess($start_time)
    {
        $this->success          = true;
        $this->execution_time   = microtime(true) - $start_time;

        return $this->save();
    }

    /**
     * @param $start_time
     * @param string $error_message
     * @return mixed
     */
    public function updateFailed($start_time, $error_message = '')
    {
        $this->error_message    = $error_message;
        $this->execution_time   = microtime(true) - $start_time;

        return $this->save();
    }
}
