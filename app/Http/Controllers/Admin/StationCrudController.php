<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StationRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\User;
/**
 * Class StationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StationCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Station::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/station');
        CRUD::setEntityNameStrings('station', 'stations');

        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->allowAccess(['list','create', 'update', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }

        $cityData = array();
        $cities = \DB::table('cities')->get();
        foreach ($cities as $key => $row) {
            $cityData[$row->id] = $row->city;
        }

        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'city_id',
              'label'=> 'City'
            ],
            $cityData,
            function($value) {
                $this->crud->addClause('where', 'city_id', $value);
        });
        $this->crud->addColumn([
            'label'     => 'City Name',
            'type'      => 'select',
            'name'      => 'city_id',
            'entity'    => 'allCities', //function name
            'attribute' => 'city', //name of fields in models table like districts
            'model'     => "App\Models\City", //name of Models

         ]);
        CRUD::column('station_name');

        $this->crud->addColumn([
            'label'     => 'Employee Name',
            'type'      => 'select',
            'name'      => 'employee_id',
            'entity'    => 'allEmployes', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models

         ]);
        CRUD::column('status');



        $city_list = array();
            
        $city_list[0] = 'Select';
        $cities = \DB::table('cities')->orderBy('id')->get();
        if($cities)
        {
            foreach($cities as $row)
            {
                $city_list[$row->id] = $row->city;
            }
        }

        $this->crud->addField([
            'name' => 'city_id',
            'label' => 'City',
            'type'      => 'select2_from_array',
            'options'   => $city_list,
            'hint' => '',
        ]);

        CRUD::field('station_name');

        $employee_list = array();
            
        $employee_list[0] = 'Select';
        $emplist= User::whereHas('roles', function($q){
                    $q->where('name', 'Employee');
                  })->get();
        if($emplist)
        {
            foreach($emplist as $row)
            {
                $employee_list[$row->id] = $row->name;
            }
        }

        $this->crud->addField([
            'name' => 'employee_id',
            'label' => 'Employee',
            'type'      => 'select2_from_array',
            'options'   => $employee_list,
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select2_from_array',
            'options' => ['Live' => 'Live', 'Not Live' => 'Not Live'],
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
        $this->crud->addColumns([
            [
                'name'  => 'station_name',
                'label' => 'Station Name',
                'type'  => 'text',
            ],
        ]);

        //CRUD::setFromDb(); // columns

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
        $this->addStationFields();
        CRUD::setValidation(StationRequest::class);

        //CRUD::setFromDb(); // fields

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    public function addStationFields()
    {
        $this->crud->addFields([
            [
                'name'  => 'station_name',
                'label' => 'Station Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'station_address',
                'label' => 'Station Address',
                'type'  => 'textarea',
            ],
            [
                'type' => 'select2_multiple',
                'name' => 'station_vehicles', // the relationship name in your Model
                'entity' => 'station_vehicles', // the relationship name in your Model
                'attribute' => 'vehicle_number', // attribute on Article that is shown to admin
                'pivot' => true,
            ],
        ]);
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
