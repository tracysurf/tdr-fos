<?php

namespace App;

use App\TDR\FOSAPI\Client;
use Exception;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Corcel\Model\User as CorcelAuthenticatable;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Laravel\Sanctum\HasApiTokens;

// We're extending the Corce\Model\User here because it's properly connected to the wordpress database for the purposes
// of user/pass Auth & token creation and it's also connected to the wordpress database through Sanctum for API auth
// token validation

/**
 * Class User
 *
 * @package App
 * @property int $ID
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property \Illuminate\Support\Carbon $user_registered
 * @property string $user_activation_key
 * @property int $user_status
 * @property string $display_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\Corcel\Model\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Corcel\Model\Collection\MetaCollection|\Corcel\Model\Meta\UserMeta[] $fields
 * @property-read int|null $fields_count
 * @property-read \Corcel\Concerns\AdvancedCustomFields $acf
 * @property-read string $avatar
 * @property-read \Corcel\Model\Collection\MetaCollection|\Corcel\Model\Meta\UserMeta[] $meta
 * @property-read int|null $meta_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Notification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Corcel\Model\Post[] $posts
 * @property-read int|null $posts_count
 * @property-write mixed $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User hasMeta($meta, $value = null, $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|User hasMetaLike($meta, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newest()
 * @method static \Illuminate\Database\Eloquent\Builder|User oldest()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserActivationKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserNicename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserUrl($value)
 * @mixin \Eloquent
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
     * @return string
     */
    public function shippingAddressCacheKey()
    {
        return 'cus_ship_add_'.$this->ID;
    }

    /**
     * @return string
     */
    public function billingAddressCacheKey()
    {
        return 'cus_bill_add_'.$this->ID;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getShippingAddress()
    {
        // Send API request to FOS to get the customers shipping info
        $client     = new Client();
        $addresses  = $client->getCustomerAddresses($this->ID);
        $addresses  = json_decode($addresses, true);
        $address    = [];
        if(isset($addresses['success']) && $addresses['success'] === true)
        {
            $address = $addresses['data']['addresses']['shipping'];
        }
        else
        {
            \Log::debug('Shipping Address below');
            \Log::debug($addresses);
            throw new Exception('Failed to getShippingAddress');
        }

        // Cache it
        Cache::put($this->shippingAddressCacheKey(), $address, 90);
        return $address;
    }

    /**
     * @param array $address
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateShippingAddress($address = [])
    {
        // Dump local cache of shipping address
        Cache::delete($this->shippingAddressCacheKey());

        // Send API request to FOS to update the data
        $client     = new Client();
        $data       = ['shipping' => $address];
        $response   = $client->updateCustomerAddresses($this->ID, $data);

        // Update cache
        Cache::put($this->shippingAddressCacheKey(), $address, 90);
        return true;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getBillingAddress()
    {
        // Send API request to FOS to get the customers billing info
        $client     = new Client();
        $addresses  = $client->getCustomerAddresses($this->ID);
        $addresses  = json_decode($addresses, true);
        $address    = [];
        if(isset($addresses['success']) && $addresses['success'] === true)
        {
            $address = $addresses['data']['addresses']['billing'];
        }
        else
        {
            \Log::debug('Billing Address below');
            \Log::debug($addresses);
            throw new Exception('Failed to getBillingAddress');
        }

        // Cache it
        Cache::put($this->billingAddressCacheKey(), $address, 90);
        return $address;
    }

    /**
     * @param array $address
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function updateBillingAddress($address = [])
    {
        // Dump local cache of shipping address
        Cache::delete($this->billingAddressCacheKey());

        // Send API request to FOS to update the data
        $client     = new Client();
        $data       = ['billing' => $address];
        $response   = $client->updateCustomerAddresses($this->ID, $data);

        // Update cache
        Cache::put($this->billingAddressCacheKey(), $address, 90);
        return true;
    }

    /**
     * Get this users saved payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $tokens = \DB::connection('wordpress')
            ->table('woocommerce_payment_tokens')
            ->where('user_id', $this->ID)
            ->orderBy('token_id', 'desc')
            ->get();

        $payment_tokens = [];
        if($tokens->count())
        {
            foreach($tokens as $token)
            {
                // Get token meta data
                $token_meta_rows = \DB::connection('wordpress')
                    ->table('woocommerce_payment_tokenmeta')
                    ->where('payment_token_id', $token->token_id)
                    ->get();

                $token_meta_fields = [
                    'card_type'     => '',
                    'expiry_month'  => '',
                    'expiry_year'   => '',
                    'last4'         => '',
                ];

                // Loop through these meta rows returned by the database to map their respective key/values with the
                // $token_meta_fields array and it's keys
                foreach($token_meta_rows as $token_meta_row)
                {
                    $meta_key   = $token_meta_row->meta_key;
                    $meta_value = $token_meta_row->meta_value;

                    foreach($token_meta_fields as $token_meta_field_key => $token_meta_field_value)
                    {
                        if($token_meta_field_key === $meta_key)
                        {
                            $token_meta_fields[$token_meta_field_key] = $meta_value;
                        }
                    }
                }

                // Drop the token details and it's meta-details into an array that we'll return
                $payment_tokens[] = [
                    'token_id'      => $token->token_id,
                    'gateway_id'    => $token->gateway_id,
                    'token'         => $token->token,
                    'type'          => $token->type,
                    'is_default'    => $token->is_default,
                    'card_type'     => $token_meta_fields['card_type'],
                    'expiry_month'  => $token_meta_fields['expiry_month'],
                    'expiry_year'   => $token_meta_fields['expiry_year'],
                    'last4'         => $token_meta_fields['last4']
                ];
            }
        }

        return $payment_tokens;
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
        // TODO: At some point determine what an acceptable # of error_count is on the tokens and stop sending to them
        // TODO: Alternatively, do we reset the errors on the model when a notification is successfully sent? TBD
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

        // Get count of non-seen notifications
        $notification_count = Notification::where('customer_id', $this->ID)
            ->whereNull('seen_at')
            ->count();

        // Attach the android specific badge count
        $push_data['badge'] = $notification_count;

        // Loop through each token to send the notification
        $messages = [];
        foreach($device_tokens as $device_token)
        {
            // Setup this APNS config specifically to include the badge value
            $apns = ApnsConfig::fromArray([
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body'  => $body
                        ],
                        'badge' => $notification_count
                    ]
                ]
            ]);

            // Create the message object
            $message = CloudMessage::withTarget('token', $device_token)
                ->withNotification($notification)
                ->withData($push_data)
                ->withApnsConfig($apns);

            // Validate the message
            $validated = false;
            try{
                $messaging->validate($message);

                $validated = true;
            } catch (InvalidMessage $e)
            {
                // Flag the error for this token
                $push_notification_token = PushNotificationToken::where('customer_id', $this->ID)->where('token', $device_token)->first();
                if($push_notification_token)
                {
                    $push_notification_token->addError($e->getMessage());
                }
            }

            if($validated)
            {
                // Put it in a bucket to send later
                $messages[] = $message;
            }
        }

        // Loop through the bucket of messages to validate and send
        $sent_messages = [];
        foreach($messages as $message)
        {
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
        $tokens = [];
        foreach(PushNotificationToken::where('customer_id', $this->ID)->get() as $token)
        {
            $tokens[] = $token->token;
        }

        return $tokens;
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
