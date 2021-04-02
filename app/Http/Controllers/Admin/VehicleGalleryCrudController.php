<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleGalleryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VehicleGalleryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VehicleGalleryCrudController extends CrudController
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
        CRUD::setModel(\App\Models\VehicleGallery::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehiclegallery');
        CRUD::setEntityNameStrings('Vehicle Gallery', 'Vehicle Galleries');

        $vehicleData = array();
        $vehicle = \DB::table('vehicle_models')->get();
        foreach ($vehicle as $key => $row) {
            $vehicleData[$row->id] = $row->model;
            # code...
        }

        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'vehicle_model_id',
              'label'=> 'Vehicle Model'
            ],
            $vehicleData,
            function($value) {
                $this->crud->addClause('where', 'vehicle_model_id', $value);
        });

        $this->crud->addColumn([
            'label'     => 'Vehicle Model',
            'type'      => 'select',
            'name'      => 'vehicle_model_id',
            'entity'    => 'allVehicleModel', //function name
            'attribute' => 'model', //name of fields in models table like districts
            'model'     => "App\Models\VehicleModels", //name of Models

         ]);
        CRUD::column('title');
        $this->crud->addColumn([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'image',
            ]);
         CRUD::column('status');



         $vehicle_list = array();
            
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
            'name' => 'vehicle_model_id',
            'label' => 'Vehicle Model',
            'type'      => 'select2_from_array',
            'options'   => $vehicle_list,
            'hint' => '',
        ]);
         
         CRUD::field('title');
         
         $this->crud->addField([
            'name' => 'image',
            'label' => 'Image',
            'type' => 'browse',
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
       // CRUD::setFromDb(); // columns



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
        CRUD::setValidation(VehicleGalleryRequest::class);

        //CRUD::setFromDb(); // fields

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
