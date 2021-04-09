<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Station_vehiclesRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class Station_vehiclesCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class Station_vehiclesCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Station_vehicles::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/station_vehicles');
        CRUD::setEntityNameStrings('Vehicle', 'station vehicles');

        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->denyAccess(['update']);    
            $this->crud->allowAccess(['list','create', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }

        $stationData = array();
        $stations = \DB::table('stations')->get();
        foreach ($stations as $key => $row) {
            $stationData[$row->id] = $row->station_name ;
            # code...
        }
        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'station_id',
              'label'=> 'Center Name'
            ],
            $stationData,
            function($value) {
                $this->crud->addClause('where', 'station_id', $value);
        });

         $this->crud->addColumn([
            'label'     => 'Station Name',
            'type'      => 'select',
            'name'      => 'station_id',
            'entity'    => 'allStation', //function name
            'attribute' => 'station_name', //name of fields in models table like districts
            'model'     => "App\Models\Station", //name of Models

         ]);

       $this->crud->addColumn([
            'label'     => 'Vehicle Number',
            'type'      => 'select',
            'name'      => 'vehicle_id',
            'entity'    => 'allVehicle', //function name
            'attribute' => 'vehicle_number', //name of fields in models table like districts
            'model'     => "App\Models\Vehicle", //name of Models

         ]);

        $this->crud->addField([
            'name' => 'station_id',
            'label' => 'Station',
            'type'      => 'select2_from_array',
            'options'   => $stationData,
            'hint' => '',
        ]);

        $vehicle_list = array();
       $vehicleidarr = array();
         
        $vehicleIds = \DB::table('station_has_vehicles')->distinct()->select('vehicle_id')->get();
        if($vehicleIds){
            foreach ($vehicleIds as $vid) {
                $vehicleidarr[] = $vid->vehicle_id;
            }
        }
       // print_r($vehicleidarr);
            
        $vehicle_list[0] = 'Select';
        $vehicles = \DB::table('vehicles')->whereNotIn('id', $vehicleidarr)->orderBy('id')->get();
        if($vehicles)
        {
            foreach($vehicles as $row)
            {
                $vehicle_list[$row->id] = $row->vehicle_number;
            }
        }
        $this->crud->addField([
            'name' => 'vehicle_id',
            'label' => 'Vehicle Number',
            'type'      => 'select2_from_array',
            'options'   => $vehicle_list,
            'hint' => '',
        ]);
         
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // columns

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
        CRUD::setValidation(Station_vehiclesRequest::class);

        CRUD::setFromDb(); // fields

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
