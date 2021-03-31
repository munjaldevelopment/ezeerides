<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StationHolidaysRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class StationHolidaysCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StationHolidaysCrudController extends CrudController
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
        CRUD::setModel(\App\Models\StationHolidays::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/stationholidays');
        CRUD::setEntityNameStrings('Station Holiday', 'Station Holiday');

         $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->allowAccess(['list','create', 'update', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }

        $this->crud->setCreateView('admin.create-station-holiday-form');
        $this->crud->setUpdateView('admin.edit-station-holiday-form');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        //CRUD::setFromDb(); // columns

        $this->crud->addColumn([
            'label'     => 'City',
            'type'      => 'select',
            'name'      => 'city_id',
            'entity'    => 'allCities', //function name
            'attribute' => 'city', //name of fields in models table like districts
            'model'     => "App\Models\City", //name of Models

         ]);

        $this->crud->addColumn([
            'label'     => 'Station',
            'type'      => 'select',
            'name'      => 'station_id',
            'entity'    => 'allStations', //function name
            'attribute' => 'station_name', //name of fields in models table like districts
            'model'     => "App\Models\Station", //name of Models

         ]);

        
        $this->crud->addColumn([
            'label'     => 'From Date',
            'type'      => 'text',
            'name'      => 'from_date',
         ]);

         $this->crud->addColumn([
            'label'     => 'To Date',
            'type'      => 'text',
            'name'      => 'to_date',
         ]);

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
        CRUD::setValidation(StationHolidaysRequest::class);

       // CRUD::setFromDb(); // fields

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
            'attributes'   => [
                'id' => 'city_id',
                'onchange' => 'getStations(this.value);'
            ],
            'hint' => '',
        ]);

       $this->crud->addField([
            'label'     => 'Station Name',
            'type'      => 'select2_from_array',
            'name'      => 'station_id',
            'options'   => array(),
            'attributes'   => [
                'id' => 'station_id'
            ]
        ]);

        $this->crud->addField([
            'label'     => 'From Date',
            'type'      => 'datetime',
            'name'      => 'from_date'
            
        ]);
        
        $this->crud->addField([
            'label'     => 'To Date',
            'type'      => 'datetime',
            'name'      => 'to_date'
            
        ]);

        $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select2_from_array',
            'options' => ['Live' => 'Live', 'Not Live' => 'Not Live'],
            'hint' => '',
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

    public function getStations($city_id)
    {
        $personInfo = \DB::table("stations")
                    ->where("city_id",$city_id)
                    ->get();
                    
        $personInfoData = array();
        foreach($personInfo as $row)
        {
            $personInfoData[$row->id] = $row->station_name;
            
        }
        return response()->json($personInfoData);
    }
}
