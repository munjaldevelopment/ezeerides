<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleServiceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class VehicleServiceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class VehicleServiceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\VehicleService::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehicleservice');
        CRUD::setEntityNameStrings('Vehicle Service', 'Vehicle Services');
        
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
       $this->crud->addColumn([
            'label'     => 'Vehicle Number',
            'type'      => 'select',
            'name'      => 'vehicle_id',
            'entity'    => 'allVehicle', //function name
            'attribute' => 'vehicle_number', //name of fields in models table like districts
            'model'     => "App\Models\Vehicle", //name of Models

         ]);

       $this->crud->addColumn([
            'label'     => 'Service By',
            'type'      => 'select',
            'name'      => 'service_by',
            'entity'    => 'roles', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "config('permission.models.role')", //name of Models

         ]);
       $this->crud->addColumn('service_amount');
       $this->crud->addColumn('service_date');
       //$this->crud->addColumn('service_date');
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
        CRUD::setValidation(VehicleServiceRequest::class);

        //CRUD::setFromDb(); // fields

        $vehicle_list = array();
            
        $vehicle_list[0] = 'Select';
        $vehicle = \DB::table('vehicles')->orderBy('id')->get();
        if($vehicle)
        {
            foreach($vehicle as $row)
            {
                $vehicle_list[$row->id] = $row->vehicle_number ;
            }
        }

        $this->crud->addField([
            'name' => 'vehicle_id',
            'label' => 'Vehicle',
            'type'      => 'select2_from_array',
            'options'   => $vehicle_list,
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'ckeditor',
            'hint' => '',
        ]);

        $user_roles_list = array();
            
        $roles = \DB::table('roles')->orderBy('id')->get();
        if($roles)
        {
            foreach($roles as $row)
            {
                $user_roles_list[$row->id] = $row->name;
            }
        }
         $this->crud->addField([
            'name' => 'service_by',
            'label' => 'Service By',
            'type'      => 'select2_from_array',
            'options'   => $user_roles_list,
            'hint' => '',
        ]);

        $this->crud->addField([
            'name' => 'service_amount',
            'label' => 'Service Amount',
            'type' => 'number',
            'hint' => '',
        ]);

        $this->crud->addField([
            'type' => 'checklist',
            'label' => 'Service Type',
            'name' => 'all_service_type', // the relationship name in your Model
            'entity' => 'all_service_type', // the relationship name in your Model
            'attribute' => 'title', // attribute on Article that is shown to admin
            'pivot' => true,
        ]);

        $this->crud->addField([
            'name' => 'service_date',
            'label' => 'Service Date',
            'type' => 'datetime',
            'hint' => '',
        ]);


        $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select2_from_array',
            'options' => ['done' => 'Done', 'pending' => 'Pending'],
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
