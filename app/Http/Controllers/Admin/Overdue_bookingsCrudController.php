<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Overdue_bookingsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class Overdue_bookingsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class Overdue_bookingsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Overdue_bookings::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/overdue_bookings');
        CRUD::setEntityNameStrings('overdue bookings', 'overdue bookings');
        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->denyAccess(['create', 'update', 'delete']);
            //$this->crud->allowAccess(['list','create', 'update', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }

        $current_date = date('Y-m-d');
        $this->crud->addClause('where', 'pick_up', '<=', $current_date);
        $this->crud->addClause('whereNull', 'return_time');
        $this->crud->addClause('where', 'booking_status', '=', '1');
        $this->crud->addClause('where', 'status', '=', 'In');
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
        CRUD::setValidation(Overdue_bookingsRequest::class);

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
