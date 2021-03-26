<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Auth;
use App\Models\Customers;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CustomersRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CustomersCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomersCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitCustomerStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitCustomerUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Customers::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/customers');
        CRUD::setEntityNameStrings('Customer', 'Customers');

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
       // CRUD::setFromDb(); // columns

        CRUD::column('name');
        CRUD::column('mobile');
        CRUD::column('email');
        CRUD::column('status');

        $this->crud->addColumn([
                'name' => 'image',
                'label' => 'Image',
                'type' => 'image',
            ]);

         $this->crud->addButtonFromView('line', 'customer_documents', 'customer_documents', 'end');
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
        CRUD::setValidation(CustomersRequest::class);

        //CRUD::setFromDb(); // fields
        $this->crud->addField([
            'name' => 'user_id',
            'label' => 'user ID',
            'type' => 'hidden',
            'hint' => '',
        ]);
        CRUD::field('name');
        CRUD::field('mobile');
        CRUD::field('email');
        $this->crud->addField([
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'hint' => '',
        ]);
        $this->crud->addField([
            'name' => 'dob',
            'label' => 'Birth of Date',
            'type' => 'date',
            'hint' => '',
        ]);
         $this->crud->addField([
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'hint' => '',
        ]);

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

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run
        
        $result = $this->traitCustomerStore();
        
        // Save Data in user table
        $id = $this->crud->entry->id;

        
        $user_id = User::insertGetId([
            'name' => $this->crud->getRequest()->name,
            'email' => $this->crud->getRequest()->email,
            'phone' => $this->crud->getRequest()->mobile,
            'password' => Hash::make($this->crud->getRequest()->password),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        Customers::where('id', $id)->update(['user_id' => $user_id]);
        
        // create role entry-
        \DB::table('model_has_roles')->insert(['role_id' => '3', 'model_type' => 'App\User', 'model_id' => $user_id]);
        
        
        return $result;
        
        
    }

    public function update()
    {
        
        $this->crud->setRequest($this->crud->validateRequest());
        //$this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        $user_id = $this->crud->getRequest()->user_id;
        
        if($this->crud->getRequest()->password == NULL)
        {
            User::where('id', $user_id)->update(['name' => $this->crud->getRequest()->name, 'email' => $this->crud->getRequest()->email, 'phone' => $this->crud->getRequest()->mobile, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        else
        {
            User::where('id', $user_id)->update(['name' => $this->crud->getRequest()->name, 'email' => $this->crud->getRequest()->email, 'phone' => $this->crud->getRequest()->mobile, 'password' => Hash::make($this->crud->getRequest()->password), 'updated_at' => date('Y-m-d H:i:s')]);
        }
        
        
        $result = $this->traitCustomerUpdate();
        
        return $result;
    }
    
    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $this->crud->getRequest()->request->remove('password_confirmation');

        // Encrypt password if specified.
        if ($this->crud->getRequest()->input('password')) {
            $this->crud->getRequest()->request->set('password', Hash::make($this->crud->getRequest()->input('password')));
        } else {
            $this->crud->getRequest()->request->remove('password');
        }

        return $this->crud->getRequest();
    }
}
