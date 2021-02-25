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

/*Route::get('/dashboard', function () {
	$vehicles = Vehicle::get();
	$stations = Station::get();
    return view('dashboard', compact('vehicles', 'stations'));
});*/

Route::post('register_result', 'RegisterController@RegisterResult');
Route::get('/history', 'HistoryController@Index');
