<?php

namespace App;

use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Corcel\Model\User as CorcelAuthenticatable;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Messaging\CloudMessage;
use Laravel\Sanctum\HasApiTokens;

// We're extending the Corce\Model\User here because it's properly connected to the wordpress database for the purposes
// of user/pass Auth & token creation and it's also connected to the wordpress database through Sanctum for API auth
// token validation

/**
 * Class User
 * @package App
 */
class User extends CorcelAuthenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return bool
     */
    public function hasSMSEnabled()
    {
        $value = \DB::connection('wordpress')
            ->table('usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', 'billing_sms_notification')
            ->first();

        $sms_enabled = false;
        if($value)
            // The value is stored as a boolean string, so if it's === '1' then it's true, otherwise it's false.
            $sms_enabled = $value->meta_value === '1';

        return $sms_enabled;
    }

    /**
     * @param $value
     * @return bool
     */
    public function updateSMSEnabled($value)
    {
        if($value === 'true' || $value === true || $value === 1 | $value === '1')
            $value = 1;
        else
            $value = 0;

        $update = \DB::connection('wordpress')
            ->table('usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', 'billing_sms_notification')
            ->update(['meta_value' => $value]);

        return true;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        $value = \DB::connection('wordpress')
            ->table('usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', 'billing_mobile_phone')
            ->first();

        $phone = '';
        if($value)
            $phone = $value->meta_value;

        return $phone;
    }

    /**
     * @param $phone_number
     * @return mixed
     */
    public function updatePhoneNumber($phone_number)
    {
        $update = \DB::connection('wordpress')
            ->table('usermeta')
            ->where('user_id', $this->ID)
            ->where('meta_key', 'billing_mobile_phone')
            ->update(['meta_value' => $phone_number]);

        return $update;
    }

    /**
     * @param $title
     * @param $body
     * @param array $data_array
     * @param null $image_url
     * @return array
     * @throws Exception
     */
    public function sendPushNotification($title, $body, $data_array = [], $image_url = null)
    {
        // Get device tokens for this user
        $device_tokens  = $this->getPushNotificationTokens();

        // Configure the notification object
        $notification   = \Kreait\Firebase\Messaging\Notification::create(
            $title,
            $body,
            $image_url);

        // Associate any data with the notification
        $push_data      = $data_array;

        // Initiate the messaging object
        $messaging = app('firebase.messaging');

        // Loop through each token to send the notification
        $messages = [];
        foreach($device_tokens as $device_token)
        {
            // Create the message object
            $message = CloudMessage::withTarget('token', $device_token)
                ->withNotification($notification)
                ->withData($push_data);

            // Put it in a bucket to send later
            $messages[] = $message;
        }

        // Loop through the bucket of messages to validate and send
        $sent_messages = [];
        foreach($messages as $message)
        {
            // Validate the message
            try{
                $messaging->validate($message);
            } catch (InvalidMessage $e)
            {
                throw $e;
            }

            // Send the message
            try {
                $response = $messaging->send($message);

                if(isset($response['name']))
                {
                    $sent_messages[] = $response['name'];
                }

            } catch (Exception $e)
            {
                throw $e;
            }
        }

        return $sent_messages;
    }

    /**
     * Get all push notification device tokens for this customer
     *
     * @return mixed
     */
    public function getPushNotificationTokens()
    {
        return PushNotificationToken::where('customer_id', $this->ID)->get();
    }

    /**
     * Get the specific push notification token associated with a specific device name from the bearer token
     * (The device_name is associated to the bearer token)
     *
     * @param $bearer_token
     * @return string
     */
    public function getDevicePushNotificationTokenFromBearer($bearer_token)
    {
        $device_name = $this->getDeviceNameFromBearerToken($bearer_token);

        $token = PushNotificationToken::where('customer_id', $this->ID)->where('device_name', $device_name)->first();

        if( ! $token)
            $push_token = '';
        else
            $push_token = $token->token;

        return $push_token;
    }

    /**
     * @param $bearer_token
     * @param $push_token
     * @return mixed
     */
    public function updatePushNotificationToken($bearer_token, $push_token)
    {
        $device_name = $this->getDeviceNameFromBearerToken($bearer_token);

        // Is there an existing token?
        $token = PushNotificationToken::where('customer_id', $this->ID)
            ->where('device_name', $device_name)
            ->first();

        // Existing token stored for this device, let's update it
        if($token)
        {
            $token->token = $push_token;
            $token->save();
        }
        else
        {
            // There's no token stored for this device yet, let's store one
            $token              = new PushNotificationToken();
            $token->customer_id = $this->ID;
            $token->device_name = $device_name;
            $token->token       = $push_token;
            $token->save();
        }

        return $token->token;
    }

    /**
     * @param $bearer_token
     * @return string
     */
    public function getDeviceNameFromBearerToken($bearer_token)
    {
        $bearer_token       = explode('|', $bearer_token);
        $bearer_token_id    = $bearer_token[0]; // Our token id for this token

        $device_name = '';
        foreach($this->tokens as $token)
        {
            if((int)$token->id === (int)$bearer_token_id)
            {
                $device_name = $token->name;
            }
        }

        return $device_name;
    }
}
