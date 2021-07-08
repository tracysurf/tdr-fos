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

// V1 API routes
Route::prefix('v1')->namespace('API\\V1')->group(function() {

    // Auth required routes
    Route::middleware('auth:sanctum')->group(function (){

        Route::get('/albums',                               'OrderController@index');
        Route::put('/albums/{order_id}',                    'OrderController@update');

        Route::get('/albums/{order_id}/rolls',              'RollController@index');
        Route::put('/albums/{order_id}/rolls/{roll_id}',    'RollController@update');

        Route::get('/notifications',                        'NotificationController@index');
        Route::get('/notifications/unseen',                 'NotificationController@unseen');
        Route::put('/notifications',                        'NotificationController@update');

        Route::get('/downloads',                            'DownloadController@index');
        Route::put('/downloads',                            'DownloadController@update');

        Route::get('/profile',                              'UserController@show');
        Route::put('/profile',                              'UserController@update');

        Route::get('/shop/products',                        'ShopController@products');

        Route::put('/albums/{order_id}/rolls/{roll_id}/download',                   'DownloadController@create'); // Note: Notice, DownloadController
        Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}',          'PhotoController@update');
        Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}/rotate',   'PhotoController@rotate');
        Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}/save_editor', 'PhotoController@save_editor');

        Route::delete('/albums/{order_id}/rolls/{roll_id}/images',                  'PhotoController@delete');

        Route::post('/orders',                              'OrderController@create');
        Route::post('/orders/{order_id}/payment',           'OrderController@create');

    });

    // Sign in
    Route::post('/auth/signIn', 'AuthController@signIn');

    // Local/private API between FOS -> this project
    Route::middleware('privateapiauth')->group(function(){

        Route::post('/push-notification', 'PushNotificationController@create');
        
    });
});
