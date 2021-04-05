<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\VehicleRegisterRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\User;
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
        CRUD::setEntityNameStrings('Vehicle', 'Vehicle Booking');

        $is_admin = backpack_user()->hasRole('Admin');
        if($is_admin)
        {
            $this->crud->allowAccess(['list','create', 'update', 'delete']);
        }else{
            $this->crud->denyAccess(['list', 'create', 'update', 'delete']);
        }

        $this->crud->setCreateView('admin.create-vehicle-booking-form');
        $this->crud->setUpdateView('admin.edit-vehicle-booking-form');

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
        CRUD::setValidation(VehicleRegisterRequest::class);

        $stationData = array('' => '--Select--');
        $stations = \DB::table('stations')->get();
        foreach($stations as $station)
        {
            $stationData[$station->station_name] = $station->station_name;
        }

        CRUD::addField([
            'label'     => 'Station',
            'type'      => 'select2_from_array',
            'name'      => 'station',
            'options'   => $stationData,
            'attributes'   => [
                'id' => 'station',
                'onchange' => 'getEmployee(this.value);'
            ],
        ]);

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
            'name' => 'user_id',
            'label' => 'Employee',
            'type'      => 'select2_from_array',
            'options'   => $employee_list,
            'attributes'   => [
                'id' => 'user_id'
                
            ],
            'hint' => '',
        ]);

        $vehicleData = array('' => '--Select--');
        $vehicles = \DB::table('vehicles')->get();
        foreach($vehicles as $vehicle)
        {
            $vehicleData[$vehicle->vehicle_number] = $vehicle->vehicle_number;
        }

        CRUD::addField([
            'label'     => 'Vehicle',
            'type'      => 'select2_from_array',
            'name'      => 'vehicle',
            'options'   => $vehicleData,
            'attributes'   => [
                'id' => 'vehicle'
            ],
        ]);

        $customer_list = array();
            
        $customer_list[0] = 'Select';
        $custlist= User::whereHas('roles', function($q){
                    $q->where('name', 'Customer');
                  })->get();
        if($custlist)
        {
            foreach($custlist as $row)
            {
                $customer_list[$row->name] = $row->name;
            }
        }
        $this->crud->addField([
            'name' => 'customer_name',
            'label' => 'Customer',
            'type'      => 'select2_from_array',
            'options'   => $customer_list,
            'attributes'   => [
                'id' => 'customer_name'
                
            ],
            'hint' => '',
        ]);
        CRUD::field('customer_name');
        CRUD::field('phone');
        CRUD::field('pick_up');
        CRUD::field('pick_up_time');
        CRUD::field('expected_drop');
        CRUD::field('expected_drop_time');

        
        //CRUD::field('total_amount');
        //CRUD::field('punchout_time');
        //CRUD::field('return_time');
        //CRUD::field('additional_hours');
        //CRUD::field('additional_amount');

        $statusData = array('0' => 'Inactive', '1' => 'Active');
        CRUD::addField([
            'label'     => 'Booking Status',
            'type'      => 'select2_from_array',
            'name'      => 'booking_status',
            'options'   => $statusData
        ]);

        /*$statusData = array('0' => 'Inactive', '1' => 'Active');
        CRUD::addField([
            'label'     => 'Amount Recceive',
            'type'      => 'select2_from_array',
            'name'      => 'is_amount_receive',
            'options'   => $statusData
        ]);*/

        /*CRUD::addField([
            'label'     => 'Receive Date',
            'type'      => 'datetime',
            'name'      => 'receive_date'
        ]);*/

        $statusData = array('' => '--Select--', 'In' => 'In', 'Out' => 'Out');
        CRUD::addField([
            'label'     => 'Status',
            'type'      => 'select2_from_array',
            'name'      => 'status',
            'options'   => $statusData
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

    public function getEmployee($station_id)
    {
       
       $emplist= User::whereHas('roles', function($q){
                    $q->where('name', 'Employee');
                  })->join('stations as s', 's.employee_id', '=', 'users.id')->where('s.station_name',$station_id)->get();
                    
        $empData = array();
        foreach($emplist as $row)
        {
            $empData[$row->id] = $row->name;
            
        }
        return response()->json($empData);
    }

    public function getVehicle($station_id)
    {
       
       $vehicleInfo = \DB::table("vehicles")->join('station_has_vehicles as sv', 'sv.vehicle_id', '=', 'vehicles.id')->join('stations as s', 's.id', '=', 'sv.station_id')->where("s.station_name",$station_id)->get();
                    
        $vhlData = array();
        foreach($vehicleInfo as $row)
        {
            $vhlData[$row->vehicle_number] = $row->vehicle_number;
            
        }
        return response()->json($vhlData);
    }
}
