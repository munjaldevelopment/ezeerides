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

Route::post('bike-detail', 'apiController@bike_detail');

Route::post('coupon-list', 'apiController@coupon_listing');

Route::post('make-payment', 'apiController@make_payment');

Route::post('confirm-payment', 'apiController@confirm_payment');

Route::post('wallet-amount', 'apiController@wallet_amount');

Route::post('add-money', 'apiController@add_money');

Route::post('confirm-wallet-payment', 'apiController@confirm_wallet_amount');

Route::post('notification-list', 'apiController@notification_list');

Route::post('booking-list', 'apiController@customer_booking');

Route::post('booking-detail', 'apiController@booking_detail');

Route::post('contact-us', 'apiController@contact_us');

Route::post('about-us', 'apiController@about_us');

Route::post('privacy', 'apiController@privacy');


/* Employee API */
Route::post('employee-login', 'apiEmployeeController@employeeLogin');

Route::post('employee-verify', 'apiEmployeeController@employeeVerify');

Route::post('employee-resend-sms', 'apiEmployeeController@resendSMS');

Route::post('employee-profile', 'apiEmployeeController@employee_profile');

Route::post('employee-logout', 'apiEmployeeController@empoyee_logout');

Route::post('current-booking-vehicle', 'apiEmployeeController@current_booking_vehicle');

Route::post('upcoming-booking-vehicle', 'apiEmployeeController@upcoming_booking_vehicle');

Route::post('old-booking-vehicle', 'apiEmployeeController@old_booking_vehicle');