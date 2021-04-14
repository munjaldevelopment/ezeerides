<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmployeeAttendanceRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\User;
/**
 * Class EmployeeAttendanceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EmployeeAttendanceCrudController extends CrudController
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
        CRUD::setModel(\App\Models\EmployeeAttendance::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/employeeattendance');
        CRUD::setEntityNameStrings('Employee Attendance', 'Employee Attendances');
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

        $this->crud->addColumn([
            'label'     => 'Employee Name',
            'type'      => 'select',
            'name'      => 'employee_id',
            'entity'    => 'allEmployes', //function name
            'attribute' => 'name', //name of fields in models table like districts
            'model'     => "App\User", //name of Models

         ]);

        $this->crud->addColumn([
            'name'      => 'UserEmail',
            'label'     => 'Email',
            'type'      => 'email',
            
         ]);

        $this->crud->addColumn([
            'label'     => 'Attendance Date',
            'type'      => 'date',
            'name'      => 'attendance_date',
            'format' =>     'D-M-Y',
         ]);

        $this->crud->addColumn([
            'label'     => 'Check IN',
            'type'      => 'text',
            'name'      => 'check_in',
         ]);

        $this->crud->addColumn([
            'label'     => 'Check Out',
            'type'      => 'text',
            'name'      => 'check_out',
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
        CRUD::setValidation(EmployeeAttendanceRequest::class);

        //CRUD::setFromDb(); // fields

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
        ]);

        $this->crud->addField([
            'label'     => 'Attendance Date',
            'type'      => 'date',
            'name'      => 'attendance_date'
            
        ]);

        $this->crud->addField([
            'label'     => 'Check IN',
            'type'      => 'time',
            'name'      => 'check_in'
            
        ]);

        $this->crud->addField([
            'label'     => 'Check OUT',
            'type'      => 'time',
            'name'      => 'check_out'
            
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
