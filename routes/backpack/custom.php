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
    Route::crud('vehiclegallery', 'VehicleGalleryCrudController');
    Route::crud('customerdocuments', 'CustomerDocumentsCrudController');
    Route::crud('city', 'CityCrudController');
    Route::crud('damages', 'DamagesCrudController');
    Route::crud('pendingservices', 'PendingServicesCrudController');
    Route::crud('completedservices', 'CompletedServicesCrudController');
    Route::crud('stationpremiums', 'StationPremiumsCrudController');

    Route::get('getStation/{city_id}', 'StationPremiumsCrudController@getStations');
    Route::crud('stationholidays', 'StationHolidaysCrudController');
    Route::get('getHolidayStation/{city_id}', 'StationHolidaysCrudController@getStations');
    Route::crud('employeeattendance', 'EmployeeAttendanceCrudController');
    Route::crud('vehiclemodels', 'VehicleModelsCrudController');
    Route::crud('bookings', 'BookingsCrudController');

    Route::get('getEmployee/{station_id}', 'VehicleRegisterCrudController@getEmployee');
    Route::get('getVehicle/{station_id}', 'VehicleRegisterCrudController@getVehicle');
    Route::crud('upcoming_bookings', 'Upcoming_bookingsCrudController');
    Route::crud('old_bookings', 'Old_bookingsCrudController');
    Route::crud('canceled_bookings', 'Canceled_bookingsCrudController');
    Route::crud('overdue_bookings', 'Overdue_bookingsCrudController');
    Route::crud('bookedvehicleimages', 'BookedVehicleImagesCrudController');

    Route::get('getVehicle', 'VehicleRegisterCrudController@getVehicle');
    Route::crud('station_vehicles', 'Station_vehiclesCrudController');
    Route::crud('supporttickets', 'SupportTicketsCrudController');
    Route::crud('needhelp', 'NeedHelpCrudController');
}); // this should be the absolute last line of this file