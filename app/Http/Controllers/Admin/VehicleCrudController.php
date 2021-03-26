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

        CRUD::column('vehicle_model');
        CRUD::column('vehicle_number');
        CRUD::column('allowed_km_per_hour');
       CRUD::column('charges_per_hour');
        $this->crud->addColumn([
                'name' => 'vehicle_image',
                'label' => 'Image',
                'type' => 'image',
            ]);

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
        CRUD::field('vehicle_model');
        CRUD::field('vehicle_number');
        CRUD::field('allowed_km_per_hour');
        CRUD::field('charges_per_hour');
        CRUD::field('premium_charges_per_hour');
        CRUD::field('penalty_amount_per_hour');

        $this->crud->addField([
            'name' => 'vehicle_image',
            'label' => 'Image',
            'type' => 'browse',
        ]);

        $this->crud->addField([
            'name' => 'alt_image1',
            'label' => 'Alt Image 1',
            'type' => 'browse',
        ]);

        $this->crud->addField([
            'name' => 'alt_image2',
            'label' => 'Alt Image 2',
            'type' => 'browse',
        ]);

        $this->crud->addField([
            'name' => 'alt_image3',
            'label' => 'Alt Image 3',
            'type' => 'browse',
        ]);

        $this->crud->addField([
            'name' => 'alt_image4',
            'label' => 'Alt Image 4',
            'type' => 'browse',
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
