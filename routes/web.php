<?php

use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;
use App\Models\Station;

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
	$vehicles = Vehicle::get();
	$stations = Station::get();
    return view('home', compact('vehicles', 'stations'));
});

Route::post('register_result', 'RegisterController@RegisterResult');
Route::get('/history', 'HistoryController@Index');
