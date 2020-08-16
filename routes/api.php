<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function (){

    Route::get('/albums', function(Request $request) {

        $user   = $request->user();
        $orders = App\Order::where('customer_id', $user->ID)->get();

        $return_array = [
            'message'   => '',
            'success'   => true,
        ];

        $data = [];
        foreach($orders as $order)
        {
            $photos         = \App\Photo::where('order_id', $order->id)->get();
            $rolls          = [];
            $thumbnail_url  = '';

            if($photos->count())
            {
                // Determine number of rolls
                foreach($photos as $photo)
                {
                    if( ! isset($rolls[$photo->roll]))
                        $rolls[$photo->roll] = 1;
                }

                $first_photo = \App\Photo::where('order_id', $order->id)->first();

                $thumbnail_url = $first_photo->thumbnailURL('_md');
            }

            $data[] = [
                'id'            => $order->id,
                'name'          => $order->name,
                'filmsCount'    => count($rolls),
                'imagesCount'   => $photos->count(),
                'date'          => $order->created_at->format('M j'),
                'imageUrl'      => $thumbnail_url,
            ];
        }

        $return_array['data'] = $data;

        return $return_array;
    });

    Route::get('/albums/{order_id}/rolls', function(Request $request, $order_id) {

        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => []
        ];

        $user   = $request->user();
        $order  = App\Order::find($order_id);

        if( $order->customer_id !== $user->ID)
        {
            return [
                'message'   => 'Order not found', // Purposefully obtuse here as to not confirm that the order exists
                'success'   => false,
            ];
        }

        if( ! $order)
        {
            return [
                'message'   => 'Order not found',
                'success'   => false
            ];
        }

        $photos = \App\Photo::where('order_id', $order_id)->orderBy('roll', 'asc')->get();

        $rolls = [];
        foreach($photos as $photo)
        {
            if( ! isset($rolls[$photo->roll]))
                $rolls[$photo->roll] = 1;
        }

        foreach($rolls as $roll_id => $nothing)
        {
            $roll_photos = App\Photo::where('order_id', $order_id)->where('roll', $roll_id)->orderBy('filename', 'asc')->get();

            $roll_photos_return = [];

            foreach($roll_photos as $roll_photo)
            {
                $image_urls = [
                    'sq'    => $roll_photo->thumbnailURL('_sq'),
                    'sm'    => $roll_photo->thumbnailURL('_sm'),
                    'lg'    => $roll_photo->thumbnailURL('_lg'),
                    'social'=> $roll_photo->thumbnailURL('_social'),
                ];

                $roll_photos_return[] = [
                    'id'            => $roll_photo->id,
                    'image_urls'    => $image_urls,
                    'liked'         => $roll_photo->favorite ? true : false
                ];

                $roll_name = $roll_photo->roll_name;
            }

            $return_array['data'][] = [
                'id'    => $roll_id,
                'name'  => $roll_name,
                'images'=> $roll_photos_return
            ];
        }

        $return_array['success'] = true;

        return $return_array;
    });

    Route::put('/albums/{order_id}', function(Request $request, $order_id){

        $return_array = [
            'message'   => '',
            'success'   => false,
            'data'      => null,
        ];

        $user   = $request->user();
        $order  = App\Order::find($order_id);

        if( $order->customer_id !== $user->ID)
        {
            // Purposefully being obtuse here as to not confirm existence of this order id
            $return_array['message'] = 'Order not found';
            return $return_array;
        }

        if( ! $order)
        {
            $return_array['message'] = 'Order not found';
            return $return_array;
        }

        // Update the name if applicable
        $name = $request->get('name');
        if($order->name !== $name)
        {
            $order->name = $name;
            $order->save();
        }

        // Check for updated rolls...
        /*
        if($request->has('updatedRolls'))
        {
            $updated_rolls = $request->get('updatedRolls');

            foreach($updated_rolls as $updated_roll)
            {
                $updated_roll_id    = $updated_roll->id;
                $updated_roll_name  = $updated_roll->name;

                // Get all photos on this order with this roll ID and change the roll_name on them
                $photos = \App\Photo::where('order_id', $order_id)->where('roll', $updated_roll_id)->get();
                foreach($photos as $photo)
                {
                    $photo->roll_name = $updated_roll_name;
                    $photo->save();
                }
            }
        }
        */

        $return_array['data']       = 'successful update';
        $return_array['success']    = true;

        return $return_array;
    });

});

Route::post('/auth/signIn', function (Request $request) {
    $request->validate([
        'email'         => 'required|email',
        'password'      => 'required',
        'device_name'   => 'required',
    ]);

    $user = User::where('user_email', $request->email)->first();

    if (! $user || ! Auth::validate(['email' => $request->email, 'password' => $request->password])) {

        return [
            'message'   => 'The username or password is incorrect.',
            'success'   => false,
            'data'      => null
        ];

        /*
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
        */
    }

    $token = $user->createToken($request->device_name)->plainTextToken;

    return [
        'message'   => '',
        'success'   => true,
        'data'      => $token
    ];
});
