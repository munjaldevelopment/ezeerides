<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CouponRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CouponCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CouponCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Coupon::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/coupon');
        CRUD::setEntityNameStrings('coupon', 'coupons');

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

       $this->crud->addColumn('title');
       $this->crud->addColumn('discount_type');
       $this->crud->addColumn('start_date');
       $this->crud->addColumn('end_date');

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
        CRUD::setValidation(CouponRequest::class);

       // CRUD::setFromDb(); // fields

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'discount_type',
            'label' => 'Discount Type',
            'type' => 'select2_from_array',
            'options' => ['percentage' => 'Percentage', 'amount' => 'Amount'],
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'discount',
            'label' => 'Discount',
            'type' => 'text',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'start_date',
            'label' => 'Start Date',
            'type' => 'datetime',
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'end_date',
            'label' => 'End Date',
            'type' => 'datetime',
            'hint' => '',
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
