<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CustomerDocumentsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CustomerDocumentsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomerDocumentsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\CustomerDocuments::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/customerdocuments');
        CRUD::setEntityNameStrings('Customer Document', 'Customer Documents');

        $customerData = array();
        $customers = \DB::table('customers')->get();
        foreach ($customers as $key => $row) {
            $customerData[$row->id] = $row->name;
            # code...
        }
        $this->crud->addFilter([
              'type' => 'select2',
              'name' => 'customer_id',
              'label'=> 'Customer'
            ],
            $customerData,
            function($value) {
                $this->crud->addClause('where', 'customer_id', $value);
        });

        $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomers', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customers", //name of Models

         ]);
        CRUD::column('title');
        CRUD::column('status');


        $customer_list = array();
            
        $customer_list[0] = 'Select';
        $customers = \DB::table('customers')->orderBy('id')->get();
        if($customers)
        {
            foreach($customers as $row)
            {
                $customer_list[$row->id] = $row->name ;
            }
        }

        $this->crud->addField([
            'name' => 'customer_id',
            'label' => 'Customer',
            'type'      => 'select2_from_array',
            'options'   => $customer_list,
            'hint' => '',
        ]);
         
         CRUD::field('title');
         
         $this->crud->addField([
            'name' => 'front_image',
            'label' => 'Front Image',
            'type' => 'browse',
        ]);

        $this->crud->addField([
            'name' => 'back_image',
            'label' => 'Back Image',
            'type' => 'browse',
        ]);
        
        $this->crud->addField([
            'name' => 'other_image',
            'label' => 'Other Image',
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
        CRUD::setValidation(CustomerDocumentsRequest::class);

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
