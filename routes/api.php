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

        $user   = $request->user();
        $order  = App\Order::find($order_id);

        $rolls  = \DB::table('photos')->where('order_id', $order_id)->select('roll')->orderBy('roll', 'asc')->distinct();

        $photos = [];
        foreach($rolls as $roll)
        {
            $roll_photos = App\Photo::where('order_id', $order_id)->where('roll', $roll)->orderBy('filename', 'asc')->get();

            $photos[$roll] = $roll_photos;
        }



        if( ! $order)
        {
            return [
                'message'   => 'Order not found',
                'success'   => false
            ];
        }

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
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken($request->device_name)->plainTextToken;

    return [
        'message'   => '',
        'success'   => true,
        'data'      => $token
    ];
});
