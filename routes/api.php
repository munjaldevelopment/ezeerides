<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('customer-token', 'apiController@getCustomerType');

Route::post('customer-login', 'apiController@customerLogin');
Route::post('customer-verify', 'apiController@customerVerify');
Route::post('resend-sms', 'apiController@resendSMS');
Route::get('customer-profile', 'apiController@customer_profile');
Route::post('update-profile', 'apiController@update_profile');
Route::post('customer-documents', 'apiController@customer_documents');
Route::get('document-type', 'apiController@documentType');
Route::post('upload-documents', 'apiController@upload_documents');

Route::post('customer-logout', 'apiController@customer_logout');

Route::get('all-city', 'apiController@allCities');

Route::post('vehicle-center', 'apiController@all_center');

Route::get('ride-type', 'apiController@rideType');



Route::post('vehicle-filter', 'apiController@vehicle_filter');

