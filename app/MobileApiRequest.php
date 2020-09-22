<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MobileApiRequest
 *
 * @package App
 * @property int $id
 * @property string $endpoint
 * @property string $controller
 * @property int|null $customer_id
 * @property int|null $order_id
 * @property int $success
 * @property float|null $execution_time
 * @property mixed $parameters
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereController($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereExecutionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileApiRequest whereUpdatedAt($value)
 * @mixin \Eloquent
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
