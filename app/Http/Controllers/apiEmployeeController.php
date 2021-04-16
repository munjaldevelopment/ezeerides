<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use DB;
use App;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Hash;
use URL;
use File;
use Session;
use QR_Code\QR_Code;
use App\Models\VehicleRegister;
use App\Models\CustomerDocuments;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

use PaytmWallet;

class apiEmployeeController extends Controller
{
    //START LOGIN
	public function employeeLogin(Request $request)
    {
        try 
        {
            $mobile = $request->mobile;
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            
            if($error == ""){
                $json = $userData = array();
                $date   = date('Y-m-d H:i:s');
                $employee = DB::table('users as u')->join('model_has_roles as rol', 'u.id', '=', 'rol.model_id')->where('u.phone', $mobile)->where('rol.role_id', 2)->first();
                if($employee) 
                {
                    
                    $empid = $employee->id;
                    $emp_status = $employee->status;
                    
                    if($emp_status == 'Live'){

                        $otp = rand(11111, 99999);
                        
                       $smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");

                        //$this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                    
                        DB::table('users')->where('id', '=', $empid)->update(['otp' => "".$otp, 'updated_at' => $date]);

                        $status_code = '1';
                        $message = 'Employee login OTP Send';
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' =>"".$empid, 'otp' => "".$otp);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Employee Not Active, Please contact to support';
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => "".$empid, 'email' => $email, 'otp' => "".$otp);
                    }
                        
                   
                }else{
                	$status_code = $success = '0';
                    $message = 'Employee not found, Please contact to support';
                   $json = array('status_code' => $status_code, 'message' => $message, 'mobile' => $mobile);
	           }   
            }   
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    // End Login
    

    public function httpGet($url)
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $head = curl_exec($ch); 
        curl_close($ch);
        //print_r($head);
        return $head;
    }

    public function sendNotification($customer_id, $title, $message, $image = '')
    {
        $date = date('Y-m-d H:i:s');
        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'employee_notification', 'user_type' => 'employee', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
        //echo $success.",".$fail.",".$total; exit;
    }

    public function getcashCollected($employee_id)
    {
        $empCashCollection = 0;
        $cashCollected = DB::table('employee_cash_collections')->where('employee_id', $employee_id)->sum('amount');
        
        $transferAmount = DB::table('employee_cash_transfer')->where('employee_id', $employee_id)->where('is_paid', '=', 'yes')->sum('amount');


        $empCashCollection = ($cashCollected-$transferAmount);
        return $empCashCollection;
    }

    //START VERIFY
    public function employeeVerify(Request $request)
    {
        try 
        {
            $baseUrl = URL::to("/");
            $json = $userData = array();
            $mobile = $request->mobile;
            $device_id = $request->device_id;
            $fcmToken = $request->fcmToken;
            $otp = $request->otp;
            $date   = date('Y-m-d H:i:s');
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($device_id == ""){
                $error = "Device id not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($otp == ""){
                $error = "otp not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $employee = DB::table('users')->where('phone', $mobile)->where('otp', $otp)->first();
                if($employee) 
                {
                    $device_id = $employee->device_id;
                    $fcmToken = $employee->fcmToken;
                    $employeeid = $employee->id;
                    $name = $employee->name;
                    $email = $employee->email;
                    
                    DB::table('users')->where('id', '=', $employeeid)->update(['device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                    $empCashCollection = $this->getcashCollected($employeeid);
                    $status_code = '1';
                    $message = 'Employee verified successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => "".$employeeid, 'mobile' => $mobile, 'name' => $name, 'email' => $email,'empCashCollection' => "".$empCashCollection);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Customer does not exists or Incorrect OTP!';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '', 'mobile' => $mobile);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }
    
    //START VERIFY
    public function resendSMS(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $mobile = $request->mobile;
            $date   = date('Y-m-d H:i:s');
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
           
            if($error == ""){
                $employee = DB::table('users')->where('phone', $mobile)->first();
                if($employee) 
                {
                    $employee_id = $employee->id;
                    $otp = rand(11111, 99999);
                    
                    $smsmessage = "Here is the new OTP ".$otp." for your login id. Please do not share with anyone. ";

                    //$this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");


                     DB::table('users')->where('id', '=', $employee_id)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send sucessfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => "".$employee_id,  'mobile' => $mobile, 'otp' => "".$otp);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Employee does not exists';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '', 'mobile' => $mobile);
               }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    
    
    //Employee Update
    public function employee_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
           
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
            if($employee){ 
                if($employee->name){
                    $name = $employee->name;
                }else{
                    $name = "";
                }
                if($employee->email){
                    $email = $employee->email; 
                }else{
                    $email = "";
                }
                $mobile = $employee->phone;
               
                $status_code = $success = '1';
                $message = 'Employee Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id , 'name' => $name, 'email' => $email, 'mobile' => $mobile);


            } else{
                $status_code = $success = '0';
                $message = 'Employee not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    

    //Customer Update
    public function empoyee_logout(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
           
            $employee = DB::table('users')->where('id', $employee_id)->where('status', '=', 'Live')->first();
            if($employee){ 

                $device_id = '';
                DB::table('users')->where('id', '=', $employee_id)->update(['device_id' => $device_id, 'updated_at' => $date]);
                
                $status_code = $success = '1';
                $message = 'Employee logout successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message);


            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Pending Cash  
    public function pending_cash_transactions(Request $request)
    {
        $employee_id = $request->employee_id;
        $device_id = $request->device_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
            if($employee){
                $pendingTransactionList = DB::table('employee_cash_transfer')->select('employee_id','amount','is_paid','remark','created_at')->where('is_paid', '=', 'no')->orderBy('id', 'ASC')->get();

                $status_code = '1';
                $message = 'Pending Transaction list';
                $json = array('status_code' => $status_code,  'message' => $message, 'pendingTransactionList' => $pendingTransactionList);
            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
            }    
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //All Cash  
    public function all_cash_transactions(Request $request)
    {
        $employee_id = $request->employee_id;
        $device_id = $request->device_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json    =   array();
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
            if($employee){
                $transactionList = DB::table('employee_cash_transfer')->where('employee_id', $employee_id)->where('is_paid', '=', 'yes')->orderBy('id', 'ASC')->get();

                $empCashCollection = $this->getcashCollected($employee_id);

                $status_code = '1';
                $message = 'ALL Transaction list';
                $json = array('status_code' => $status_code,  'message' => $message, 'empCashCollection' => "".$empCashCollection,'transactionList' => $transactionList);
            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
            }    
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 


    public function tranfer_cash(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $amount = $request->amount;
            $remark = $request->reason;
            $is_paid = 'no';
            $error = "";
            if($amount == ""){
                $error = "Please enter amount";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

            if($remark == ""){
                $error = "Please add reason";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        
                        $booking_id = DB::table('employee_cash_transfer')->insert([
                            'employee_id' => $employee_id,
                            'amount' => $amount,
                            'remark' => $remark,
                            'is_paid' => $is_paid,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        
                        $status_code = $success = '1';
                        $message = 'Cash Transfered Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                    
                    
                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

     public function expences_type(Request $request)
    {
        try 
        {   
            $json       =   array();
            $expences_type[] = array('key' => 'service', "value" => 'Bike Service');
            $expences_type[] = array('key' => 'damege', "value" => 'Bike Damege');
            $expences_type[] = array('key' => 'other', "value" => 'Others');
            $status_code = '1';
            $message = 'All Expenses Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'expences_type' => $expences_type);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function add_cash_expense(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $expences_type = $request->expences_type;
            $amount = $request->amount;
            $remark = $request->reason;
            $is_paid = 'no';
            $error = "";
            if($amount == ""){
                $error = "Please enter amount";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

            if($remark == ""){
                $error = "Please add reason";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        $station_id = DB::table('stations')->where('employee_id', $employee_id)->pluck('id')[0];
                        $booking_id = DB::table('employee_expences')->insert([
                            'employee_id' => $employee_id,
                            'station_id' => $station_id,
                            'expences_type' => $expences_type,
                            'amount' => $amount,
                            'remark' => $remark,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        
                        $status_code = $success = '1';
                        $message = 'Cash / Expenses Added Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                    
                    
                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    

    public function fleet_calendar(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    $employeeFleetExists = DB::table('stations as s')->join('station_has_vehicles as sv', 's.id', '=', 'sv.station_id')->join('vehicles as v', 'v.id', '=', 'sv.vehicle_id')->select('v.id','v.vehicle_model','v.vehicle_number')->where('v.status','Live')->where('s.employee_id', $employee_id)->orderBy('v.id', 'DESC')->get();
                    $fleet_List = array();
                    if($employeeFleetExists){
                        foreach($employeeFleetExists as $rsfleet)
                        {
                            $model_id = $rsfleet->vehicle_model;
                            $vehicle_number = $rsfleet->vehicle_number;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                            $vehicle_status = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->pluck('status')[0];
                           $vstatus = "In Service";
                            if($vehicle_status == 'In'){
                                $vstatus = 'At Station';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Out of Station';
                            }

                            $fleet_List[] = array('id' => "".$rsfleet->id, 'vehicle_model' => $vehicleModel,'vehicle_number' => $rsfleet->vehicle_number,'vehicle_status' => $vstatus); 
                           
                        } 

                        
                        $status_code = '1';
                        $message = 'Fleet Calendar';
                        $json = array('status_code' => $status_code,  'message' => $message, 'fleet_List' => $fleet_List);
                    }else{
                         $status_code = '0';
                        $message = 'No Fleet found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }


    //Bike Detail
    public function fleet_detail(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $vehicle_id = $request->fleet_id;
            $error = "";
            if($vehicle_id == ""){
                $error = "Please send valid vehicle id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    
                    $bikeDetail = DB::table('vehicle_models as vm')->join('vehicles as v', 'v.vehicle_model', '=', 'vm.id')->select('v.id','vm.model','v.vehicle_number', 'vm.allowed_km_per_hour', 'vm.charges_per_hour', 'vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image','v.vehicle_model')->where('v.id', $vehicle_id)->where('v.status', '=', 'Live')->first();
                    $bike_feature = array();
                    if($bikeDetail){ 
                        $bike_model_id = $bikeDetail->vehicle_model;
                        $vehicle_model = $bikeDetail->model;
                        $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour.' KM';
                        $excess_km_charges = '0';
                        $charges_per_hour = '₹ '.$bikeDetail->charges_per_hour.' / Hr';
                        $bikecharges = $bikeDetail->charges_per_hour;
                        $insurance_charges_per_hour =$bikeDetail->insurance_charges_per_hour;
                        $insurance_charges = $bikeDetail->insurance_charges_per_hour;
                        $penalty_amount_per_hour = '₹ '.$bikeDetail->penalty_amount_per_hour.' / Hr';
                        
                        
                        if($allowed_km_per_hour > 0){
                            $bike_feature[] =  ['title' => 'Allowed KM','subtitle' => $allowed_km_per_hour];
                            
                        }
                        if($excess_km_charges){
                             $bike_feature[] =  ['title' => 'Excess KM Charges', 'subtitle' => $excess_km_charges];
                        }

                        if($charges_per_hour){
                          
                            $bike_feature[] =  ['title' => 'Charges', 'subtitle' => $charges_per_hour];
                            
                        }

                        if($penalty_amount_per_hour){
                             
                             $bike_feature[] =  ['title' => 'Penalty', 'subtitle' => $penalty_amount_per_hour];

                        }

                        if($insurance_charges_per_hour > 0){
                            
                            $bike_feature[] =  ['title' => 'Insurance for your Ride', 'subtitle' => '₹ '.$insurance_charges_per_hour];

                            
                        }

                        
                        $baseUrl = URL::to("/");
                        $vehicle_image  = "";
                        if($bikeDetail->vehicle_image){
                            $vehicle_image  =  $baseUrl."/public/".$bikeDetail->vehicle_image;
                        
                        }
                        
                        $bikegallery = DB::table('vehicle_galleries')->where('vehicle_model_id', $bike_model_id)->where('status', '=', 'Live')->get();
                        $bgallery = array();
                        if(count($bikegallery) >0){
                            
                            foreach($bikegallery as $bike_gallery)
                            {   
                                if($bike_gallery->image){
                                    $title = $bike_gallery->title;
                                    $vehicle_gal_image  =  $baseUrl."/public/".$bike_gallery->image;

                                    $bgallery[] = ['title' => $title, 'gallery_img' => $vehicle_gal_image];
                                }      
                            }
                        }       
                            
                         

                        $status_code = $success = '1';
                        $message = 'Fleet Details';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id, 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model,'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'bike_feature' => $bike_feature );
                    }else{
                        $status_code = $success = '0';
                        $message = 'Fleet not valid';
                        
                        $json = array('status_code' => $status_code, 'message' => $message);
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getTraceAsString();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //START show cities 
    public function allCities(Request $request)
    {
        $employee_id = $request->employee_id;
        $device_id = $request->device_id;

        try 
        {   
            $json       =   array();
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
            if($employee){
                $cityList = DB::table('cities as c')->join('stations as s', 's.city_id', '=', 'c.id')->select('c.*')->where('s.employee_id',$employee_id)->where('c.status', '=', 'Live')->orderBy('c.id', 'ASC')->get();

                $status_code = '1';
                $message = 'All City list';
                $json = array('status_code' => $status_code,  'message' => $message, 'cityList' => $cityList);
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //START show feed list 
    public function all_emp_center(Request $request)
    {
        $city_id = $request->city_id;
        $employee_id = $request->employee_id;
        $device_id = $request->device_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
            if($employee){
                $centerList = DB::table('stations')->select('id','city_id','station_name')->where('city_id', $city_id)->where('employee_id',$employee_id)->orderBy('station_name', 'ASC')->get();
                
                $status_code = '1';
                $message = 'Center list';
                $json = array('status_code' => $status_code,  'message' => $message, 'centerList' => $centerList);
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //New Booking 
    public function fleet_booking(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $city_id = $request->city_id;
            $center = $request->center_id;
            $start_date = date("Y-m-d",strtotime($request->start_date));
            $end_date = date("Y-m-d",strtotime($request->end_date));
            $error = "";
            if($center == ""){
                $error = "Please enter center for ride";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 

                    $vehicleList = DB::table('vehicles as v')->join('vehicle_models as vm', 'v.vehicle_model', '=', 'vm.id')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->join('stations as s', 's.id', '=', 'sv.station_id')->select('vm.id','vm.model','vm.allowed_km_per_hour','vm.charges_per_hour','vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('s.employee_id',$employee_id)->where('v.status','Live')->groupBy('vm.id');

                    if($center){
                        $vehicleList = $vehicleList->where('sv.station_id',$center);    
                    }

                   /* if($ride_type){
                        $vehicleList = $vehicleList->where('v.ride_type',$ride_type);    
                    }*/

                    if($start_date){
                        //$rentinList = $rentinList->wheredate('available_date',' > ',$available_date);   
                        //$vehicleList = $vehicleList->where('available_date', '<=', $from_date.' 00:00:00'); 
                    }
                    $vehicleList = $vehicleList->orderBy('v.id', 'asc')->get(); 
                    if(count($vehicleList) >0){
                        $v_list = array();
                        foreach($vehicleList as $vlist)
                        {
                            
                            $vehicle_model = $vlist->model;
                            $allowed_km_per_hour = $vlist->allowed_km_per_hour;
                            $charges_per_hour = $vlist->charges_per_hour;
                            $insurance_charges_per_hour = $vlist->insurance_charges_per_hour;
                            $penalty_amount_per_hour = $vlist->penalty_amount_per_hour;
                            $baseUrl = URL::to("/");
                            $vehicle_image  = "";
                            if($vlist->vehicle_image){
                                $vehicle_image  =  $baseUrl."/public/".$vlist->vehicle_image;
                            
                            }
                            $premium_charges_per_hour = '0.00';
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'allowed_km_per_hour' =>$allowed_km_per_hour, 'charges_per_hour' =>$charges_per_hour, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'premium_charges_per_hour' => $premium_charges_per_hour, 'penalty_amount_per_hour' => $penalty_amount_per_hour, 'vehicle_image' => $vehicle_image]; 
                         }

                         if($center != 0){
                    
                            $station_name = DB::table('stations')->where('id', $center)->pluck('station_name')[0];
                        }else{
                            
                            $station_name = "";
                        } 
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Fleet Result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Fleet not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }
    //END 

    
    //Current Booking
    public function current_booking_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            /*$city_id = $request->city_id;
            $center = $request->center_id;
            $ride_type = $request->ride_type;
            $from_date = date("Y-m-d",strtotime($request->from_date));
            $to_date = date("Y-m-d",strtotime($request->to_date));*/
            $error = "";
           /* if($employee_id == ""){
                $error = "Please select date range";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }*/
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 


                    $stationinfo = DB::table('stations')->where('employee_id', $employee_id)->where('status', '=', 'Live')->first();
                    
                    $city_id = $stationinfo->city_id;
                    $center = $stationinfo->id;
                    $station_name = $stationinfo->station_name;
                    $today = date('Y-m-d');
                    $current_time = date('H:i:s');

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive')->where('user_id',$employee_id)->where('booking_status','1')->where('pick_up', $today)->where('pick_up_time', '<=', $current_time);

                    if($center){
                        $booked_vehicleList = $booked_vehicleList->where('station',$station_name);    
                    }

                   /* if($ride_typ528e){
                        $vehicleList = $vehicleList->where('v.ride_type',$ride_type);    
                    }*/

                   /* if($from_date){
                        $rentinList = $rentinList->wheredate('available_date',' > ',$available_date);   
                        $vehicleList = $vehicleList->where('available_date', '<=', $from_date.' 00:00:00'); 

                    }*/
                    $booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            $model_id = $vlist->vehicle_model_id;
                            $vehicle_status = $vlist->status;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                            if($vehicle_status == 'In'){
                                $vstatus = 'Prepare To Delivery';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Customer Return at Center';
                            }

                            if($vlist->receive_date != '' && $vlist->is_amount_receive === 1){
                                $vstatus = 'Completed';
                            }

                            $vehicle_model = $vehicleModel;
                            $booking_no = $vlist->booking_no;
                            $customer_name = $vlist->customer_name;
                            $vehicle_number = $vlist->vehicle;
                            $pick_up = date("d M F",strtotime($vlist->pick_up));
                            $pick_up_time = $vlist->pick_up_time;

                            $expected_drop = date("d M F",strtotime($vlist->expected_drop));
                            $expected_drop_time = $vlist->expected_drop_time;
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'vehicle_number' => $vehicle_number, 'vehicle_status' => $vstatus, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'expected_drop_date' => $expected_drop, 'expected_drop_time' => $expected_drop_time]; 
                         }

                        
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Vehicle Filter Result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Vehicle not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Upcoming Booking
    public function upcoming_booking_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            /*$city_id = $request->city_id;
            $center = $request->center_id;
            $ride_type = $request->ride_type;
            $from_date = date("Y-m-d",strtotime($request->from_date));
            $to_date = date("Y-m-d",strtotime($request->to_date));*/
            $error = "";
           /* if($employee_id == ""){
                $error = "Please select date range";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }*/
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 


                    $stationinfo = DB::table('stations')->where('employee_id', $employee_id)->where('status', '=', 'Live')->first();
                    
                    $city_id = $stationinfo->city_id;
                    $center = $stationinfo->id;
                    $station_name = $stationinfo->station_name;
                    $today = date('Y-m-d');
                    $current_time = date('H:i:s');

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive')->where('user_id',$employee_id)->where('booking_status','1')->where('pick_up', '>', $today);

                    if($center){
                        $booked_vehicleList = $booked_vehicleList->where('station',$station_name);    
                    }

                   /* if($ride_type){
                        $vehicleList = $vehicleList->where('v.ride_type',$ride_type);    
                    }*/

                   /* if($from_date){
                        $rentinList = $rentinList->wheredate('available_date',' > ',$available_date);   
                        $vehicleList = $vehicleList->where('available_date', '<=', $from_date.' 00:00:00'); 

                    }*/
                    $booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            $model_id = $vlist->vehicle_model_id;
                            $vehicle_status = $vlist->status;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                            if($vehicle_status == 'In'){
                                $vstatus = 'Prepare To Delivery';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Customer Return at Center';
                            }

                            if($vlist->receive_date != '' && $vlist->is_amount_receive === 1){
                                $vstatus = 'Completed';
                            }

                            $vehicle_model = $vehicleModel;
                            $booking_no = $vlist->booking_no;
                            $customer_name = $vlist->customer_name;
                            $vehicle_number = $vlist->vehicle;
                            $pick_up = date("d M F",strtotime($vlist->pick_up));
                            $pick_up_time = $vlist->pick_up_time;

                            $expected_drop = date("d M F",strtotime($vlist->expected_drop));
                            $expected_drop_time = $vlist->expected_drop_time;
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'vehicle_number' => $vehicle_number, 'vehicle_status' => $vstatus, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'expected_drop_date' => $expected_drop, 'expected_drop_time' => $expected_drop_time]; 
                         }

                        
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Vehicle Filter Result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Vehicle not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Old Booking
    public function old_booking_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $filterdate = date("Y-m-d",strtotime($request->filterdate));
            $error = "";
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 


                    $stationinfo = DB::table('stations')->where('employee_id', $employee_id)->where('status', '=', 'Live')->first();
                    
                    $city_id = $stationinfo->city_id;
                    $center = $stationinfo->id;
                    $station_name = $stationinfo->station_name;
                    if(!$filterdate){
                        $filterdate = date('Y-m-d');    
                    }
                    $current_time = date('H:i:s');

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status')->where('user_id',$employee_id)->where('is_amount_receive','1')->where('booking_status','1')->where('receive_date', '<', $filterdate);

                    if($center){
                        $booked_vehicleList = $booked_vehicleList->where('station',$station_name);    
                    }

                   /* if($ride_type){
                        $vehicleList = $vehicleList->where('v.ride_type',$ride_type);    
                    }*/

                   /* if($from_date){
                        $rentinList = $rentinList->wheredate('available_date',' > ',$available_date);   
                        $vehicleList = $vehicleList->where('available_date', '<=', $from_date.' 00:00:00'); 

                    }*/
                    $booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            $model_id = $vlist->vehicle_model_id;
                            $vehicle_status = $vlist->status;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                            $vstatus = 'Completed';
                            

                            $vehicle_model = $vehicleModel;
                            $booking_no = $vlist->booking_no;
                            $customer_name = $vlist->customer_name;
                            $vehicle_number = $vlist->vehicle;
                            $pick_up = date("d M F",strtotime($vlist->pick_up));
                            $pick_up_time = $vlist->pick_up_time;

                            $expected_drop = date("d M F",strtotime($vlist->expected_drop));
                            $expected_drop_time = $vlist->expected_drop_time;
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'vehicle_number' => $vehicle_number, 'vehicle_status' => $vstatus, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'expected_drop_date' => $expected_drop, 'expected_drop_time' => $expected_drop_time]; 
                         }

                        
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Vehicle Filter Result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Vehicle not available right now';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);    
                    }

                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

     

    //Booking Detail

    public function booking_details(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;

            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->first();
                    
                   
                    if($booking){
                        $bike_options = array();
                        $customer_id = $booking->customer_id;
                        $customerImage = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->pluck('image')[0];

                        if($customerImage){
                            $selfi  =  $baseUrl."/public/".$customerImage;
                        }else{
                           $selfi  =  "";
                        }
                        /* Customer Licence */
                        $custdoc = new CustomerDocuments();
                        $customerLicense = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'Driving License');

                        $licence_image  = '';
                        if($customerLicense->front_image){
                            $licence_image  .=  "Front Image: <br />".$baseUrl."/public/".$customerLicense->front_image.'<br />';
                        }
                        if($customerLicense->back_image){
                            $licence_image  .=  "Back Image: <br />".$baseUrl."/public/".$customerLicense->back_image;
                        }
                        if($customerLicense->other_image){
                            $licence_image  .=  "Other Image: <br />".$baseUrl."/public/".$customerLicense->other_image;
                        }

                        /* Customer Adhaar */
                        $customeradaar = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'ID Proof (Adhaar Card)');
                        
                        $adaarimage  = '';
                        if($customeradaar){
                            if($customeradaar->front_image){
                                $adaarimage  .=  "Front Image: <br />".$baseUrl."/public/".$customeradaar->front_image;
                            }
                            if($customeradaar->back_image){
                                $adaarimage  .= "Back Image: <br />".$baseUrl."/public/".$customeradaar->back_image;
                            }
                            if($customeradaar->other_image){
                                $adaarimage  .=  "Other Image: <br />".$baseUrl."/public/".$customeradaar->other_image;
                            }
                        }
                        
                         $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];

                         $vehicle_status = $booking->status;
                         $vstatus ='';
                         if($vehicle_status == 'In'){
                                $vstatus = 'Prepare To Delivery';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Customer Return at Center';
                            }

                            if($booking->receive_date != '' && $booking->is_amount_receive === 1){
                                $vstatus = 'Completed';
                            }

                        $before_ride_img = DB::table('booked_vehicle_images')->where('customer_id', $customer_id)->where('booking_id', $booking_id)->where('image_type', 'Before Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_before_list = array();
                        foreach($before_ride_img as $beforeimg)
                        {
                            if($beforeimg->image){
                                $beforeimgurl = $baseUrl."/public/".$beforeimg->image; 
                                
                                $booked_vehicle_before_list[] = array('title' => $beforeimg->title, 'image' => $beforeimgurl); 
                            }
                        } 

                        $after_ride_img = DB::table('booked_vehicle_images')->where('customer_id', $customer_id)->where('booking_id', $booking_id)->where('image_type', 'After Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_after_list = array();
                        foreach($after_ride_img as $afterimg)
                        {
                            if($afterimg->image){
                                $afterimgurl = $baseUrl."/public/".$afterimg->image; 
                                
                                $booked_vehicle_after_list[] = array('title' => $afterimg->title, 'image' => $afterimgurl); 
                            }
                        }    

                         $customerLogs[] = array('heading' => 'Customer Logs', 'content' => 'Not Found.');   
   
                         $selfiarr[] = array('heading' => 'Customer Selfi', 'content' => $selfi);

                         $licensDocearr[] = array('heading' => 'Customer License', 'content' => $licence_image);

                        $adhaarDocearr[] = array('heading' => 'Customer Adhaar ID', 'content' => $adaarimage);

                        $trackVehicle[] = array('heading' => 'Track Vehicles', 'content' => 'Not found');

                        
                        $bike_options[] = array('optionTitle' => 'Show Logs', 'optionData' => $customerLogs);
                        
                        $bike_options[] = array('optionTitle' => 'View License', 'optionData' => $licensDocearr);

                        $bike_options[] = array('optionTitle' => 'View Adhaar', 'optionData' => $adhaarDocearr);

                        $bike_options[] = array('optionTitle' => 'Track Vehicle', 'optionData' => $trackVehicle);

                        $bike_options[] = array('optionTitle' => 'Selfi', 'optionData' => $selfiarr);

                        

                        $status_code = '1';
                        $message = 'Booking Details';

                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'Type' => $vstatus, 'booking_no' => $booking->booking_no, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'vehicle_number' => $booking->vehicle, 'employee_name' => $employee->name, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time,  'total_amount' => $booking->total_amount, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)), 'bike_options' => $bike_options,  'vehicle_image_before_ride' => $booked_vehicle_before_list, 'vehicle_image_after_ride' => $booked_vehicle_after_list );
                    }else{
                         $status_code = '0';
                        $message = 'No notification found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    
    public function employee_attendance(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $attendanceType = $request->attendanceType;
            $error = "";
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        $attendance_date = date('Y-m-d');
                        $time = date('H:i:s');
                        if($attendanceType == 'IN'){
                            $intime = DB::table('employee_attendance')->insert([
                                'employee_id' => $employee_id,
                                'attendance_date' => $attendance_date,
                                'check_in' => $time,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                            $message = 'You are Check IN Successfully';
                        }
                        if($attendanceType == 'OUT'){
                            $employeeintime = DB::table('employee_attendance')->where('employee_id', $employee_id)->where('attendance_date', $attendance_date)->first();
                            if($employeeintime){
                                $outtime =  DB::table('employee_attendance')->where('employee_id', '=', $employee_id)->where('attendance_date', '=', $attendance_date)->update(['check_out' => "".$time, 'updated_at' => $date]);
                            }

                            $message = 'You are Check Out Successfully';    
                        }
                        
                        $status_code = $success = '1';
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                    
                    
                } else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    

    //Make Payment
    public function add_money(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $amount = $request->amount;
            $error = "";
            if($amount == "" && $amount <= "0"){
                $error = "Please add valid amount";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $comment = 'Online Amount added';
                    $payment_type = 'Online';
                    $payment_status = "pending";
                    $walletAmtid = DB::table('customer_wallet_payments')->insertGetId(['customer_id' => $customer_id, 'amount' => "".$amount, 'comment' => $comment, 'payment_type' => $payment_type, 'payment_status' => $payment_status, 'created_at' => $date, 'isactive' => '1',  'updated_at' => $date]); 

                    $order_id = $walletAmtid.'_'.time();
                    $status_code = '1';
                    $message = 'Wallet Amount';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'order_id' => $order_id, 'amount' => $amount); 
                    
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function wallet_amount(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $error = "";
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                    $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');
                    
                    $totalamount = '₹ '.($walletAmt-$orderWalletAmt);

                    
                    $status_code = '1';
                    $message = 'Total Wallet Amount';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'wallet_amount' => $totalamount); 
                    
                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function confirm_wallet_amount(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $order_id = $request->order_id;
            $error = "";
           if($order_id == ""){
                $error = "Please send valid order id.";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $odridarr = explode('_', $order_id);
                    $walletchk = DB::table('customer_wallet_payments')->select('id','amount','created_at')->where('customer_id', $customer_id)->where('id', $odridarr[0])->orderBy('id', 'Asc')->first();
                    if($walletchk){

                        $status = PaytmWallet::with('status');
                        $status->prepare(['order' => $order_id]);
                        $status->check();
                        
                        $response = $status->response(); // To get raw response as array
                        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=txn-status-api-description
                        /*print_r($response);
                        exit;*/

                        $responseMessage = $status->getResponseMessage(); //Get Response Message If Available
                        //get important parameters via public methods
                        $orderId = $status->getOrderId(); // Get order id
                        
                        $transactionId = $status->getTransactionId(); // Get transaction id
                        
                        if($status->isSuccessful()){
                          //Transaction Successful
                            $payment_status = 'success';
                            DB::table('customer_wallet_payments')->where('id', '=', $odridarr[0])->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);

                            $status_code = $success = '1';
                            $message = 'Your Wallet Amount Added Successfully.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);  

                        }else if($status->isFailed()){
                          //Transaction Failed
                            
                            $payment_status = 'failed';
                            DB::table('customer_wallet_payments')->where('id', '=', $odridarr[0])->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);


                            $status_code = $success = '1';
                            $message = 'Your Wallet Amount Transaction Failed.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message); 
                        }else if($status->isOpen()){
                          //Transaction Open/Processing
                            $payment_status = 'pending';
                            DB::table('customer_wallet_payments')->where('id', '=', $odridarr[0])->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);
                            $status_code = $success = '1';
                            $message = 'Your Wallet Amount Transaction is Pending / Processing.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);
                        }
                        

                        
                    }else{
                        $status_code = '0';
                        $message = 'Booking id not valid';
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                    }
                } else{
                    $status_code = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function notification_list(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $notificationExists = DB::table('notifications')->where('customer_id', $customer_id)->where('user_type', 'customer')->orderBy('id', 'DESC')->count();
                    $notify_List = array();
                    if($notificationExists > 0){
                        $notifyList = DB::table('notifications')->select('id','notification_title','notification_content','notification_type','created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();

                        
                        foreach($notifyList as $notifylist)
                        {
                            $notification_type = $notifylist->notification_type;
                            
                            $notify_List[] = array('id' => "".$notifylist->id, 'notification_title' => $notifylist->notification_title,'notification_content' => "".$notifylist->notification_content, 'notification_type' => $notification_type, 'date' => date('d-m-Y H:i:s', strtotime($notifylist->created_at))); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Notification List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'notify_List' => $notify_List);
                    }else{
                         $status_code = '0';
                        $message = 'No notification found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => $customer_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function coupon_listing(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    /* Used Coupon List */
                    $usedCouponList = array('REFFER00');
                    $bookedCoupons = \DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_status', 'success')->distinct()->select('coupon_code')->get();
                    if($bookedCoupons){
                        foreach ($bookedCoupons as $usedCoupons) {
                            if($usedCoupons->coupon_code){
                                $usedCouponList[] = $usedCoupons->coupon_code;
                            }
                        }
                    }
                    //print_r($usedCouponList);
                    $referCouponList = DB::table('customer_referal_coupons')->select('id','customer_id','coupon_code', 'discount','description')->where('customer_id', $customer_id)->where('status', 'Live')->whereNotIn('coupon_code', $usedCouponList)->orderBy('id', 'ASC')->get();
                    $coupon_list = array();
                    if($referCouponList){
                        foreach($referCouponList as $couponlist)
                        {
                            
                            $coupon_list[] = array('coupon_code' => "".$couponlist->coupon_code, 'discount_type' => 'percentage', 'discount' => $couponlist->discount, 'description' => $couponlist->description); 
                           
                        }
                    } 

                    $generalCouponList = DB::table('coupons')->select('id','title','discount_type','discount','description')->where('status', 'Live')->whereNotIn('title', $usedCouponList)->orderBy('id', 'ASC')->get();
                    if($generalCouponList){
                        foreach($generalCouponList as $gencouponlist)
                        {
                            
                            $coupon_list[] = array('coupon_code' => "".$gencouponlist->title, 'discount_type' => $gencouponlist->discount_type, 'discount' => $gencouponlist->discount, 'description' => $gencouponlist->description); 
                           
                        }
                    } 

                    $status_code = '1';
                    $message = 'Coupon List';
                    $json = array('status_code' => $status_code,  'message' => $message, 'coupon_list' => $coupon_list);
                    
                }else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //contact_us
    public function contact_us(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $error = "";
            $contactusid = 1;
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $rspage = DB::table('pages')->where('id', $contactusid)->first();
                    $pagecontent = $rspage->content;
                    $pagetitle = $rspage->title;

                    $status_code = $success = '1';
                    $message = 'Contact Us';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'heading' => $pagetitle, 'content' => $pagecontent);

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //contact_us
    public function about_us(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $error = "";
            $pageid = 2;
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $rspage = DB::table('pages')->where('id', $pageid)->first();
                    $pagecontent = $rspage->content;
                    $pagetitle = $rspage->title;

                    $status_code = $success = '1';
                    $message = 'About Us';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'heading' => $pagetitle, 'content' => $pagecontent);

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //contact_us
    public function privacy(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $error = "";
            $pageid = 3;
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $rspage = DB::table('pages')->where('id', $pageid)->first();
                    $pagecontent = $rspage->content;
                    $pagetitle = $rspage->title;

                    $status_code = $success = '1';
                    $message = 'privacy';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'heading' => $pagetitle, 'content' => $pagecontent);

                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

   
    

    //Agri Land Feedback
    public function feedback(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $comment = $request->comment;
            $error = "";
            if($comment == ""){
                $error = "Please enter comment for feedback";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    
                    DB::table('feedback')->insert(['customer_id' => $customer_id, 'comment' => $comment, 'status' => 'live', 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Feedback added successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);


                } else{
                    $status_code = $success = '0';
                    $message = 'Customer not valid';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }

    

    public function push_notification($data, $device_tokens)
        {
            $senderid = '789600431472';
            $msg = array
            (
                'title'  => $data[0],
                'name' => $data[1],
                'body' => $data[2],
                //'image' => $data[3],
                //'product_id' => $data[4],
                'vibrate' => 1,
                'sound'  => 'mySound',      
                'driverData'=>$data,        
            );

            $dataarr['data'] = array
            (
                'title'  => $data[0],
                'name' => $data[1],
                'body' => $data[2],
                //'image' => $data[3],
                //'product_id' => $data[4],
                        
            );

            $fields = array
            (
                'to'        => $device_tokens,
                'notification'  => $msg,
                'data'  => $dataarr
            ); 
            $serverKey = 'AAAAt9fabXA:APA91bFYHHT1fn136eJoJS2qNormp-KGZugqxTsSb859REUYAdVr9mWp7qsKgCeEmGVvygGIhybVOrc49S79DGknfMqVfvc_wi8piLb0TjjcKzIjJOY2snY763yCQeAEuDo32Wj6fA26'; 
            $headers = array
            (
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );

            curl_close( $ch );
            return true;
        }
    
}
