<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('station', 'StationCrudController');
    Route::crud('vehicle', 'VehicleCrudController');

    Route::crud('employee', 'UserCrudController');
    Route::crud('vehicle_register', 'VehicleRegisterCrudController');

    Route::get('receive_amount/{id}', 'UserCrudController@receiveAmount');
    Route::crud('customers', 'CustomersCrudController');
    Route::crud('coupon', 'CouponCrudController');
    Route::crud('helmets', 'HelmetsCrudController');
    Route::crud('penalty', 'PenaltyCrudController');
    Route::crud('servicetype', 'ServiceTypeCrudController');
    Route::crud('vehicleservice', 'VehicleServiceCrudController');
}); // this should be the absolute last line of this file