<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
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

Route::get('/callback-verification','FacebookWebhookController@CallbackVerification');
Route::post('/callback-verification','FacebookWebhookController@CallbackVerification');
Route::get('/getLeadData','FacebookWebhookController@getLeadData');
Route::post('/set-token','FacebookWebhookController@setToken');


