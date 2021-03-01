<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleRegisterRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VehicleRegisterCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VehicleRegisterCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\VehicleRegister::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehicle_register');
        CRUD::setEntityNameStrings('Vehicle', 'Vehicle Data');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::addColumn([
            'label'     => 'Employee',
            'type'      => 'select',
            'name'      => 'user_id',
            'entity'    => 'users', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User",
        ]);
        CRUD::column('customer_name');
        CRUD::column('phone');
        CRUD::column('pick_up');
        CRUD::column('expected_drop');
        CRUD::column('station');
        CRUD::column('vehicle');
        CRUD::column('status');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(VehicleRegisterRequest::class);

        CRUD::addField([
            'label'     => 'Employee',
            'type'      => 'select2',
            'name'      => 'user_id',
            'entity'    => 'users', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User",
        ]);
        CRUD::field('customer_name');
        CRUD::field('phone');
        CRUD::field('pick_up');
        CRUD::field('expected_drop');

        $stationData = array('' => '--Select--');
        $stations = \DB::table('stations')->get();
        foreach($stations as $station)
        {
            $stationData[$station->station_name] = $station->station_name;
        }

        CRUD::addField([
            'label'     => 'Station',
            'type'      => 'select2_from_array',
            'name'      => 'station',
            'options'   => $stationData
        ]);

        $vehicleData = array('' => '--Select--');
        $vehicles = \DB::table('vehicles')->get();
        foreach($vehicles as $vehicle)
        {
            $vehicleData[$vehicle->vehicle_number] = $vehicle->vehicle_number;
        }

        CRUD::addField([
            'label'     => 'Vehicle',
            'type'      => 'select2_from_array',
            'name'      => 'vehicle',
            'options'   => $vehicleData
        ]);
        CRUD::field('total_amount');
        CRUD::field('punchout_time');
        CRUD::field('return_time');
        CRUD::field('additional_hours');
        CRUD::field('additional_amount');

        $statusData = array('' => '--Select--', 'In' => 'In', 'Out' => 'Out');
        CRUD::addField([
            'label'     => 'Status',
            'type'      => 'select2_from_array',
            'name'      => 'status',
            'options'   => $statusData
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
