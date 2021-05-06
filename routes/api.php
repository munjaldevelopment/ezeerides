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

Route::get('vehicle-rides', 'apiController@vehicleRides');

Route::post('vehicle-model-center', 'apiController@centerByModel');

Route::post('vehicle-filter', 'apiController@vehicle_filter');

Route::post('bike-detail', 'apiController@bike_detail');

Route::post('coupon-list', 'apiController@coupon_listing');

Route::post('make-payment', 'apiController@make_payment');

Route::post('confirm-payment', 'apiController@confirm_payment');

Route::post('wallet-amount', 'apiController@wallet_amount');

Route::post('wallet-history', 'apiController@wallet_history');

Route::post('add-money', 'apiController@add_money');

Route::post('confirm-wallet-payment', 'apiController@confirm_wallet_amount');

Route::post('notification-list', 'apiController@notification_list');

Route::post('booking-list', 'apiController@customer_booking');

Route::post('booking-detail', 'apiController@booking_detail');

Route::post('cancel-booking', 'apiController@canceledBooking');

Route::post('contact-us', 'apiController@contact_us');

Route::post('about-us', 'apiController@about_us');

Route::post('privacy', 'apiController@privacy');

Route::post('need-help', 'apiController@need_help');

Route::post('create-ticket', 'apiController@add_support_query');

Route::post('ticket-history', 'apiController@ticket_history');

Route::post('policies', 'apiController@policies');

Route::post('home-notification-list', 'apiController@home_notification_list');

/* Employee API */
Route::post('employee-login', 'apiEmployeeController@employeeLogin');

Route::post('employee-verify', 'apiEmployeeController@employeeVerify');

Route::post('employee-resend-sms', 'apiEmployeeController@resendSMS');

Route::post('employee-profile', 'apiEmployeeController@employee_profile');

Route::post('employee-logout', 'apiEmployeeController@empoyee_logout');

Route::post('pending-cash-transactions', 'apiEmployeeController@pending_cash_transactions');

Route::post('all-cash-transactions', 'apiEmployeeController@all_cash_transactions');

Route::post('tranfer-cash', 'apiEmployeeController@tranfer_cash');

Route::post('expences-type', 'apiEmployeeController@expences_type');

Route::post('add-cash-expense', 'apiEmployeeController@add_cash_expense');

Route::post('fleet-calendar', 'apiEmployeeController@fleet_calendar');

Route::post('our-fleet', 'apiEmployeeController@our_fleet');

Route::post('fleet-detail', 'apiEmployeeController@fleet_detail');

Route::post('all-emp-city', 'apiEmployeeController@allCities');

Route::post('all-emp-center', 'apiEmployeeController@all_emp_center');

Route::post('fleet-booking', 'apiEmployeeController@fleet_booking');

Route::post('current-booking-vehicle', 'apiEmployeeController@current_booking_vehicle');

Route::post('tranfer-cash', 'apiEmployeeController@tranfer_cash');


Route::post('upcoming-booking-vehicle', 'apiEmployeeController@upcoming_booking_vehicle');

Route::post('old-booking-vehicle', 'apiEmployeeController@old_booking_vehicle');

Route::post('search-order', 'apiEmployeeController@search_order');

Route::post('booking-details', 'apiEmployeeController@booking_details');

Route::post('track-vehicle', 'apiEmployeeController@track_vehicle');

Route::post('confirm-bike-detail', 'apiEmployeeController@booking_bike_detail');

Route::post('prepare-to-delivery', 'apiEmployeeController@prepareToDelivery');

Route::post('deliver-vehicle', 'apiEmployeeController@deliver_vehicle');

Route::post('customer-return-vehicle', 'apiEmployeeController@customerReturnVehicle');

Route::post('return-vehicle', 'apiEmployeeController@return_vehicle');

Route::post('get-customer-detail', 'apiEmployeeController@getCustomerDetail');

Route::post('reserve-bike', 'apiEmployeeController@reserve_bike');

Route::post('employee-attendance', 'apiEmployeeController@employee_attendance');