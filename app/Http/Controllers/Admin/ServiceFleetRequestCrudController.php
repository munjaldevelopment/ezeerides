<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ServiceFleetRequestRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\User;
/**
 * Class ServiceFleetRequestCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ServiceFleetRequestCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ServiceFleetRequest::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/servicefleetrequest');
        CRUD::setEntityNameStrings('Service Fleet Request', 'Service Fleet Requests');

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
        $this->crud->addColumn([
            'label'     => 'Employee Name',
            'type'      => 'select',
            'name'      => 'employee_id',
            'entity'    => 'allEmployes', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models

         ]);

        $this->crud->addColumn([
            'label'     => 'Vehicle Number',
            'type'      => 'select',
            'name'      => 'vehicle_id',
            'entity'    => 'allVehicle', //function name
            'attribute' => 'vehicle_number', //name of fields in models table like districts
            'model'     => "App\Models\Vehicle", //name of Models

         ]);

        $this->crud->addColumns([
            [
                'name'  => 'oil_check',
                'label' => 'Check Oil',
                'type'  => 'text',
            ],
        ]);

        $this->crud->addColumns([
            [
                'name'  => 'engine_check',
                'label' => 'Check Engine',
                'type'  => 'text',
            ],
        ]);

        $this->crud->addColumns([
            [
                'name'  => 'filter_check',
                'label' => 'Check Filter',
                'type'  => 'text',
            ],
        ]);

        $this->crud->addColumns([
            [
                'name'  => 'tyre_check',
                'label' => 'Check Tyre',
                'type'  => 'text',
            ],
        ]);

        $this->crud->addColumns([
            [
                'name'  => 'status',
                'label' => 'Status',
                'type'  => 'text',
            ],
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
        CRUD::setValidation(ServiceFleetRequestRequest::class);

       // CRUD::setFromDb(); // fields

         $employee_list = array();
            
        $employee_list[0] = 'Select';
        $emplist= User::whereHas('roles', function($q){
                    $q->where('name', 'Employee');
                  })->get();
        if($emplist)
        {
            foreach($emplist as $row)
            {
                $employee_list[$row->id] = $row->name;
            }
        }

        $this->crud->addField([
            'name' => 'employee_id',
            'label' => 'Employee',
            'type'      => 'select2_from_array',
            'options'   => $employee_list,
            'hint' => '',
            'attributes' => [
                'disabled' => 'disabled'
            ],
        ]);

         $vehicle_list = array();
       
            
        $vehicle_list[0] = 'Select';
        $vehicles = \DB::table('vehicles as v')->join('vehicle_models as vm', 'v.vehicle_model', '=', 'vm.id')->where('v.status','Live')->orderBy('v.id')->select('v.id','v.vehicle_number','vm.model')->get();
        if($vehicles)
        {
            foreach($vehicles as $row)
            {
                $vehicle_list[$row->id] = $row->model." - ".$row->vehicle_number;
            }
        }
        $this->crud->addField([
            'name' => 'vehicle_id',
            'label' => 'Vehicle Number',
            'type'      => 'select2_from_array',
            'options'   => $vehicle_list,
            'hint' => '',
            'attributes' => [
                'disabled' => 'disabled'
            ],
        ]);

         $this->crud->addField([
            'name' => 'oil_check',
            'label' => 'Check Oil',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

         $this->crud->addField([
            'name' => 'engine_check',
            'label' => 'Check Engine',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

         $this->crud->addField([
            'name' => 'filter_check',
            'label' => 'Check Filter',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly'
            ],
        ]);

         $this->crud->addField([
            'name' => 'tyre_check',
            'label' => 'Check Tyre',
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
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select2_from_array',
            'options' => ['Pending' => 'Pending', 'Approve' => 'Approve'],
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
