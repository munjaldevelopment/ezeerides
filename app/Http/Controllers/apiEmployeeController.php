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
    //START EMPLOYEE LOGIN
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

                        $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                    
                        DB::table('users')->where('id', '=', $empid)->update(['otp' => "".$otp, 'updated_at' => $date]);

                        $status_code = '1';
                        $message = 'Employee login OTP Send';
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' =>"".$empid, 'otp' => "".$otp);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Employee Not Active, Please contact to support';
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => "".$empid, 'email' => $email, 'otp' => "".$otp ,"smsurl"=>"http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
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
                    $device_id = $device_id;
                    $fcmToken = $fcmToken;
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
                    
                    $smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");

                    $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");


                     DB::table('users')->where('id', '=', $employee_id)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send sucessfully1';
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
               
               $empCashCollection = $this->getcashCollected($employee_id);
                $status_code = $success = '1';
                $message = 'Employee Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id , 'name' => $name, 'email' => $email, 'mobile' => $mobile, 'empCashCollection' => $empCashCollection);


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
            $expences_reciept = $request->expences_reciept;
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
                        if($expences_reciept != ''){
                            $image_parts = explode(";base64,", $expences_reciept);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];

                            $expencesreciept = rand(10000, 99999).'-'.time().'.'.$image_type;
                            $destinationPath = public_path('/uploads/expences_reciept/').$expencesreciept;

                            $data = base64_decode($image_parts[1]);
                           // $data = $image_parts[1];
                            file_put_contents($destinationPath, $data);
                        }
                        $booking_id = DB::table('employee_expences')->insert([
                            'employee_id' => $employee_id,
                            'station_id' => $station_id,
                            'expences_type' => $expences_type,
                            'amount' => $amount,
                            'expences_reciept' => 'uploads/expences_reciept/'.$expencesreciept,
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
                           
                            $vehicle_status_exist = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->count();
                            
                           
                            $vstatus = "At Station";
                            if($vehicle_status_exist > 0){

                                $vehicle_status = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->pluck('status')[0];
                               
                                if($vehicle_status == 'In'){
                                    $vstatus = 'At Station';
                                }

                                if($vehicle_status == 'Out'){
                                    $vstatus = 'Out of Station';
                                }
                            }    

                            $fleet_List[] = array('id' => "".$rsfleet->id, 'vehicle_model' => $vehicleModel,'vehicle_number' => $rsfleet->vehicle_number,'vehicle_status' => $vstatus); 
                          
                        } 

                        
                        $status_code = '1';
                        $message = 'Fleet Calendar';
                        $json = array('status_code' => $status_code,  'message' => $message, 'fleet_List' => $fleet_List);
                    }else{
                         $status_code = '0';
                        $message = 'No Fleet found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getTraceAsString();//$e->getTraceAsString(); getMessage //
    
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

    public function our_fleet(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    $employeeFleetExists = DB::table('stations as s')->join('station_has_vehicles as sv', 's.id', '=', 'sv.station_id')->join('vehicles as v', 'v.id', '=', 'sv.vehicle_id')->join('vehicle_models as vm', 'vm.id', '=', 'v.vehicle_model')->select('v.id','vm.model','v.vehicle_number', 'v.vehicle_model', 'vm.allowed_km_per_hour', 'vm.charges_per_hour', 'vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('v.status','Live')->where('s.employee_id', $employee_id)->orderBy('v.id', 'DESC')->get();
                    $fleet_List = array();
                    $bike_feature = array();
                    if($employeeFleetExists){
                        foreach($employeeFleetExists as $rsfleet)
                        {
                           $vehicle_number = $rsfleet->vehicle_number;
                           $vehicleModel = $rsfleet->model;
                            $numBike = DB::table('stations as s')->join('station_has_vehicles as sv', 's.id', '=', 'sv.station_id')->join('vehicles as v', 'v.id', '=', 'sv.vehicle_id')->where('s.employee_id', $employee_id)->where('v.vehicle_model', $rsfleet->vehicle_model)->orderBy('v.id', 'DESC')->count();

                            

                            $allowed_km_per_hour = $rsfleet->allowed_km_per_hour.' KM';
                            $excess_km_charges = '0';
                            $charges_per_hour = '₹ '.$rsfleet->charges_per_hour.' / Hr';
                            $bikecharges = $rsfleet->charges_per_hour;
                            $insurance_charges_per_hour =$rsfleet->insurance_charges_per_hour;
                            $insurance_charges = $rsfleet->insurance_charges_per_hour;
                            $penalty_amount_per_hour = '₹ '.$rsfleet->penalty_amount_per_hour.' / Hr';
                            
                            
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

                            if($numBike > 0){
                                
                                $bike_feature[] =  ['title' => 'Total bike', 'subtitle' => "".$numBike];

                                
                            }

                            
                            $baseUrl = URL::to("/");
                            $vehicle_image  = "";
                            if($rsfleet->vehicle_image){
                                $vehicle_image  =  $baseUrl."/public/".$rsfleet->vehicle_image;
                            
                            }

                            $vehicle_status = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->pluck('status')[0];
                           $vstatus = "In Service";
                            if($vehicle_status == 'In'){
                                $vstatus = 'At Station';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Out of Station';
                            }

                            $fleet_List[] = array('id' => "".$rsfleet->id, 'vehicle_model' => $vehicleModel,'vehicle_image' => $vehicle_image, 'bike_feature' => $bike_feature ); 
                           
                        } 

                        
                        $status_code = '1';
                        $message = 'Our Fleet';
                        $json = array('status_code' => $status_code,  'message' => $message, 'fleet_List' => $fleet_List);
                    }else{
                         $status_code = '0';
                        $message = 'No Fleet found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
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
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
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
                        $pick_upDateTime = $start_date;
                        $expected_dropDateTime = $end_date;
                        $timestamp1 = strtotime($pick_upDateTime);
                        $timestamp2 = strtotime($expected_dropDateTime);

                        $hours = abs($timestamp2 - $timestamp1)/(60*60);

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

                            $fleetFare = 0;
                            $total_price = 0;
                            if($hours > 0){
                                //echo $bikecharges;
                                $VehicleRegister = new VehicleRegister();
                                $fleetFare = $VehicleRegister->getFleetFare($hours,$charges_per_hour);
                                $total_price = $fleetFare+$insurance_charges_per_hour;
                            }
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'allowed_km_per_hour' =>$allowed_km_per_hour, 'charges_per_hour' =>$charges_per_hour, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'premium_charges_per_hour' => $premium_charges_per_hour, 'penalty_amount_per_hour' => $penalty_amount_per_hour, 'vehicle_image' => $vehicle_image, 'booking_hour' => "".$hours , 'total_price' => "".$total_price]; 
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
                        
                        $start_date_time = date("d-m-Y H:i:s",strtotime($request->start_date));
                        $end_date_time = date("d-m-Y H:i:s",strtotime($request->end_date));

                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'start_date' => $start_date_time, 'end_date' => $end_date_time, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Fleet not available right now';
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }
    //END 

    //Confirm booking
    public function booking_bike_detail(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $bike_model_id = $request->bike_id;
            $city_id = $request->city_id;
            $station_id = $request->center_id;
            $from_date = $request->start_date;
            $to_date = $request->end_date;
            $error = "";
            if($station_id == ""){
                $error = "Please choose ride type for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

            if($bike_model_id == ""){
                $error = "Please choose bike model for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    
                    $bikeDetail = DB::table('vehicle_models')->where('id', $bike_model_id)->where('status', '=', 'Live')->first();
                    $bike_feature = array();
                    if($bikeDetail){ 
                        $vehicle_model = $bikeDetail->model;
                        $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour.' KM';
                        $excess_km_charges = '0';
                        $charges_per_hour = '₹ '.$bikeDetail->charges_per_hour.' / Hr';
                        $bikecharges = $bikeDetail->charges_per_hour;
                        $insurance_charges_per_hour =$bikeDetail->insurance_charges_per_hour;
                        $insurance_charges = $bikeDetail->insurance_charges_per_hour;
                        $penalty_amount_per_hour = '₹ '.$bikeDetail->penalty_amount_per_hour.' / Hr';
                        $helmet_charges = '₹ 0';
                        $helmet_status = '1';
                        $document_status = '';

                        $pick_upDateTime = $from_date;
                        $expected_dropDateTime = $to_date;
                        $timestamp1 = strtotime($pick_upDateTime);
                        $timestamp2 = strtotime($expected_dropDateTime);

                        $hours = abs($timestamp2 - $timestamp1)/(60*60);

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

                         if($helmet_charges){
                            $bike_feature[] =  ['title' => 'Number of Helmet (2)', 'subtitle' => $helmet_charges];

                        }

                        if($document_status){
                            $bike_feature[] =  ['title' => 'Documents Status', 'subtitle' => $document_status];

                        }


                       
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];

                        $booking_time = $from_date."-".$to_date;

                        $start_trip_date = date('d-m-Y',strtotime($from_date));
                        $start_trip_time = date('H:i',strtotime($from_date));
                        $end_trip_date = date('d-m-Y',strtotime($to_date));
                        $end_trip_time = date('H:i',strtotime($to_date));

                        
                        
                        $fleetFare = 0;
                        $total_price = 0;
                        if($hours > 0){
                            //echo $bikecharges;
                            $VehicleRegister = new VehicleRegister();
                            $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                            $total_price = $fleetFare+$insurance_charges;
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
                        $message = 'Bike Details';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id, 'city_id' => $city_id , 'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model, 'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'bike_feature' => $bike_feature, 'helmet_status' => $helmet_status, 'document_status' => $document_status, 'pickup_station' => $station_name, 'booking_time' => $booking_time ,  'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time, 'without_insurance_price' => "".$fleetFare, 'total_price' => '₹ '.$total_price, 'booking_hours' => $hours." Hr" );
                    }else{
                        $status_code = $success = '0';
                        $message = 'Bike not valid';
                        
                        $json = array('status_code' => $status_code, 'message' => $message);
                    } 
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

    public function getCustomerDetail(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $mobile = $request->mobile;
            $error = "";
            if($error == ""){
                $customer = DB::table('customers')->where('mobile', $mobile)->where('status', '=', 'Live')->first();
                if($customer){
                    if($customer->name){
                        $name = $customer->name;
                    }else{
                        $name = "";
                    }
                    if($customer->email){
                        $email = $customer->email; 
                    }else{
                        $email = "";
                    }
                    if($customer->address){
                        $address = $customer->address;    
                    }else{
                        $address = "";
                    }
                    $status_code = '1';
                    $message = 'Customer Info';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer->id , 'name' => $name, 'email' => $email, 'mobile' => $mobile, 'address' => $address, 'customer_type' => 'already_exist' ); 
                    
                } else{
                    /*$otp = rand(11111, 99999);
                    $customer = DB::table('customers')->where('mobile', $mobile)->where('status', '!=', 'Live')->first();
                    if($customer){
                        $customerid = $customer->id;
                        $smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");

                        $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                    
                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'updated_at' => $date]);

                        $status_code = $success = '0';
                        $message = 'Customer Otp Send, Please Process Next Step';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => "", 'mobile' => $mobile, "customer_type" => "new", 'otp' => "".$otp);
                    }else{
                     
                        
                     //$smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");
                     $smsmessage = str_replace(" ", '%20', "Thank you for registering on AUTO AWAY RENTALS app. ".$otp." is the OTP for your Login id. Please do not share with anyone.");

                     $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                     $device_id = '';
                     $fcmToken = '';
                     $customerid = DB::table('customers')->insertGetId(['mobile' => $mobile, 'otp' => "".$otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'created_at' => $date, 'status' => 'Not live',  'updated_at' => $date]); 

                    $status_code = $success = '0';
                    $message = 'Customer not Exist';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '','name' => '', 'email' => '', 'mobile' => $mobile, 'address' => '', 'otp' => "".$otp, 'customer_type' => 'new' );
                    }*/
                    $status_code = $success = '0';
                    $message = 'Customer not Exist';
                     $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '','name' => '', 'email' => '', 'mobile' => $mobile, 'address' => '', 'customer_type' => 'new' );
                    
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

    public function upload_customer_documents(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $title = $request->document_type;
            $front_image = $request->front_image;
            $back_image = $request->back_image;
            $other_image = $request->other_image;
            $status = 'Live';
            
            
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
            if($customer){ 
                $customerDoc = DB::table('customer_documents')->where('title', $title)->where('customer_id', $customer_id)->first();
                if(!$customerDoc){ 
                    $frontimage = '';
                    $backimage = '';
                    $otherimage = '';
                    if($front_image != ''){
                        $image_parts = explode(";base64,", $front_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];

                        $frontimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                        $destinationPath = public_path('/uploads/customer_documents/').$frontimage;

                        $data = base64_decode($image_parts[1]);
                       // $data = $image_parts[1];
                        file_put_contents($destinationPath, $data);
                    }

                    if($back_image != ''){
                        $image_parts = explode(";base64,", $back_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];

                        $backimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                        $backimgdestinationPath = public_path('/uploads/customer_documents/').$backimage;

                        $data = base64_decode($image_parts[1]);
                       // $data = $image_parts[1];
                        file_put_contents($backimgdestinationPath, $data);
                    }

                    if($other_image != ''){
                        $image_parts = explode(";base64,", $other_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];

                        $otherimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                        $otherdestinationPath = public_path('/uploads/customer_documents/').$otherimage;

                        $data = base64_decode($image_parts[1]);
                       // $data = $image_parts[1];
                        file_put_contents($otherdestinationPath, $data);
                    }
                    DB::table('customer_documents')->insert(['customer_id' => $customer_id, 'title' => $title, 'front_image' => 'uploads/customer_documents/'.$frontimage, 'back_image' => 'uploads/customer_documents/'.$backimage, 'other_image' => 'uploads/customer_documents/'.$otherimage, 'status' => $status, 'created_at' => $date, 'updated_at' => $date]);
                    
                    $status_code = $success = '1';
                    $message = 'Customer Documents uploaded successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                } else{
                    $status_code = $success = '0';
                    $message = 'Documents already uploaded for '.$title;
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }                    

            } else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
            }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
        }
        
        return response()->json($json, 200);
    }
    //Reserve Bike
    public function reserve_bike(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $customer_id = $request->customer_id;
            $customer_name = $request->customer_name;
            $customer_phone = $request->customer_mobile;
            $customer_email = $request->customer_email;
            $bike_model_id = $request->bike_id;
            $city_id = $request->city_id;
            $station_id = $request->center_id;
            $hours = $request->hours;
            $total_amount = $request->total_amount;
            $from_date = $request->start_date;
            $to_date = $request->end_date;
            $document_status = 0;
            $error = "";
            
            if($bike_model_id == ""){
                $error = "Please choose bike model for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($customer_id == ""){
                $error = "Please enter valid customer";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($customer_phone == ""){
                $error = "Please enter valid customer Phone";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            
            if($error == ""){

                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    $customer_doc = DB::table('customer_documents')->where('customer_id', $customer_id)->where('status', '=', 'Not Live')->first();
                    if($customer_doc){

                        $status_code = $success = '0';
                        $message = 'Customer Document not verified yet.';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                    }else{
                        $coupon_code = '';
                        $coupon_discount = 0;
                        $referDisc = DB::table('customer_referal_coupons')->where('coupon_code', $coupon_code)->where('status', '=', 'Live')->first();
                        if($referDisc){
                            
                            if($referDisc->discount > 0){
                                $perval = ($referDisc->discount/100);
                                $coupon_discount = $perval*$total_amount;   
                            }
                        }
                        $couponDisc = DB::table('coupons')->where('title', $coupon_code)->where('status', '=', 'Live')->first();
                        if($couponDisc){
                            if($couponDisc->discount_type == 'amount'){
                                $coupon_discount = $couponDisc->discount;
                            }
                            if($couponDisc->discount_type == 'percentage'){
                                $perval = ($couponDisc->discount/100);
                                $coupon_discount = $perval*$total_amount;   
                            }
                        }

                        $status = 'In';
                        $booking_status = '1';
                        $payment_status = 'success';
                        $payment_type = 'cashToEmp';
                        $booking_from = 'employee';
                        $customer_name = $customer_name;
                        $phone = $customer_phone;
                        $pick_up = date('Y-m-d',strtotime($from_date));
                        $pick_up_time = date('H:i',strtotime($from_date));
                        $expected_drop = date('Y-m-d',strtotime($to_date));
                        $expected_drop_time = date('H:i',strtotime($to_date));
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                        $user_id = $employee_id;
                        
                        $allowed_km_per_hour = DB::table('vehicle_models')->where('id', $bike_model_id)->pluck('allowed_km_per_hour')[0];

                        $allowed_km = ($allowed_km_per_hour*$hours);
                        $otp = rand(111111, 999999);
                        $booking_id = VehicleRegister::insertGetId([
                            'user_id' => $user_id,
                            'station' => $station_name,
                            'customer_id' => $customer_id,
                            'vehicle_model_id' => $bike_model_id,
                            'customer_name' => $customer_name,
                            'register_otp' => $otp,
                            'phone' => $phone,
                            'pick_up' => $pick_up,
                            'pick_up_time' => $pick_up_time,
                            'expected_drop' => $expected_drop,
                            'expected_drop_time' => $expected_drop_time,
                            'coupon_code' => $coupon_code,
                            'coupon_discount' => $coupon_discount,
                            'booking_hours' => $hours,
                            'allowed_km' => $allowed_km,
                            'total_amount' => $total_amount,
                            'booking_status' => $booking_status,
                            'payment_status' => $payment_status,
                            'payment_type' => $payment_type,
                            'booking_from' => $booking_from,
                            'status' => $status,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        $booking_no = "EZR".date('YmdHis').str_pad($booking_id, 5, "0", STR_PAD_LEFT);
            
                        VehicleRegister::where('id', $booking_id)->update(['booking_no' => $booking_no]);
                        // Send booking no for customer mobile
                         $vehicle_model = DB::table('vehicle_models')->where('id', $bike_model_id)->pluck('model')[0];
                        
                        $smsmessage = str_replace(" ", '%20', "Your booking is confirmed fo ".$vehicle_model.", booking price for your ride is: ".$total_amount." rs. And your booking code is ".$otp.". Enjoy your ride!.");

                        $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$phone."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                        
                        $status_code = $success = '1';
                        $message = 'Bike Enquiry Reserved Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id, 'customer_id' => $customer_id, 'booking_id' => $booking_id, 'booking_no' => $booking_no , 'total_amount' => $total_amount , 'booking_hours' => $hours." Hr" );
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }
    
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

                     $booked_vehicleList1 = DB::table('vehicle_registers as v')->join('booking_expended as be', 'be.booking_id', '=', 'v.id')->select('v.id','v.vehicle_model_id','v.booking_no','v.user_id','v.customer_id', 'v.customer_name','v.pick_up','v.pick_up_time','be.expand_date as expected_drop','be.expand_time as expected_drop_time','v.station','v.vehicle','v.status','v.receive_date','v.is_amount_receive','is_expended')->where('v.user_id',$employee_id)->where('v.booking_status','1')->where('v.due_penalty','no')->where('v.is_amount_receive','0')->where('v.is_expended','yes')->where('v.vehicle', '!=', '')->where('be.expand_date', '>=', $today);

                     $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive','is_expended')->where('user_id',$employee_id)->where('booking_status','1')->where('due_penalty','no')->where('is_amount_receive','0')->where('is_expended','no')->where('vehicle', '!=', '')->where('expected_drop', '>=', $today);
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
                    $booked_vehicleList = $booked_vehicleList->union($booked_vehicleList1)->orderBy('pick_up', 'asc')->get();
                    //$booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) > 0){
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
                            $is_expended = $vlist->is_expended;
                            $customer_name = $vlist->customer_name;
                            $vehicle_number = $vlist->vehicle;
                            $pick_up = date("d M Y",strtotime($vlist->pick_up));
                            $pick_up_time = $vlist->pick_up_time;

                            $expected_drop = date("d M Y",strtotime($vlist->expected_drop));
                            $expected_drop_time = $vlist->expected_drop_time;
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'vehicle_number' => $vehicle_number, 'vehicle_status' => $vstatus, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'expected_drop_date' => $expected_drop, 'expected_drop_time' => $expected_drop_time, 'is_expended' => $is_expended]; 
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
            //$message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
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

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive','is_upgrade')->where('user_id',$employee_id)->where('booking_status','1')->wheredate('expected_drop', '>=', $today)->where('expected_drop_time', '>=', $current_time);

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
                            
                            if($vlist->is_upgrade == 'yes'){
                                $upgrade_vehicle_model_id = DB::table('booking_upgrade_bike')->where('booking_id', $vlist->id)->where('payment_status', 'success')->orderBy('id', 'desc')->pluck('vehicle_model_id')[0];
                                $model_id = $upgrade_vehicle_model_id;
                            }else{
                                $model_id = $vlist->vehicle_model_id;    
                            }    
                            
                            $vehicle_status = $vlist->status;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];
                            $upgrade_button = 'f';
                            if($vehicle_status == 'In'){
                                $vstatus = 'Prepare To Delivery';
                                 if($vlist->vehicle == ''){
                                   $upgrade_button = 't';
                               }
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
                            
                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'vehicle_number' => $vehicle_number, 'vehicle_status' => $vstatus, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'expected_drop_date' => $expected_drop, 'expected_drop_time' => $expected_drop_time, 'is_upgrade_button' => $upgrade_button]; 
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
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

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','additional_amount', 'receive_amount','is_amount_receive','receive_date','status')->where('user_id',$employee_id)->where('booking_status','1')->where('due_penalty','no')->wheredate('expected_drop', '<=', $filterdate)->where('expected_drop_time', '<', $current_time);

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
                    $booked_vehicleList = $booked_vehicleList->orderBy('id', 'desc')->get(); 
                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            $model_id = $vlist->vehicle_model_id;
                            $vehicle_status = $vlist->status;
                            $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                            if($vehicle_status == 'In'){
                                $vstatus = 'Prepare To Delivery';
                                //$vstatus = '';
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Customer Return at Center';
                                //$vstatus = '';
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Current Booking
    public function search_order(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $search_keyword = $request->search_keyword;
            
            /*$ride_type = $request->ride_type;
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

                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','phone','user_id','customer_id', 'customer_name','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive')->where('user_id',$employee_id);

                    if($search_keyword){
                        $booked_vehicleList = $booked_vehicleList->where('booking_no', $search_keyword)->orWhere('phone', $search_keyword);    
                    }

                  
                    $booked_vehicleList = $booked_vehicleList->where('booking_no','!=','')->orderBy('id', 'asc')->get(); 

                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            $model_id = $vlist->vehicle_model_id;
                            $vehicle_status = $vlist->status;
                           $vehicleModel = '';
                           if($model_id){
                                $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];
                           }
                             
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
                        $message = 'Vehicle Search Result';
                        
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    } 

    //Due Penalties
    public function due_penalties(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $error = "";
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 


                    $stationinfo = DB::table('stations')->where('employee_id', $employee_id)->where('status', '=', 'Live')->first();
                    
                    $city_id = $stationinfo->city_id;
                    $center = $stationinfo->id;
                    $station_name = $stationinfo->station_name;
                   
                    $booked_vehicleList = DB::table('vehicle_registers')->select('id','vehicle_model_id','booking_no','user_id','customer_id', 'customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','additional_amount','receive_amount','receive_date','return_time','is_amount_receive')->where('user_id',$employee_id)->where('booking_status','1')->where('additional_amount', '>', 0)->where('is_amount_receive', '=', 1)->where('due_penalty','yes');
                    $booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) >0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            if($vlist->receive_amount < $vlist->additional_amount){
                                $model_id = $vlist->vehicle_model_id;
                                $vehicle_status = $vlist->status;
                                $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];
                                /* Customer info from prepare to delivery */
                                $bookingid = $vlist->id;
                                $customerDeliveyinfo = DB::table('vehicle_prepare_to_delivery')->where('booking_id', $bookingid)->first();

                                $vehicle_model = $vehicleModel;
                                $booking_no = $vlist->booking_no;
                                $penalty_amount = "".($vlist->additional_amount-$vlist->receive_amount);
                                $customer_name = $vlist->customer_name;
                                $customer_phone = $vlist->phone;
                                if($customerDeliveyinfo){
                                    $secondary_number = $customerDeliveyinfo->secondary_number;
                                    $parents_number = $customerDeliveyinfo->parents_number;
                                }else{
                                    $secondary_number = '';
                                    $parents_number = '';
                                }
                                $vehicle_number = $vlist->vehicle;
                                $pick_up = date("d M Y",strtotime($vlist->pick_up));
                                $pick_up_time = $vlist->pick_up_time;

                                $receive_date = date("d M Y",strtotime($vlist->receive_date));
                                $return_time = date("H:i:s",strtotime($vlist->return_time));
                                
                                $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'customer_phone' =>$customer_phone, 'secondary_number' =>$secondary_number, 'parents_number' =>$parents_number, 'vehicle_number' => $vehicle_number, 'penalty_amount' => $penalty_amount, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'receive_date' => $receive_date, 'return_time' => $return_time]; 
                             }
                        } 

                        
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Due Penalties Result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Penalty Vehicle not available right now';
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Due Penalties
    public function penalty_detail(Request $request)
    {
        try 
        {
            $json = $userData = array();
             $baseUrl = URL::to("/");
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 


                    $stationinfo = DB::table('stations')->where('employee_id', $employee_id)->where('status', '=', 'Live')->first();
                    
                    $city_id = $stationinfo->city_id;
                    $center = $stationinfo->id;
                    $station_name = $stationinfo->station_name;
                   
                    $booked_vehicleList = DB::table('vehicle_registers')->where('id',$booking_id)->where('user_id',$employee_id)->where('booking_status','1')->where('additional_amount', '>', 0)->where('is_amount_receive', '=', 1)->where('due_penalty','yes');
                    $booked_vehicleList = $booked_vehicleList->orderBy('pick_up', 'asc')->get(); 
                    if(count($booked_vehicleList) > 0){
                        $v_list = array();
                        foreach($booked_vehicleList as $vlist)
                        {
                            if($vlist->receive_amount < $vlist->additional_amount){
                                $model_id = $vlist->vehicle_model_id;
                                $customer_id = $vlist->customer_id;
                                $vehicle_status = $vlist->status;
                                $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];
                                /* Customer info from prepare to delivery */
                                $bookingid = $vlist->id;
                                $customerinfo = DB::table('customers')->where('id', $customer_id)->first();

                                $customerDeliveyinfo = DB::table('vehicle_prepare_to_delivery')->where('booking_id', $bookingid)->first();

                                $customerReturninfo = DB::table('vehicle_return_to_station')->where('booking_id', $bookingid)->first();

                                $vehicle_model = $vehicleModel;
                                $booking_no = $vlist->booking_no;
                                $penalty_amount = "".($vlist->additional_amount-$vlist->receive_amount);
                                $customer_name = $vlist->customer_name;
                                $customer_phone = $vlist->phone;
                                $customer_address = $customerinfo->address;
                                $customer_email = $customerinfo->email;
                                if($customerinfo->image){
                                    $customer_image  =  $baseUrl."/public/".$customerinfo->image;
                                }else{
                                   $customer_image  =  "";
                                }
                                $customerDocArr = array();
                                $customerDocList = DB::table('customer_documents')->select('id','title','front_image' ,'back_image', 'other_image')->where('customer_id', '=', $customer_id)->where('status', '=', 'Live')->orderBy('id', 'DESC')->get();
                               
                                foreach ($customerDocList as $doclist) {
                                    $front_image  = '';
                                    $back_image  = '';
                                    $other_image  = '';
                                    if($doclist->front_image){
                                        $front_image  =  $baseUrl."/public/".$doclist->front_image;
                                    }
                                    if($doclist->back_image){
                                        $back_image  =  $baseUrl."/public/".$doclist->back_image;
                                    }
                                    if($doclist->other_image){
                                        $other_image  =  $baseUrl."/public/".$doclist->other_image;
                                    }
                                    $customerDocArr[] = ['id' => (string)$doclist->id, 'title' => $doclist->title, 'front_image' => $front_image, 'back_image' => $back_image , 'other_image' => $other_image]; //'planning_isprogress' => 
                                }
                                if($customerDeliveyinfo){
                                    $secondary_number = $customerDeliveyinfo->secondary_number;
                                    $parents_number = $customerDeliveyinfo->parents_number;
                                    $deliverytime_meeterreading = $customerDeliveyinfo->meter_reading;
                                }else{
                                    $secondary_number = '';
                                    $parents_number = '';
                                    $deliverytime_meeterreading = '';
                                }

                                if($customerReturninfo){
                                    $extra_charges = "".$customerReturninfo->extra_charges;
                                    $damage_charges = "".$customerReturninfo->damage_charges;
                                    $returntime_meeterreading = $customerReturninfo->meter_reading;
                                }else{
                                    $extra_charges = '';
                                    $damage_charges = '';
                                    $returntime_meeterreading = '';
                                }
                                $vehicle_number = $vlist->vehicle;
                                
                                $booking_hours = "".$vlist->booking_hours;
                                $additional_hours = "".$vlist->additional_hours;
                                $allowed_km = "".$vlist->allowed_km;
                                $additional_amount = "".$vlist->additional_amount;
                                $receive_amount = "".$vlist->receive_amount;
                                $total_amount = $vlist->total_amount;
                                $pick_up = date("d M Y",strtotime($vlist->pick_up));
                                $pick_up_time = $vlist->pick_up_time;

                                $receive_date = date("d M Y",strtotime($vlist->receive_date));
                                $return_time = date("H:i:s",strtotime($vlist->return_time));

                                $before_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'Before Ride')->orderBy('id', 'DESC')->get();
                                $booked_vehicle_before_list = array();
                                foreach($before_ride_img as $beforeimg)
                                {
                                    if($beforeimg->image){
                                        $beforeimgurl = $baseUrl."/public/".$beforeimg->image; 
                                        
                                        $booked_vehicle_before_list[] = array('title' => $beforeimg->title, 'image' => $beforeimgurl); 
                                    }
                                } 

                                $after_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'After Ride')->orderBy('id', 'DESC')->get();
                                $booked_vehicle_after_list = array();
                                foreach($after_ride_img as $afterimg)
                                {
                                    if($afterimg->image){
                                        $afterimgurl = $baseUrl."/public/".$afterimg->image; 
                                        
                                        $booked_vehicle_after_list[] = array('title' => $afterimg->title, 'image' => $afterimgurl); 
                                    }
                                }  
                                
                                $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'customer_phone' =>$customer_phone, 'secondary_number' =>$secondary_number, 'parents_number' =>$parents_number, 'customer_email' =>$customer_email, 'customer_address' =>$customer_address, 'customer_image' => $customer_image, 'customer_doc' =>$customerDocArr, 'vehicle_number' => $vehicle_number, 'booking_hours' => $booking_hours, 'additional_hours' => $additional_hours, 'allowed_km' => $allowed_km, 'additional_amount' => $additional_amount, 'extra_charges' => $extra_charges, 'damage_charges' => $damage_charges,  'receive_amount' => $receive_amount, 'penalty_amount' => $penalty_amount, 'total_amount' => $total_amount, 'deliverytime_bike_meeter_reading' => "".$deliverytime_meeterreading, 'returntime_bike_meeter_reading' => "".$returntime_meeterreading, 'pick_up_date' => $pick_up, 'pick_up_time' => $pick_up_time, 'receive_date' => $receive_date, 'return_time' => $return_time,'booked_vehicle_before_images' => $booked_vehicle_before_list, 'booked_vehicle_after_images' => $booked_vehicle_after_list]; 
                             }
                        } 

                        
                        if($city_id > 0){
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        $status_code = $success = '1';
                        $message = 'Penalties Detail';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'center_name' => $station_name, 'penality_detail' => $v_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Penalty Vehicle not available right now';
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
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
                    //$booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->first();
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','receive_date','is_amount_receive','status','vehicle', 'is_expended', 'is_upgrade', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    
                   
                    if($booking){

                        $extendhistory = array();
                        $extendamount = 0;
                        if($booking->is_expended == 'yes'){
                            
                            $expand_booking = DB::table('booking_expended')->where('booking_id', $booking->id)->where('payment_status', 'success')->orderBy('id', 'DESC')->get();
                            if($expand_booking){
                                foreach($expand_booking as $extendbookdata)
                                {
                                    $extendhistory[] = array('expand_date' => $extendbookdata->expand_date, 'expand_time' => $extendbookdata->expand_time, 'expand_amount' => "".$extendbookdata->expand_amount, 'expand_km' => "".$extendbookdata->expand_km, 'booking_hours' => "".$extendbookdata->booking_hours);
                                     $extendamount += $extendbookdata->expand_amount;   
                                }   
                            }
                            
                           
                        }

                        $upgradeBikehistory = array();
                        $upgradeamount = 0;
                        if($booking->is_upgrade == 'yes'){
                            
                            $upgrade_booking = DB::table('booking_upgrade_bike')->where('booking_id', $booking->id)->where('payment_status', 'success')->orderBy('id', 'DESC')->get();
                            
                            if($upgrade_booking){
                                foreach($upgrade_booking as $upgradebookdata)
                                {
                                    $upgrade_vehicle_model = DB::table('vehicle_models')->where('id', $upgradebookdata->vehicle_model_id)->pluck('model')[0];
                                    $upgradeBikehistory[] = array('upgrade_vehicle_model' => $upgrade_vehicle_model, 'upgrade_amount' => "".$upgradebookdata->upgrade_amount, 'allowed_km' => "".$upgradebookdata->allowed_km);
                                    $upgradeamount += $upgradebookdata->upgrade_amount;  
                                }   
                            }
                            
                           
                        }
                       
                        
                        if($booking->is_upgrade == 'yes'){
                             $upgrade_vehicle_model_id = DB::table('booking_upgrade_bike')->where('booking_id', $booking->id)->where('payment_status', 'success')->orderBy('id', 'DESC')->pluck('vehicle_model_id')[0];
                             $vehicle_model = DB::table('vehicle_models')->where('id', $upgrade_vehicle_model_id)->pluck('model')[0];

                        }else{
                            $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];
                        }
                        $bike_options = array();
                        $customer_id = $booking->customer_id;
                        $customerImage = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->pluck('image')[0];
                        $selfi  = array();
                        if($customerImage){
                            $selfi_img  =  $baseUrl."/public/".$customerImage;
                            $selfi[] =  array("label" => "Selfi Image", "dataval"=> $selfi_img);

                        }
                        /* Customer Licence */
                        $custdoc = new CustomerDocuments();
                        $customerLicense = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'Driving License');
                        $licence_image  = array();
                        if($customerLicense){
                        
                            if($customerLicense->front_image){
                                $licence_image[] = array("label" => "Front Image", "dataval" => $baseUrl."/public/".$customerLicense->front_image);
                            }
                            if($customerLicense->back_image){
                                $licence_image[] = array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customerLicense->back_image);

                            }
                            if($customerLicense->other_image){
                                $licence_image[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customerLicense->other_image);

                            }
                        }

                        /* Customer Adhaar */
                        $customeradaar = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'ID Proof (Adhaar Card)');
                        
                        $adaarimage  = array();
                        if($customeradaar){
                            if($customeradaar->front_image){
                                
                                 $adaarimage[] = array("label" => "Front Image", "dataval"=> $baseUrl."/public/".$customeradaar->front_image);

                                
                            }
                            if($customeradaar->back_image){
                                $adaarimage[] =  array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customeradaar->back_image);

                                
                            }
                            if($customeradaar->other_image){
                                
                                $adaarimage[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customeradaar->other_image);

                            }
                        }
                        
                        

                         $vehicleimage = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('vehicle_image')[0];

                        
                        $vehicle_image  = "";
                        if($vehicleimage){
                            $vehicle_image  =  $baseUrl."/public/".$vehicleimage;
                        
                        }
                         $vehicle_status = $booking->status;
                         $vstatus ='';
                         if($vehicle_status == 'In'){
                                $expected_drop = $booking->expected_drop;
                                $expected_drop_time = $booking->expected_drop_time;
                                $expected_droptimestamp = strtotime($expected_drop.' '.$expected_drop_time);
                                $today  = date('Y-m-d H:i:s');
                                $currentTimestamp  = strtotime($today);
                                if($expected_droptimestamp >= $currentTimestamp){
                                    $vstatus = 'Prepare To Delivery';
                                }else{
                                    $vstatus = 'Book New Order';
                                }
                            }

                            if($vehicle_status == 'Out'){
                                $vstatus = 'Customer Return at Center';
                            }

                            if($booking->receive_date != '' && $booking->is_amount_receive === 1){
                                $vstatus = 'Completed';
                            }

                        $before_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'Before Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_before_list = array();
                        foreach($before_ride_img as $beforeimg)
                        {
                            if($beforeimg->image){
                                $beforeimgurl = $baseUrl."/public/".$beforeimg->image; 
                                
                                $booked_vehicle_before_list[] = array('title' => $beforeimg->title, 'image' => $beforeimgurl); 
                            }
                        } 

                        $after_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'After Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_after_list = array();
                        foreach($after_ride_img as $afterimg)
                        {
                            if($afterimg->image){
                                $afterimgurl = $baseUrl."/public/".$afterimg->image; 
                                
                                $booked_vehicle_after_list[] = array('title' => $afterimg->title, 'image' => $afterimgurl); 
                            }
                        }    
                         $customerlog  = array();
                         $vehicleTracking  = array();
                         $customerLogs[] = array('heading' => 'Customer Logs', 'content' => $customerlog);   
   
                         $selfiarr[] = array('heading' => 'Customer Selfi', 'content' => $selfi);

                         $licensDocearr[] = array('heading' => 'Customer License', 'content' => $licence_image);

                        $adhaarDocearr[] = array('heading' => 'Customer Adhaar ID', 'content' => $adaarimage);

                        $trackVehicle[] = array('heading' => 'Track Vehicles', 'content' => $vehicleTracking);

                        
                        $bike_options[] = array('optionTitle' => 'Show Logs', 'optionData' => $customerLogs);
                        
                        $bike_options[] = array('optionTitle' => 'View License', 'optionData' => $licensDocearr);

                        $bike_options[] = array('optionTitle' => 'View Adhaar', 'optionData' => $adhaarDocearr);

                        $bike_options[] = array('optionTitle' => 'Track Vehicle', 'optionData' => $trackVehicle);

                        $bike_options[] = array('optionTitle' => 'Selfi', 'optionData' => $selfiarr);

                        /* due penalties */
                        $booked_vehicleList = DB::table('vehicle_registers')->select('id','customer_id','additional_amount','receive_amount')->where('customer_id',$customer_id)->where('booking_status','1')->where('additional_amount', '>', 0)->where('is_amount_receive', '=', 1)->get();
                        $customer_penalty = 0;
                        if(count($booked_vehicleList) >0){
                            foreach($booked_vehicleList as $vlist)
                            {
                                if($vlist->receive_amount < $vlist->additional_amount){
                                    $penalty_amount = "".($vlist->additional_amount-$vlist->receive_amount);
                                    $customer_penalty += $penalty_amount;
                                }
                            }
                        }        
                        /* End */
                        $total_amount = $booking->total_amount+$customer_penalty+$extendamount+$upgradeamount;
                        $status_code = '1';
                        $message = 'Booking Details';

                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'Type' => $vstatus, 'booking_no' => $booking->booking_no, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'vehicle_image' => $vehicle_image, 'vehicle_number' => $booking->vehicle, 'employee_name' => $employee->name, 'customer_id' => $customer_id,'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'customer_penalty_amount' => "".$customer_penalty, 'total_amount' => "".$total_amount, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)), 'bike_options' => $bike_options,  'vehicle_image_before_ride' => $booked_vehicle_before_list, 'vehicle_image_after_ride' => $booked_vehicle_after_list, 'is_expended' => $booking->is_expended, 'extendhistory' => $extendhistory, 'is_upgrade' => $booking->is_upgrade,'upgradeBikehistory' => $upgradeBikehistory );
                    }else{
                         $status_code = '0';
                        $message = 'No booking data found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                    }
                }else{
                    $status_code = $success = '0';
                    $message = 'Employee not valid';
                    $json = array('status_codemployee_ide' => $status_code, 'message' => $message, 'employee_id' => $employee_id);

                }
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getTraceAsString();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function track_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $vehicle_number = $request->vehicle_number;
            $error = "";
            if($vehicle_number == ""){
                $error = "Please enter vehicle number";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','vehicle_model_id','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('vehicle', $vehicle_number)->orderBy('id', 'DESC')->first();
                        $attendance_date = date('Y-m-d');
                        $time = date('H:i:s');
                        if($booking){
                            
                            $vehicle_regno = $vehicle_number;

                            $curl = curl_init();

                            curl_setopt_array($curl, array(
                              CURLOPT_URL => 'http://13.127.228.11/webservice?token=getLiveData&vehicle_no='.$vehicle_regno.'&format=json',
                              CURLOPT_RETURNTRANSFER => true,
                              CURLOPT_ENCODING => '',
                              CURLOPT_MAXREDIRS => 10,
                              CURLOPT_TIMEOUT => 0,
                              CURLOPT_FOLLOWLOCATION => true,
                              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                              CURLOPT_CUSTOMREQUEST => 'GET',
                              CURLOPT_HTTPHEADER => array(
                                'Cookie: JSESSIONID=5139FFE98C395187DB84A00C18508065'
                              ),
                            ));

                            $response = curl_exec($curl);
                            $trackdetail = json_decode($response);
                            curl_close($curl);
                            //print_r($trackdetail);
                            if(isset($trackdetail->root->error)){
                                $vehicle_No = '';
                                $vehicle_Name = '';
                                $vehicletype = '';
                                $imeino = '';
                                $deviceModel = '';
                                $location = '';
                                $datetime = '';
                                $latitude = '';
                                $longitude = '';
                                $status = '';
                                $speed = '';
                                $gps = '';
                                $ignission = '';
                                $power = '';
                                $fuel = '';
                                $odometer = '';
                            }else{ 
                                $vehicle_No = $trackdetail->root->VehicleData[0]->Vehicle_No;
                                $vehicle_Name = $trackdetail->root->VehicleData[0]->Vehicle_Name;
                                $vehicletype = $trackdetail->root->VehicleData[0]->Vehicletype;
                                $imeino = $trackdetail->root->VehicleData[0]->Imeino;
                                $deviceModel = $trackdetail->root->VehicleData[0]->DeviceModel;
                                $location = $trackdetail->root->VehicleData[0]->Location;
                                $datetime = $trackdetail->root->VehicleData[0]->Datetime;
                                $latitude = $trackdetail->root->VehicleData[0]->Latitude;
                                $longitude = $trackdetail->root->VehicleData[0]->Longitude;
                                $status = $trackdetail->root->VehicleData[0]->Status;
                                $speed = $trackdetail->root->VehicleData[0]->Speed;
                                $gps = $trackdetail->root->VehicleData[0]->GPS;
                                $ignission = $trackdetail->root->VehicleData[0]->IGN;
                                $power = $trackdetail->root->VehicleData[0]->Power;
                                $fuel = $trackdetail->root->VehicleData[0]->Fuel;
                                $odometer = $trackdetail->root->VehicleData[0]->Odometer;
                            }    


                            $message = 'Vehicle Tracking Detail';
                            $status_code = '1';
                            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id,"vehicle_No" => $vehicle_No, "vehicle_Name" => $vehicle_Name, "vehicletype" => $vehicletype, "imeino" => $imeino, "deviceModel" => $deviceModel,"location" => $location,"datetime" => $datetime,"latitude" => $latitude,"longitude" => $longitude,"status" => $status,"speed" => $speed,"gps" => $gps,"ignission" => $ignission ,"power" => $power,"fuel" => $fuel,"odometer" => $odometer);
                        }else{

                            $status_code = $success = '0';
                            $message = 'Booking vehicle Data not valid';
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }
    
    public function prepareToDelivery(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $baseUrl = URL::to("/");
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $booking_otp = $request->booking_no;
            $error = "";
            if($booking_otp == ""){
                $error = "Please enter booking no as send you at booking enquiry";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    //$booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','booking_hours','allowed_km','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('payment_status', 'success')->where('status', 'In')->where('register_otp', $booking_otp)->orderBy('id', 'DESC')->first();
                    
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','booking_hours','allowed_km','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('status', 'In')->where('register_otp', $booking_otp)->orderBy('id', 'DESC')->first();
                    
                   
                    if($booking){
                        $bike_options = array();
                        $customer_id = $booking->customer_id;
                        $customer_address = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->pluck('address')[0];

                        
                        /* Customer Licence */
                        $custdoc = new CustomerDocuments();
                        $customerLicense = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'Driving License');

                        $licence_image  = array();
                        if($customerLicense){
                            if($customerLicense->front_image){
                                $licence_image[] = array("label" => "Front Image", "dataval" => $baseUrl."/public/".$customerLicense->front_image);
                            }
                            if($customerLicense->back_image){
                                $licence_image[] = array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customerLicense->back_image);

                            }
                            if($customerLicense->other_image){
                                $licence_image[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customerLicense->other_image);

                            }
                        }

                        /* Customer Adhaar */
                        $customeradaar = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'ID Proof (Adhaar Card)');
                        
                        $adaarimage  = array();
                        if($customeradaar){
                            if($customeradaar->front_image){
                                
                                 $adaarimage[] = array("label" => "Front Image", "dataval"=> $baseUrl."/public/".$customeradaar->front_image);

                                
                            }
                            if($customeradaar->back_image){
                                $adaarimage[] =  array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customeradaar->back_image);

                                
                            }
                            if($customeradaar->other_image){
                                
                                $adaarimage[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customeradaar->other_image);

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
  

                        $before_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'Before Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_before_list = array();
                        foreach($before_ride_img as $beforeimg)
                        {
                            if($beforeimg->image){
                                $beforeimgurl = $baseUrl."/public/".$beforeimg->image; 
                                
                                $booked_vehicle_before_list[] = array('title' => $beforeimg->title, 'image' => $beforeimgurl); 
                            }
                        } 
                        
                        $usedVehList = array('01');
                        $bookedvehicle = \DB::table('vehicle_registers')->where('user_id', $employee_id)->where('status', 'Out')->where('station', $booking->station)->distinct()->select('vehicle')->get();
                        if($bookedvehicle){
                            foreach ($bookedvehicle as $usedvehicle) {
                                if($usedvehicle->vehicle){
                                    $usedVehList[] = $usedvehicle->vehicle;
                                }
                            }
                        }
                        
                        $vehiclelist = DB::table('vehicles as v')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->join('stations as s', 's.id', '=', 'sv.station_id')->select('v.id','v.vehicle_number')->whereNotIn('v.vehicle_number', $usedVehList)->where('v.vehicle_model', $booking->vehicle_model_id)->where('s.employee_id', $employee_id)->orderBy('v.id', 'DESC')->get();
                        $vehicle_list = array();
                        foreach($vehiclelist as $bikelist)
                        {
                            if($bikelist->vehicle_number){
                                $vehicle_list[] = array('vehicle_number' => $bikelist->vehicle_number); 
                            }
                        } 
                       
                        
                        $status_code = '1';
                        if($vehicle_status == 'In'){
                            $message = 'Prepare To Delivery Detail';
                        }

                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'Type' => $vstatus, 'booking_no' => $booking->booking_no, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'customer_id' => $customer_id, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'customer_address' => $customer_address, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'allowed_helmet' => '2', 'check_side_mirrors' => 'f', 'check_key' => 'f', 'fuel_reading' => '', 'meter_reading' => '', 'secondary_number' => '', 'parents_number' => '', 'booking_hours' => "".$booking->booking_hours, 'allowed_km' => "".$booking->allowed_km, 'total_amount' => "".$booking->total_amount, 'licence_image' => $licence_image, 'adaarimage' => $adaarimage, 'vehicle_number' => $vehicle_list, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)),  'vehicle_image_before_ride' => $booked_vehicle_before_list );
                    }else{
                         $status_code = '0';
                        $message = 'No booking data found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function add_vehicle_booking_image(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $baseUrl = URL::to("/");
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $customer_id = $request->customer_id;
            $vehicle_image_type = $request->image_type;
            $title = $request->title;
            $vehicle_image = $request->vehicle_image;
           
            $error = "";
            if($vehicle_image == ""){
                $error = "Please add vehicle Image";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

           if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        
                        if($vehicle_image != ''){
                            $image_parts = explode(";base64,", $vehicle_image);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];

                            $vehicleImage = rand(10000, 99999).'-'.time().'.'.$image_type;
                            $destinationPath = public_path('/uploads/vehicle_booking_image/').$vehicleImage;

                            $data = base64_decode($image_parts[1]);
                           // $data = $image_parts[1];
                            file_put_contents($destinationPath, $data);
                        }
                        $status = 'Live';
                        $vehicle_booking_image_id = DB::table('booked_vehicle_images')->insert([
                            'booking_id' => $booking_id,
                            'customer_id' => $customer_id,
                            'title' => $title,
                            'image' => 'uploads/vehicle_booking_image/'.$vehicleImage,
                            'image_type' => $vehicle_image_type,
                            'status' => $status,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $vehicle_imageurl = $baseUrl."/public/uploads/vehicle_booking_image/".$vehicleImage; 
                        
                        $status_code = $success = '1';
                        $message = 'Vihicle Image Added Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'title' => $title, 'vehicle_image_type' => $vehicle_image_type, "vehicle_image" => $vehicle_imageurl, 'employee_id' => $employee_id);
                    
                    
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

    public function deliver_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $vehicle_number = $request->vehicle_number;
            $allowed_helmets = $request->allowed_helmets;
            $check_side_mirrors = $request->check_side_mirrors;
            $check_key = $request->check_key;
            $fuel_reading = $request->fuel_reading;
            $meter_reading = $request->meter_reading;
            $secondary_number = $request->secondary_number;
            $parents_number = $request->parents_number;
            $error = "";
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        
                        if($booking_id){
                            $chkPreparebooking = DB::table('vehicle_prepare_to_delivery')->where('booking_id', $booking_id)->first();
                   
                            if($chkPreparebooking){
                                $bookingPrepare_id = $chkPreparebooking->id;
                                $updateserviceRequest_id = DB::table('vehicle_prepare_to_delivery')->where('id', '=', $bookingPrepare_id)->update([
                                    'allowed_helmets' => $allowed_helmets,
                                    'check_side_mirrors' => $check_side_mirrors,
                                    'check_key' => $check_key,
                                    'fuel_reading' => $fuel_reading,
                                    'meter_reading' => $meter_reading,
                                    'secondary_number' => $secondary_number,
                                    'parents_number' => $parents_number,
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                            }else{
                                $prepare_delivery = DB::table('vehicle_prepare_to_delivery')->insert([
                                    'booking_id' => $booking_id,
                                    'allowed_helmets' => $allowed_helmets,
                                    'check_side_mirrors' => $check_side_mirrors,
                                    'check_key' => $check_key,
                                    'fuel_reading' => $fuel_reading,
                                    'meter_reading' => $meter_reading,
                                    'secondary_number' => $secondary_number,
                                    'parents_number' => $parents_number,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]);
                            }

                            /* Assigne vehicle number */
                            $vehicleBooking =  DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['vehicle' => $vehicle_number, 'status' => 'Out', 'updated_at' => $date]);

                           
                        }
                        
                        $status_code = $success = '1';
                        $message = "Vehicle delivery done successfully";
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

    public function customerReturnVehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $baseUrl = URL::to("/");
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($booking_no == ""){
                $error = "Please enter booking no as send you at booking enquiry";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    //$booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','booking_hours','allowed_km','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('payment_status', 'success')->where('status', 'In')->orderBy('id', 'DESC')->first();
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','booking_hours','allowed_km','total_amount','receive_date','is_amount_receive','status','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('status', 'In')->orderBy('id', 'DESC')->first();
                    
                   
                    if($booking){
                        $bike_options = array();
                        $customer_id = $booking->customer_id;
                        $customer_address = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->pluck('address')[0];

                        
                        /* Customer Licence */
                        $custdoc = new CustomerDocuments();
                        $customerLicense = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'Driving License');

                        $licence_image  = array();
                        if($customerLicense){
                            if($customerLicense->front_image){
                                $licence_image[] = array("label" => "Front Image", "dataval" => $baseUrl."/public/".$customerLicense->front_image);
                            }
                            if($customerLicense->back_image){
                                $licence_image[] = array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customerLicense->back_image);

                            }
                            if($customerLicense->other_image){
                                $licence_image[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customerLicense->other_image);

                            }
                        }

                        /* Customer Adhaar */
                        $customeradaar = $custdoc->getCustomerDocumentsByCustomerid($customer_id,'ID Proof (Adhaar Card)');
                        
                        $adaarimage  = array();
                        if($customeradaar){
                            if($customeradaar->front_image){
                                
                                 $adaarimage[] = array("label" => "Front Image", "dataval"=> $baseUrl."/public/".$customeradaar->front_image);

                                
                            }
                            if($customeradaar->back_image){
                                $adaarimage[] =  array("label" => "Back Image", "dataval"=> $baseUrl."/public/".$customeradaar->back_image);

                                
                            }
                            if($customeradaar->other_image){
                                
                                $adaarimage[] = array("label" => "Other Image", "dataval"=> $baseUrl."/public/".$customeradaar->other_image);

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
  

                        $before_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'Before Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_before_list = array();
                        foreach($before_ride_img as $beforeimg)
                        {
                            if($beforeimg->image){
                                $beforeimgurl = $baseUrl."/public/".$beforeimg->image; 
                                
                                $booked_vehicle_before_list[] = array('title' => $beforeimg->title, 'image' => $beforeimgurl); 
                            }
                        } 

                        $after_ride_img = DB::table('booked_vehicle_images')->where('booking_id', $booking_id)->where('image_type', 'After Ride')->orderBy('id', 'DESC')->get();
                        $booked_vehicle_after_list = array();
                        foreach($after_ride_img as $afterimg)
                        {
                            if($afterimg->image){
                                $afterimgurl = $baseUrl."/public/".$afterimg->image; 
                                
                                $booked_vehicle_after_list[] = array('title' => $afterimg->title, 'image' => $afterimgurl); 
                            }
                        } 
                        
                        $usedVehList = array('01');
                        $bookedvehicle = \DB::table('vehicle_registers')->where('user_id', $employee_id)->where('status', 'Out')->where('station', $booking->station)->distinct()->select('vehicle')->get();
                        if($bookedvehicle){
                            foreach ($bookedvehicle as $usedvehicle) {
                                if($usedvehicle->vehicle){
                                    $usedVehList[] = $usedvehicle->vehicle;
                                }
                            }
                        }
                        
                        $vehiclelist = DB::table('vehicles as v')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->join('stations as s', 's.id', '=', 'sv.station_id')->select('v.id','v.vehicle_number')->whereNotIn('v.vehicle_number', $usedVehList)->where('v.vehicle_model', $booking->vehicle_model_id)->where('s.employee_id', $employee_id)->orderBy('v.id', 'DESC')->get();
                        $vehicle_list = array();
                        foreach($vehiclelist as $bikelist)
                        {
                            if($bikelist->vehicle_number){
                                $vehicle_list[] = array('vehicle_number' => $bikelist->vehicle_number); 
                            }
                        } 
                       
                        
                        $status_code = '1';
                        if($vehicle_status == 'In'){
                            $message = 'Return To Station Detail';
                        }

                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'Type' => $vstatus, 'booking_no' => $booking->booking_no, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'customer_id' => $customer_id, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'customer_address' => $customer_address, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'allowed_helmet' => '2', 'check_side_mirrors' => 'f', 'check_key' => 'f', 'fuel_reading' => '', 'meter_reading' => '', 'secondary_number' => '', 'parents_number' => '', 'booking_hours' => "".$booking->booking_hours, 'allowed_km' => "".$booking->allowed_km, 'total_amount' => "".$booking->total_amount, 'licence_image' => $licence_image, 'adaarimage' => $adaarimage, 'vehicle_number' => $vehicle_list, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)),  'vehicle_image_before_ride' => $booked_vehicle_before_list, 'booked_vehicle_after_list' => $booked_vehicle_after_list );
                    }else{
                         $status_code = '0';
                        $message = 'No booking data found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function return_vehicle(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $recieve_helmets = $request->recieve_helmets;
            $check_side_mirrors = $request->check_side_mirrors;
            $check_key = $request->check_key;
            $fuel_reading = $request->fuel_reading;
            $meter_reading = $request->meter_reading;

            $damage_charges = $request->damage_charges;
            $extra_charges = $request->extra_charges;
            $receive_amount = $request->receive_amount;
            $error = "";
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                    if($booking_id){
                        $chkreturnbooking = DB::table('vehicle_return_to_station')->where('booking_id', $booking_id)->first();
                   
                        if($chkreturnbooking){
                            $bookingreturn_id = $chkreturnbooking->id;
                            $updateserviceRequest_id = DB::table('vehicle_return_to_station')->where('id', '=', $bookingreturn_id)->update([
                                'recieve_helmets' => $recieve_helmets,
                                'check_side_mirrors' => $check_side_mirrors,
                                'check_key' => $check_key,
                                'fuel_reading' => $fuel_reading,
                                'meter_reading' => $meter_reading,
                                'damage_charges' => $damage_charges,
                                'extra_charges' => $extra_charges,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);

                         }else{
                            $prepare_delivery = DB::table('vehicle_return_to_station')->insert([
                                'booking_id' => $booking_id,
                                'recieve_helmets' => $recieve_helmets,
                                'check_side_mirrors' => $check_side_mirrors,
                                'check_key' => $check_key,
                                'fuel_reading' => $fuel_reading,
                                'meter_reading' => $meter_reading,
                                'damage_charges' => $damage_charges,
                                'extra_charges' => $extra_charges,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        }    

                        
                        $return_time = date('Y-m-d H:i:s');
                        $additional_hours = 0;
                        $additional_amount = 0;
                        if($damage_charges > 0 || $extra_charges > 0){
                            $additional_amount = $damage_charges+$extra_charges;
                        }
                        
                        $is_amount_receive = 1;
                        $due_penalty = 'no';
                        if($receive_amount < $additional_amount){
                            $due_penalty = 'yes';
                        }
                        $receive_date = date('Y-m-d H:i:s');
                        $vehicleBooking =  DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['return_time' => $return_time, 'additional_hours' => $additional_hours, 'additional_amount' => $additional_amount, 'receive_amount' => $receive_amount, 'due_penalty' => $due_penalty, 'is_amount_receive' => $is_amount_receive, 'receive_date' => $receive_date, 'status' => 'In', 'updated_at' => $date]);

                        /* Check vehicle number for service */
                         $bookinginfo = DB::table('vehicle_registers')->select('id','vehicle')->where('id', $booking_id)->first();
                         if($bookinginfo){
                            $vehicle_number = $bookinginfo->vehicle;
                             $checkserviceReminder = DB::table('vehicle_service_reminder')->select('id','service_complete_date')->where('vehicle_number', $vehicle_number)->where('service_status', 'done')->first();

                             $no_ofride = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->where('status', 'In')->where('payment_status', 'success')->where('is_amount_receive', '1');
                             if($checkserviceReminder){
                                $service_complete_date = $checkserviceReminder->service_complete_date;    
                                $no_ofride = $no_ofride->wheredate('pick_up', '>' ,$service_complete_date);    
                            }
                            $no_ofride = $no_ofride->count();
                              
                            $totalKm = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->where('status', 'In')->where('payment_status', 'success')->where('is_amount_receive', '1');
                            if($checkserviceReminder){
                                $service_complete_date = $checkserviceReminder->service_complete_date;    
                                $totalKm = $totalKm->wheredate('pick_up', '>' ,$service_complete_date);    
                            }    
                            $totalKm = $totalKm->sum('allowed_km');
                              if($no_ofride >= 60){
                                    $serviceReminder = DB::table('vehicle_service_reminder')->select('id')->where('vehicle_number', $vehicle_number)->where('service_status', 'pending')->count();
                                    if($serviceReminder == 0){
                                         $vehicle_service_reminder = DB::table('vehicle_service_reminder')->insert([
                                            'vehicle_number' => $vehicle_number,
                                            'service_type' => 'ride',
                                            'service_status' => 'pending',
                                            'service_complete_date' => '',
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);   
                                    }
                              }

                                if($totalKm >= 5000 ){
                                    $serviceReminder = DB::table('vehicle_service_reminder')->select('id')->where('vehicle_number', $vehicle_number)->where('service_status', 'pending')->count();
                                    if($serviceReminder == 0){
                                         $vehicle_service_reminder = DB::table('vehicle_service_reminder')->insert([
                                            'vehicle_number' => $vehicle_number,
                                            'service_type' => 'km',
                                            'service_status' => 'pending',
                                            'service_complete_date' => '',
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);   
                                    }
                                }
                         }     
                        /* End */
                    }
                    
                    $status_code = $success = '1';
                    $message = "Customer return vehicle successfully";
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

    public function empoyee_today_attendance(Request $request)
    {
        try 
        {
            $json = $userData = array();

            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                
            if($employee){ 
                 $start_date   = date('Y-m-d');
                //$end_date   = date('Y-m-d 21:00:00');
               $employeeAttendance = DB::table('employee_attendance')->where('employee_id', $employee_id)->wheredate('attendance_date',' = ',$start_date)->first();
                
                
                $check_inTime = '';
                $check_outTime = ''; 

                if($employeeAttendance){
                    $check_inTime = $employeeAttendance->check_in;
                    $check_outTime = $employeeAttendance->check_out; 
                }

                $status_code = $success = '1';
                $message = 'Employee Today Attendance';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'today_date' => date('Y-m-d H:i:s'), 'check_inTime' => $check_inTime, 'check_outTime' => $check_outTime);
            } else{
                $status_code = $success = '0';
                $message = 'Employee not exists';
                
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

    
    public function noticeboard_list(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
             $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

            if($employee){
                $notificationExists = DB::table('noticeboards')->where('status', 'Live')->orderBy('id', 'DESC')->count();
                $notify_List = array();
                if($notificationExists > 0){
                    $notifyList = DB::table('noticeboards')->select('id','title','description','created_at')->where('status', 'Live')->orderBy('id', 'DESC')->get();

                    
                    foreach($notifyList as $notifylist)
                    {
                        $notification_type = 'Employee';
                        
                        $notify_List[] = array('id' => "".$notifylist->id, 'notification_title' => $notifylist->title,'notification_content' => "".$notifylist->description, 'notification_type' => $notification_type, 'date' => date('d-m-Y H:i:s', strtotime($notifylist->created_at))); 
                       
                    } 

                    //print_r($odr_List);
                    //exit;
                    $status_code = '1';
                    $message = 'Noticeboard List';
                    $json = array('status_code' => $status_code,  'message' => $message, 'notify_List' => $notify_List);
                }else{
                     $status_code = '0';
                    $message = 'No notification found.';
                    $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                }
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
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

    public function statics_info(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
             $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

            if($employee){
                $start_date = date("Y-04-01 00:00:01");
                $end_date = date("Y-m-30 23:55:01");
                $totalCompleted = DB::table('vehicle_registers')->where('user_id', $employee_id)->where('is_amount_receive', 1)->wheredate('created_at',' > ',$start_date)->wheredate('created_at',' <= ',$end_date)->orderBy('id', 'DESC')->count();
                
                $cashCollected = DB::table('vehicle_registers')->where('user_id', $employee_id)->where('is_amount_receive', 1)->wheredate('created_at',' > ',$start_date)->wheredate('created_at',' <= ',$end_date)->sum('total_amount');

                $totalPenalties = DB::table('vehicle_registers')->where('user_id', $employee_id)->where('is_amount_receive', 1)->wheredate('created_at',' > ',$start_date)->wheredate('created_at',' <= ',$end_date)->sum('additional_amount');

                $totalCustomer = DB::table('vehicle_registers')->where('user_id', $employee_id)->where('is_amount_receive', 1)->wheredate('created_at',' > ',$start_date)->wheredate('created_at',' <= ',$end_date)->distinct()->count('customer_id');
               
                
                $status_code = '1';
                $message = 'Statics Data';
                $json = array('status_code' => $status_code,  'message' => $message, 'total_orders' => $totalCompleted, 'total_cash' => '₹ '.$cashCollected, 'total_penalty' => '₹ '.$totalPenalties, 'total_customer' => $totalCustomer);
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
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

    public function service_fleet_on_ride(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $baseUrl = URL::to("/");
             $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

            if($employee){
                //$booked_vehicleList = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','station','vehicle_model_id','vehicle' )->where('user_id', $employee_id)->where('payment_status', 'success')->where('booking_status','1')->where('status','out')->where('is_amount_receive','0')->orderBy('id', 'DESC')->get();
                $booked_vehicleList = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','station','vehicle_model_id','vehicle' )->where('user_id', $employee_id)->where('booking_status','1')->where('status','out')->where('is_amount_receive','0')->orderBy('id', 'DESC')->get();
                if(count($booked_vehicleList) >0){
                    $v_list = array();
                    foreach($booked_vehicleList as $vlist)
                    {
                        $model_id = $vlist->vehicle_model_id;
                        
                        $vehicleModel = DB::table('vehicle_models')->where('id', $model_id)->pluck('model')[0];

                        $vehicle_image = DB::table('vehicle_models')->where('id', $model_id)->pluck('vehicle_image')[0];
                         $bike_image = '';
                         if($vehicle_image){
                            $bike_image = $baseUrl."/public/".$vehicle_image; 
                         }
                        /* Customer info from prepare to delivery */
                        $bookingid = $vlist->id;
                        
                        $vehicle_model = $vehicleModel;
                        $booking_no = $vlist->booking_no;
                        $station_name = $vlist->station;
                        $customer_name = $vlist->customer_name;
                        $customer_phone = $vlist->phone;
                        $vehicle_number = $vlist->vehicle;
                        $status = 'Green';
                        
                        $v_list[] = ['booking_id' => (string)$vlist->id, 'station_name' =>$station_name, 'vehicle_model' =>$vehicle_model, 'booking_no' =>$booking_no, 'customer_name' =>$customer_name, 'customer_phone' =>$customer_phone, 'vehicle_number' => $vehicle_number, 'vehicle_image' => $bike_image, 'status_color' => $status]; 
                    }

                    
                    
                
                
                    $status_code = '1';
                    $message = 'On Ride Fleets';
                    $json = array('status_code' => $status_code,  'message' => $message, 'fleet_details' => $v_list);
                }else{
                        $status_code = '0';
                        $message = 'No fleet found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                    }
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
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

    public function service_fleet_on_pickup_center(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $baseUrl = URL::to("/");
             $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

            if($employee){
                $employeeFleetExists = DB::table('stations as s')->join('station_has_vehicles as sv', 's.id', '=', 'sv.station_id')->join('vehicles as v', 'v.id', '=', 'sv.vehicle_id')->join('vehicle_models as vm', 'vm.id', '=', 'v.vehicle_model')->select('v.id','vm.model','v.vehicle_number', 'v.vehicle_model', 'vm.allowed_km_per_hour', 'vm.charges_per_hour', 'vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('v.status','Live')->where('s.employee_id', $employee_id)->orderBy('v.id', 'DESC')->get();
                    $fleet_List = array();
                    
                    if($employeeFleetExists){
                        foreach($employeeFleetExists as $rsfleet)
                        {
                           $vehicle_number = $rsfleet->vehicle_number;
                           $vehicleModel = $rsfleet->model;
                           $checkserviceReminder = DB::table('vehicle_service_reminder')->select('id','service_complete_date')->where('vehicle_number', $vehicle_number)->where('service_status', 'done')->first();

                             $no_ofride = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->where('status', 'In')->where('payment_status', 'success')->where('is_amount_receive', '1');
                             if($checkserviceReminder){
                                $service_complete_date = $checkserviceReminder->service_complete_date;    
                                $no_ofride = $no_ofride->wheredate('pick_up', '>' ,$service_complete_date);    
                            }
                            $no_ofride = $no_ofride->count();
                              
                            $totalKm = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->where('status', 'In')->where('payment_status', 'success')->where('is_amount_receive', '1');
                            if($checkserviceReminder){
                                $service_complete_date = $checkserviceReminder->service_complete_date;    
                                $totalKm = $totalKm->wheredate('pick_up', '>' ,$service_complete_date);    
                            }    
                            $totalKm = $totalKm->sum('allowed_km');
                          if($no_ofride >= 6 || $totalKm >= 5000){
                            $baseUrl = URL::to("/");
                            $vehicle_image  = "";
                            if($rsfleet->vehicle_image){
                                $vehicle_image  =  $baseUrl."/public/".$rsfleet->vehicle_image;
                            
                            }
                            
                            
                            $status = 'Yellow';
                            

                            if($no_ofride >= 60 || $totalKm >= 5000 ){
                                $status = 'Red';
                            }

                            $vehicle_status = DB::table('vehicle_registers')->where('vehicle', $vehicle_number)->pluck('status')[0];
                           
                            $fleet_List[] = array('id' => "".$rsfleet->id, 'vehicle_model' => $vehicleModel, 'vehicle_number' => $vehicle_number, 'vehicle_image' => $vehicle_image, 'no_ofride' => "".$no_ofride, 'total_km' => "".$totalKm, 'status_color' => $status); 
                           } 
                           
                        } 

                        
                        $status_code = '1';
                        $message = 'Our Fleet on Pickup Center';
                        $json = array('status_code' => $status_code,  'message' => $message, 'fleet_List' => $fleet_List);
                    }else{
                         $status_code = '0';
                        $message = 'No Fleet found.';
                        $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => $employee_id);
                    }
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
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

    public function save_service_fleet_request(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $vehicle_id = $request->vehicle_id;
            $oil_check = $request->oil_check;
            $engine_check = $request->engine_check;
            $filter_check = $request->filter_check;
            $tyre_check = $request->tyre_check;
            $description = $request->description;
            $approx_amount = $request->approx_amount;
            //$approx_amount = '500';
            $status = 'Pending';
            $error = "";
            if($vehicle_id == ""){
                $error = "Please enter vehicle";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

           
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){

                    $employeeServiceFleet = DB::table('employee_service_fleet_request')->where('employee_id', $employee_id)->where('vehicle_id', $vehicle_id)->first();
                   
                    if($employeeServiceFleet){
                        $serviceRequest_id = $employeeServiceFleet->id;
                        $updateserviceRequest_id = DB::table('employee_service_fleet_request')->where('id', '=', $serviceRequest_id)->update([
                            'oil_check' => $oil_check,
                            'engine_check' => $engine_check,
                            'filter_check' => $filter_check,
                            'tyre_check' => $tyre_check,
                            'description' => $description,
                            'approx_amount' => $approx_amount,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $serviceRequest_status = $employeeServiceFleet->status;
                        if($serviceRequest_status == 'Approve'){
                            $currentDay = date('Y-m-d');
                            $fleetService = DB::table('vehicle_service')->where('vehicle_id', $vehicle_id)->wheredate('next_date', '>', $currentDay)->count();
                            if($fleetService == 0){

                                /* Update vehicle service reminder */
                                    $vehicle_number = DB::table('vehicles')->where('id', $vehicle_id)->pluck('vehicle_number')[0];

                                    $fleetServiceReminder = DB::table('vehicle_service_reminder')->where('vehicle_number', $vehicle_number)->where('service_status', 'pending')->first();
                                    if($fleetServiceReminder){
                                        $serviceReminderid = $fleetServiceReminder->id;
                                        $updateServiceReminder = DB::table('vehicle_service_reminder')->where('id', '=', $serviceReminderid)->update([
                                            'service_status' => 'done',
                                            'service_complete_date' => date('Y-m-d'),
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }    
                                /* End */
                                $service_by = '1';
                                $bike_km = '0';
                                $fleetstatus = 'done';
                                $insertservice_id = DB::table('vehicle_service')->insertGetId([
                                    'vehicle_id' => $vehicle_id,
                                    'description' => $description,
                                    'service_by' => $service_by,
                                    'service_amount' => $approx_amount,
                                    'bike_km' => $bike_km,
                                    'service_date' => date('Y-m-d H:i:s'),
                                    'next_date' => date('Y-m-d H:i:s',strtotime("+3 month")),
                                    'status' => $fleetstatus,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);

                                if($oil_check=='Yes'){
                                    $insertserviceType = DB::table('services_has_vehicles')->insertGetId([
                                        'vehicle_service_id' => $insertservice_id,
                                        'service_type_id' => '1'
                                    
                                    ]);
                                }
                                if($engine_check=='Yes'){
                                    $insertserviceType = DB::table('services_has_vehicles')->insertGetId([
                                        'vehicle_service_id' => $insertservice_id,
                                        'service_type_id' => '4'
                                    
                                    ]);
                                }
                                if($filter_check=='Yes'){
                                    $insertserviceType = DB::table('services_has_vehicles')->insertGetId([
                                        'vehicle_service_id' => $insertservice_id,
                                        'service_type_id' => '3'
                                    
                                    ]);
                                }
                                if($tyre_check=='Yes'){
                                    $insertserviceType = DB::table('services_has_vehicles')->insertGetId([
                                        'vehicle_service_id' => $insertservice_id,
                                        'service_type_id' => '2'
                                    
                                    ]);
                                }
                            }
                        }

                    }else{
                        $insertserviceRequest_id = DB::table('employee_service_fleet_request')->insert([
                            'employee_id' => $employee_id,
                            'vehicle_id' => $vehicle_id,
                            'oil_check' => $oil_check,
                            'engine_check' => $engine_check,
                            'filter_check' => $filter_check,
                            'tyre_check' => $tyre_check,
                            'description' => $description,
                            'status' => $status,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }    

                        
                        $status_code = $success = '1';
                        $message = 'Fleet Service Added Successfully';
                            
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

    public function service_fleet_request(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $vehicle_id = $request->vehicle_id;
            $baseUrl = URL::to("/");
             $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

            if($employee){
                    $employeeServiceFleet = DB::table('employee_service_fleet_request')->where('employee_id', $employee_id)->where('vehicle_id', $vehicle_id)->first();
                   
                    if($employeeServiceFleet){
                        
                        $status_code = '1';
                        $message = 'Service Fleet Request';
                        $json = array('status_code' => $status_code,  'message' => $message, 'vehicle_id' => $employeeServiceFleet->vehicle_id, 'oil_check' => $employeeServiceFleet->oil_check, 'engine_check' => $employeeServiceFleet->engine_check, 'filter_check' => $employeeServiceFleet->filter_check, 'tyre_check' => $employeeServiceFleet->tyre_check, 'description' => $employeeServiceFleet->description, 'approx_amount' => "".$employeeServiceFleet->approx_amount, 'status' => $employeeServiceFleet->status);
                    }else{
                        $status_code = '1';
                        $message = 'Service Fleet Request';
                        $json = array('status_code' => $status_code,  'message' => $message,  'vehicle_id' => $vehicle_id, 'oil_check' => 'No', 'engine_check' => 'No', 'filter_check' => 'No', 'tyre_check' => 'No', 'description' => '', 'approx_amount' => '');
                    }
            }else{
                $status_code = $success = '0';
                $message = 'Employee not valid';
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

     //upgrade vehicle filter Result
    public function upgrade_vehicle_filter(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter booking id ";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

                if($employee){ 
                    $booking = DB::table('vehicle_registers')->select('id','user_id','booking_no','customer_name','vehicle_model_id','station', 'booking_status','pick_up','pick_up_time','expected_drop','expected_drop_time')->where('user_id', $employee_id)->where('vehicle', '=', '')->where('status','In')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){
                        $station_name = $booking->station;
                        $vehicle_model_id = $booking->vehicle_model_id;
                        $station_id = DB::table('stations')->where('station_name', $station_name)->pluck('id')[0];
                        $vehicleList = DB::table('vehicles as v')->join('vehicle_models as vm', 'v.vehicle_model', '=', 'vm.id')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->select('vm.id','vm.model','vm.allowed_km_per_hour','vm.charges_per_hour','vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('v.status','Live')->groupBy('vm.id');

                            if($station_id > 0){
                                $vehicleList = $vehicleList->where('sv.station_id',$station_id);    
                            }

                            if($vehicle_model_id != ''){
                                $vehicleList = $vehicleList->where('vm.id','!=',$vehicle_model_id);    
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
                                    $from_date = $booking->pick_up." ".$booking->pick_up_time;
                                    $to_date = $booking->expected_drop." ".$booking->expected_drop_time;
                                    $pick_upDateTime = $from_date;
                                    $expected_dropDateTime = $to_date;
                                    $timestamp1 = strtotime($pick_upDateTime);
                                    $timestamp2 = strtotime($expected_dropDateTime);

                                    $hours = abs($timestamp2 - $timestamp1)/(60*60);
                                    $bikecharges = $charges_per_hour;
                                    $fleetFare = 0;
                                    $total_price = 0;
                                    if($hours > 0){
                                       
                                        $VehicleRegister = new VehicleRegister();
                                        $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                                        $total_price = $fleetFare+$insurance_charges_per_hour;
                                    }
                                    $day = floor($hours/24);
                                    $ride_type = 'regular';
                                    if($ride_type == 'long'){
                                        
                                       
                                        $charges = '₹ '.$total_price.' / '.$day.'d';

                                    }else{
                                        
                                        $charges = '₹ '.$total_price.' / Hr';
                                    }
                                    
                                    $user_id = DB::table('stations')->where('id', $station_id)->pluck('employee_id')[0];
                                    if($station_id != 0){
                            
                                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                                    }else{
                                        
                                        $station_name = "";
                                    } 

                                    /*Check Bike availability */
                                    $usedVehList = array('01');
                                    $bookedvehicle = \DB::table('vehicle_registers')->where('vehicle', '!=', '')->where('vehicle_model_id', $vlist->id)->where('user_id', $user_id)->where('status', 'Out')->where('station', $station_name)->distinct()->select('vehicle')->get();
                                    if($bookedvehicle){
                                        foreach ($bookedvehicle as $usedvehicle) {
                                            if($usedvehicle->vehicle){
                                                $usedVehList[] = $usedvehicle->vehicle;
                                            }
                                        }
                                    }

                                $vehiclelist = DB::table('vehicles as v')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->join('stations as s', 's.id', '=', 'sv.station_id')->select('v.id','v.vehicle_number')->whereNotIn('v.vehicle_number', $usedVehList)->where('v.vehicle_model', $vlist->id)->where('s.employee_id', $user_id)->orderBy('v.id', 'DESC')->get();
                                $vehicle_list = array();
                                foreach($vehiclelist as $bikelist)
                                {
                                    if($bikelist->vehicle_number){
                                        $vehicle_list[] = array('vehicle_number' => $bikelist->vehicle_number); 
                                    }
                                }

                                /* get latest return bike booking id */ 
                                $from_returndate  = date('Y-m-d', strtotime($from_date));
                                $from_returnTime  = date('H:i:s', strtotime($from_date));
                                $latestreturnbookedvehicle = DB::table('vehicle_registers')->where('vehicle', '!=', '')->where('vehicle_model_id', $vlist->id)->where('status', 'Out')->where('station', $station_name)->where('expected_drop', '>=', $from_returndate)->orderBy('expected_drop', 'ASC')->orderBy('expected_drop_time', 'ASC')->first();
                                 //echo count($vehicle_list)."-".$latestreturnbookedvehicle->id;
                                    $next_booking_time = '';
                                    if(count($vehicle_list) == 0){
                                        if($latestreturnbookedvehicle){
                                            $next_booking_time = date("d-m-Y",strtotime($latestreturnbookedvehicle->expected_drop))." ".$latestreturnbookedvehicle->expected_drop_time;
                                        }
                                    }

                                    $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'allowed_km_per_hour' =>$allowed_km_per_hour, 'charges_per_hour' =>$charges_per_hour, 'booking_hours' =>$hours, 'charges' =>$charges, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'premium_charges_per_hour' => $premium_charges_per_hour, 'penalty_amount_per_hour' => $penalty_amount_per_hour, 'vehicle_image' => $vehicle_image]; 
                                 }

                                
                                
                                $status_code = $success = '1';
                                $message = 'Upgrade Vehicle Filter Result';
                                
                                $json = array('status_code' => $status_code, 'message' => $message, 'booking_id' => $booking_id, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                            }else{
                                $status_code = $success = '0';
                                $message = 'Vehicle not available right now';
                                $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);    
                            }
                        }else{
                            $status_code = $success = '0';
                            $message = 'Booking detail not found for this booking id';
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
            //$message = $e->getTraceAsString(); 
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    //Upgrade Bike Detail
    public function upgrade_bike(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $bike_model_id = $request->bike_model_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

                if($employee){  

                     $booking = DB::table('vehicle_registers')->select('id','user_id','customer_id','booking_no','customer_name','vehicle_model_id','station', 'booking_status','pick_up','pick_up_time','expected_drop','expected_drop_time','booking_hours','allowed_km','total_amount')->where('user_id', $employee_id)->where('vehicle', '=', '')->where('status','In')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){
                        $station_name = $booking->station;
                        $station_id = DB::table('stations')->where('station_name', $station_name)->pluck('id')[0];
                        $bikeDetail = DB::table('vehicle_models')->where('id', $bike_model_id)->where('status', '=', 'Live')->first();
                        $bike_feature = array();
                        if($bikeDetail){ 
                            $vehicle_model = $bikeDetail->model;
                            $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour.' KM';
                            $excess_km_charges = '0';
                            $charges_per_hour = '₹ '.$bikeDetail->charges_per_hour.' / Hr';
                            $bikecharges = $bikeDetail->charges_per_hour;
                            $insurance_charges_per_hour =$bikeDetail->insurance_charges_per_hour;
                            $insurance_charges = $bikeDetail->insurance_charges_per_hour;
                            $penalty_amount_per_hour = '₹ '.$bikeDetail->penalty_amount_per_hour.' / Hr';
                            $helmet_charges = '₹ 0';
                            $helmet_status = '1';
                            $customer_id = $booking->customer_id;
                            $customer_doc = DB::table('customer_documents')->where('customer_id', $customer_id)->first();
                            if($customer_doc){
                                $document_status = 'Attached';
                            }else{
                                $document_status = 'Not Attached';
                            }

                            $from_date = $booking->pick_up." ".$booking->pick_up_time;
                            $to_date = $booking->expected_drop." ".$booking->expected_drop_time;

                            $hours = $booking->booking_hours;

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

                             if($helmet_charges){
                                $bike_feature[] =  ['title' => 'Number of Helmet (2)', 'subtitle' => $helmet_charges];

                            }

                            if($document_status){
                                $bike_feature[] =  ['title' => 'Documents Status', 'subtitle' => $document_status];

                            }

                       
                            $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];

                            $booking_time = $from_date."-".$to_date;

                            $start_trip_date = date('d-m-Y',strtotime($from_date));
                            $start_trip_time = date('H:i',strtotime($from_date));
                            $end_trip_date = date('d-m-Y',strtotime($to_date));
                            $end_trip_time = date('H:i',strtotime($to_date));

                        

                             $fleetFare = 0;
                             $total_price = 0;
                            if($hours > 0){
                                //echo $bikecharges;
                                $VehicleRegister = new VehicleRegister();
                                $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                                $total_price = $fleetFare+$insurance_charges;
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
                            $booking_amount = $booking->total_amount;
                            $differance_amount = '0';
                            if($total_price > $booking->total_amount){
                                $differance_amount = $total_price-$booking->total_amount;
                            }

                            $status_code = $success = '1';
                            $message = 'Bike Details';
                            
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id,  'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model,'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'bike_feature' => $bike_feature, 'helmet_status' => $helmet_status, 'document_status' => $document_status, 'pickup_station' => $station_name, 'booking_time' => $booking_time ,  'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time,  'old_model_booking_amount' => '₹ '.$booking_amount, 'upgrade_bike_model_price' => '₹ '.$total_price, 'differance_amount' => "".$differance_amount, 'booking_hours' => $hours." Hr" );
                        }else{
                            $status_code = $success = '0';
                            $message = 'Bike not valid';
                            
                            $json = array('status_code' => $status_code, 'message' => $message);
                        }

                
                    }else{
                        $status_code = $success = '0';
                        $message = 'booking detail not valid';
                        
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
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }


    //Make Payment for upgrade bike
    public function make_payment_upgrade_bike(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $upgrade_model_id = $request->upgrade_model_id;
            $differance_amount = $request->differance_amount;
            $allowed_km = $request->allowed_km;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }

            if($upgrade_model_id == ""){
                $error = "Please enter upgrade bike model ";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();

                if($employee){
                     $booking = DB::table('vehicle_registers')->select('id','user_id','customer_id','booking_no','customer_name','vehicle_model_id','station', 'booking_status','pick_up','pick_up_time','expected_drop','expected_drop_time','booking_hours','allowed_km','total_amount')->where('user_id', $employee_id)->where('vehicle', '=', '')->where('status','In')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){
                        $customer_id = $booking->customer_id;
                        $customer_doc = DB::table('customer_documents')->where('customer_id', $customer_id)->where('status', '=', 'Not Live')->first();
                        if($customer_doc){

                            $status_code = $success = '0';
                            $message = 'Customer Document not verified yet.';
                            
                            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id);
                        }else{
                            
                            
                            $payment_status = 'success';
                            $payment_type = 'cashToEmp';
                            
                            $upgradeBikebooking_id = DB::table('booking_upgrade_bike')->insertGetId([
                                'booking_id' => $booking_id,
                                'vehicle_model_id' => $upgrade_model_id,
                                'upgrade_amount' => $differance_amount,
                                'allowed_km' => $allowed_km,
                                'payment_status' => $payment_status,
                                'payment_type' => $payment_type,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                            
                           $status_code = $success = '1';
                           $message = 'Bike Model Upgrade Successfully';
                                
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id,'upgrade_booking_id' => $upgradeBikebooking_id, 'differance_amount' => $differance_amount , 'payment_type' => "Cash to Employee" , "employee_id"=>$employee_id); 
                               
                        
                        }
                    } else{
                        $status_code = $success = '0';
                        $message = 'Booking id not valid';
                        
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    } 


    public function current_vehicle_location(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter Booking id";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','vehicle_model_id','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('status', 'Out')->orderBy('id', 'DESC')->first();
                        
                        $time = date('H:i:s');
                        if($booking){
                            $vehicle_number = $booking->vehicle;

                            $curl = curl_init();

                            curl_setopt_array($curl, array(
                              CURLOPT_URL => 'http://13.127.228.11/webservice?token=getLiveData&vehicle_no='.$vehicle_number.'&format=json',
                              CURLOPT_RETURNTRANSFER => true,
                              CURLOPT_ENCODING => '',
                              CURLOPT_MAXREDIRS => 10,
                              CURLOPT_TIMEOUT => 0,
                              CURLOPT_FOLLOWLOCATION => true,
                              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                              CURLOPT_CUSTOMREQUEST => 'GET',
                              CURLOPT_HTTPHEADER => array(
                                'Cookie: JSESSIONID=5139FFE98C395187DB84A00C18508065'
                              ),
                            ));

                            $response = curl_exec($curl);
                            $trackdetail = json_decode($response);
                            curl_close($curl);
                            //print_r($trackdetail);
                            if(isset($trackdetail->root->error)){
                                $vehicle_No = '';
                                $vehicle_Name = '';
                                $vehicletype = '';
                                $imeino = '';
                                $deviceModel = '';
                                $location = '';
                                $datetime = '';
                                $latitude = '';
                                $longitude = '';
                                $status = '';
                                $speed = '';
                                $gps = '';
                                $ignission = '';
                                $power = '';
                                $fuel = '';
                                $odometer = '';
                            }else{    
                                $vehicle_No = $trackdetail->root->VehicleData[0]->Vehicle_No;
                                $vehicle_Name = $trackdetail->root->VehicleData[0]->Vehicle_Name;
                                $vehicletype = $trackdetail->root->VehicleData[0]->Vehicletype;
                                $imeino = $trackdetail->root->VehicleData[0]->Imeino;
                                $deviceModel = $trackdetail->root->VehicleData[0]->DeviceModel;
                                $location = $trackdetail->root->VehicleData[0]->Location;
                                $datetime = $trackdetail->root->VehicleData[0]->Datetime;
                                $latitude = $trackdetail->root->VehicleData[0]->Latitude;
                                $longitude = $trackdetail->root->VehicleData[0]->Longitude;
                                $status = $trackdetail->root->VehicleData[0]->Status;
                                $speed = $trackdetail->root->VehicleData[0]->Speed;
                                $gps = $trackdetail->root->VehicleData[0]->GPS;
                                $ignission = $trackdetail->root->VehicleData[0]->IGN;
                                $power = $trackdetail->root->VehicleData[0]->Power;
                                $fuel = $trackdetail->root->VehicleData[0]->Fuel;
                                $odometer = $trackdetail->root->VehicleData[0]->Odometer;
                            }    


                            $message = 'Vehicle Tracking Detail';
                            $status_code = '1';
                            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id,"vehicle_No" => $vehicle_No, "vehicle_Name" => $vehicle_Name, "vehicletype" => $vehicletype, "imeino" => $imeino, "deviceModel" => $deviceModel,"location" => $location,"datetime" => $datetime,"latitude" => $latitude,"longitude" => $longitude,"status" => $status,"speed" => $speed,"gps" => $gps,"ignission" => $ignission ,"power" => $power,"fuel" => $fuel,"odometer" => $odometer);
                        }else{

                            $status_code = $success = '0';
                            $message = 'Booking vehicle Data not valid';
                    
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
            //$message = $e->getTraceAsString();
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }

    public function vehicle_track_log(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter Booking id";
                $json = array('status_code' => '0', 'message' => $error, 'employee_id' => $employee_id);
            }
            if($error == ""){
                $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){
                        $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_id','customer_name','phone','vehicle_model_id','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                        $attendance_date = date('Y-m-d');
                        $time = date('H:i:s');
                        if($booking){
                             $vehicle_number = $booking->vehicle;
                             $trackbooking = DB::table('booking_vehicle_gpstrack_log')->where('booking_id', $booking_id)->orderBy('id', 'asc')->get();
                            // print_r($trackbooking);
                             $trackArr = array();
                            if($trackbooking){
                                foreach ($trackbooking as $trackdetail) {
                                    $vehicle_No = $trackdetail->vehicle_No;
                                    $trackArr[] = ['id' => (int)$booking_id, 'vehicle_No' => $vehicle_number, 'location' => $trackdetail->location, 'latitude' => $trackdetail->latitude , 'longitude' => $trackdetail->longitude, 'status' => $trackdetail->status , 'speed' => $trackdetail->speed, 'ignission' => $trackdetail->ignission, 'power' => $trackdetail->power, 'Odometer' => $trackdetail->odometer, 'datetime' => $trackdetail->created_at];

                                    
                                } 
                            }   


                            $message = 'Vehicle Tracking Log';
                            $status_code = '1';
                            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => $employee_id,"vehicle_No" => $vehicle_number, "booking_id" => $booking_id, "vehiclelog" => $trackArr);
                        }else{

                            $status_code = $success = '0';
                            $message = 'Booking vehicle Data not valid';
                    
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
    
            $json = array('status_code' => $status_code, 'message' => $message, 'employee_id' => '');
        }
        
        return response()->json($json, 200);
    }
}