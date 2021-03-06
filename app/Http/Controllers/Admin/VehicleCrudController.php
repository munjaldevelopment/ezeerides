<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VehicleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VehicleCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Vehicle::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehicle');
        CRUD::setEntityNameStrings('vehicle', 'vehicles');

        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->allowAccess(['list','create', 'update', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }
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

        $vehicleData = array();
        $vehicle = \DB::table('vehicle_models')->get();
        foreach ($vehicle as $key => $row) {
            $vehicleData[$row->id] = $row->model;
            # code...
        }

        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'vehicle_model',
              'label'=> 'Vehicle Model'
            ],
            $vehicleData,
            function($value) {
                $this->crud->addClause('where', 'vehicle_model', $value);
        });

        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'status',
              'label'=> 'Vehicle Status'
            ],
            ['Live' => 'Live', 'Not Live' => 'Not Live'],
            function($value) {
                $this->crud->addClause('where', 'status', $value);
        });

         $this->crud->addColumn([
            'label'     => 'Vehicle Model',
            'type'      => 'select',
            'name'      => 'vehicle_model',
            'entity'    => 'allVehicleModel', //function name
            'attribute' => 'model', //name of fields in models table like districts
            'model'     => "App\Models\VehicleModels", //name of Models

         ]);

        //CRUD::column('vehicle_model');
        CRUD::column('vehicle_number');
        CRUD::column('status');
        //CRUD::column('allowed_km_per_hour');
        //CRUD::column('charges_per_hour');
        // $this->crud->addColumn([
        //         'name' => 'vehicle_image',
        //         'label' => 'Image',
        //         'type' => 'image',
        //     ]);

        
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
        CRUD::setValidation(VehicleRequest::class);

        //CRUD::setFromDb(); // fields
        
        $vehicle_list[0] = 'Select';
        $vehicle = \DB::table('vehicle_models')->orderBy('id')->get();
        if($vehicle)
        {
            foreach($vehicle as $row)
            {
                $vehicle_list[$row->id] = $row->model ;
            }
        }

        $this->crud->addField([
            'name' => 'vehicle_model',
            'label' => 'Vehicle Model',
            'type'      => 'select2_from_array',
            'options'   => $vehicle_list,
            'hint' => '',
        ]);

        CRUD::field('vehicle_number');
        //CRUD::field('allowed_km_per_hour');
        //CRUD::field('charges_per_hour');
        //CRUD::field('insurance_charges_per_hour');
        //CRUD::field('penalty_amount_per_hour');

        /*$this->crud->addField([
            'name' => 'vehicle_image',
            'label' => 'Image',
            'type' => 'browse',
        ]);*/

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


}
