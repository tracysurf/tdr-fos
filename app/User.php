<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Corcel\Model\User as CorcelAuthenticatable;
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
     * @param $bearer_token
     * @return string
     */
    public function getPushNotificationToken($bearer_token)
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

        // TODO: Link this device name + user_id up on another table that stores the firebase/push notifications tokens

        return $device_name;
    }
}
