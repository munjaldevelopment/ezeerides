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
        CRUD::column('id');
        CRUD::column('user_id');
        CRUD::column('customer_name');
        CRUD::column('phone');
        CRUD::column('pick_up');
        CRUD::column('expected_drop');
        CRUD::column('station');
        CRUD::column('vehicle');
        CRUD::column('total_amount');
        CRUD::column('punchout_time');
        CRUD::column('return_time');
        CRUD::column('additional_hours');
        CRUD::column('additional_amount');
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('updated_at');

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

        CRUD::field('id');
        CRUD::field('user_id');
        CRUD::field('customer_name');
        CRUD::field('phone');
        CRUD::field('pick_up');
        CRUD::field('expected_drop');
        CRUD::field('station');
        CRUD::field('vehicle');
        CRUD::field('total_amount');
        CRUD::field('punchout_time');
        CRUD::field('return_time');
        CRUD::field('additional_hours');
        CRUD::field('additional_amount');
        CRUD::field('status');
        CRUD::field('created_at');
        CRUD::field('updated_at');

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
