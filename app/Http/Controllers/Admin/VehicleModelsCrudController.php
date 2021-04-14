<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleModelsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VehicleModelsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VehicleModelsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\VehicleModels::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehiclemodels');
        CRUD::setEntityNameStrings('Model', 'Vehicle Models');

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

        CRUD::column('model');
        CRUD::column('allowed_km_per_hour');
        CRUD::column('charges_per_hour');
        $this->crud->addColumn([
                'name' => 'vehicle_image',
                'label' => 'Image',
                'type' => 'image',
            ]);

        $this->crud->addButtonFromView('line', 'gallery', 'gallery', 'end');

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
        CRUD::setValidation(VehicleModelsRequest::class);

       //CRUD::setFromDb(); // fields
        CRUD::field('model');
        
        CRUD::field('allowed_km_per_hour');
        CRUD::field('charges_per_hour');
        CRUD::field('insurance_charges_per_hour');
        CRUD::field('penalty_amount_per_hour');

        $this->crud->addField([
            'name' => 'vehicle_image',
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
