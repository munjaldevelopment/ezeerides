<?php

use Illuminate\Support\Facades\Route;
//

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

Route::post('post-login', 'HomeController@postLogin')->name('post-login');
Route::post('post-password', 'HomeController@postPassword')->name('post-password');
Route::post('post-register', 'HomeController@postRegister')->name('post-register');

Route::get('dashboard', 'HomeController@dashboard')->name('dashboard');

Route::get('booking_verify/{id}', 'HomeController@bookingVerify')->name('bookingVerify');

Route::get('return_vehicle/{id}', 'HomeController@returnVehicle')->name('returnVehicle');

/*Route::get('/dashboard', function () {
	$vehicles = Vehicle::get();
	$stations = Station::get();
    return view('dashboard', compact('vehicles', 'stations'));
});*/

Route::post('register_result', 'RegisterController@RegisterResult');
Route::post('save_return', 'RegisterController@saveReturn');
Route::get('history', 'HistoryController@Index')->name('history');

Route::post('showVehicle', 'HomeController@showVehicle');
Route::post('save_booking_verify', 'RegisterController@saveBookingVerify');

Route::get('initiate','OrderController@initiate')->name('initiate.payment');
Route::post('payment','OrderController@pay')->name('make.payment');
Route::post('payment/status', 'OrderController@paymentCallback')->name('status');

Route::get('/terms', 'StaticPageController@termsPage');
Route::get('/privacy', 'StaticPageController@privacyPage');
Route::get('/faq', 'StaticPageController@faqPage');

Route::get('payment-razorpay', 'PaymentController@create')->name('paywithrazorpay');
Route::post('payment', 'PaymentController@payment')->name('payment');

