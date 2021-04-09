<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PendingServicesRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PendingServicesCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PendingServicesCrudController extends CrudController
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
        CRUD::setModel(\App\Models\PendingServices::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/pendingservices');
        CRUD::setEntityNameStrings('Pending Services', 'Pending Services');
        $this->crud->denyAccess(['create', 'update', 'delete']);
         $current_date = date('Y-m-d H:i:s');
        $this->crud->addClause('where', 'next_date', '<=', $current_date);

         $this->crud->addColumn([
            'name'      => 'VehicleModel',
            'label'     => 'Vehicle Model',
            'type'      => 'text',
            
         ]);
         
         $this->crud->addColumn([
            'label'     => 'Vehicle Number',
            'type'      => 'select',
            'name'      => 'vehicle_id',
            'entity'    => 'allVehicle', //function name
            'attribute' => 'vehicle_number', //name of fields in models table like districts
            'model'     => "App\Models\Vehicle", //name of Models

         ]);
         $this->crud->addColumn([
            'label'     => 'Last Services Date',
            'type'      => 'text',
            'name'      => 'service_date'
            
         ]);

          $this->crud->addColumn([
            'label'     => 'Pending Services Date',
            'type'      => 'text',
            'name'      => 'next_date'
            
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
        CRUD::setValidation(PendingServicesRequest::class);

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
