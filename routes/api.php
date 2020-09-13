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

    Route::get('/albums',                               '\App\Http\Controllers\API\V1\OrderController@index');
    Route::put('/albums/{order_id}',                    '\App\Http\Controllers\API\V1\OrderController@update');

    Route::get('/albums/{order_id}/rolls',              '\App\Http\Controllers\API\V1\RollController@index');
    Route::put('/albums/{order_id}/rolls/{roll_id}',    '\App\Http\Controllers\API\V1\RollController@update');

    Route::get('/notifications',                        '\App\Http\Controllers\API\V1\NotificationController@index');
    Route::put('/notifications',                        '\App\Http\Controllers\API\V1\NotificationController@update');

    Route::get('/downloads',                                    '\App\Http\Controllers\API\V1\DownloadController@index');
    Route::put('/downloads',                                    '\App\Http\Controllers\API\V1\DownloadController@update');
    Route::put('/albums/{order_id}/rolls/{roll_id}/download',   '\App\Http\Controllers\API\V1\DownloadController@create');

    Route::get('/profile',                              '\App\Http\Controllers\API\V1\UserController@show');
    Route::put('/profile',                              '\App\Http\Controllers\API\V1\UserController@update');


    Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}',          '\App\Http\Controllers\API\V1\PhotoController@update');
    Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}/rotate',   '\App\Http\Controllers\API\V1\PhotoController@rotate');

    Route::delete('/albums/{order_id}/rolls/{roll_id}/images',                  '\App\Http\Controllers\API\V1\PhotoController@delete');

});

Route::post('/auth/signIn', '\App\Http\Controllers\API\V1\AuthController@signIn');

Route::middleware('privateapiauth')->group(function(){
    Route::post('/push-notification', '\App\Http\Controllers\API\V1\PushNotificationController@create');
});
