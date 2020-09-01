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

    Route::get('/albums',                               '\App\Http\Controllers\APIOrderController@index');
    Route::put('/albums/{order_id}',                    '\App\Http\Controllers\APIOrderController@update');

    Route::get('/albums/{order_id}/rolls',              '\App\Http\Controllers\APIRollController@index');
    Route::put('/albums/{order_id}/rolls/{roll_id}',    '\App\Http\Controllers\APIRollController@update');

    Route::get('/notifications',                        '\App\Http\Controllers\APINotificationController@index');

    Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}',          '\App\Http\Controllers\APIPhotoController@update');
    Route::put('/albums/{order_id}/rolls/{roll_id}/images/{photo_id}/rotate',   '\App\Http\Controllers\APIPhotoController@rotate');

    Route::delete('/albums/{order_id}/rolls/{roll_id}/images',                  '\App\Http\Controllers\APIPhotoController@delete');
    
});

Route::post('/auth/signIn', '\App\Http\Controllers\APIAuthController@signIn');
