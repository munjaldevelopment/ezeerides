<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SupportTicketsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SupportTicketsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SupportTicketsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\SupportTickets::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/supporttickets');
        CRUD::setEntityNameStrings('Support Ticket', 'Support Tickets');
        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->allowAccess(['list','create', 'update', 'delete']);
             $this->crud->denyAccess(['create']);
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

        CRUD::column('ticket_no');
        
        $this->crud->addColumn([
            'label'     => 'Customer Name',
            'type'      => 'select',
            'name'      => 'customer_id',
            'entity'    => 'allCustomer', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\Models\Customers", //name of Models

         ]);
        CRUD::column('title');
        CRUD::column('created_at');
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
        CRUD::setValidation(SupportTicketsRequest::class);

        //CRUD::setFromDb(); // fields
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
            'attributes' => [
                'disabled' => 'disabled'
            ],
        ]);

        $this->crud->addField([
            'name' => 'ticket_no',
            'label' => 'Ticket No',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

        $this->crud->addField([
            'name' => 'answer',
            'label' => 'Answer',
            'type' => 'textarea',
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
