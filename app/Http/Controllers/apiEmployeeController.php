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

                    $status_code = '1';
                    $message = 'Employee verified successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'employee_id' => "".$employeeid, 'mobile' => $mobile, 'name' => $name, 'email' => $email);
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

    //START show cities 
    public function allCities(Request $request)
    {
        try 
        {   
            $json       =   array();
            
            $cityList = DB::table('cities')->select('id','city')->where('status', '=', 'Live')->orderBy('id', 'ASC')->get();

            $status_code = '1';
            $message = 'All City list';
            $json = array('status_code' => $status_code,  'message' => $message, 'cityList' => $cityList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //START show feed list 
    public function all_center(Request $request)
    {
        $city_id = $request->city_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            $centerList = DB::table('stations')->select('id','city_id','station_name')->where('city_id', $city_id)->orderBy('station_name', 'ASC')->get();
            
            $status_code = '1';
            $message = 'Center list';
            $json = array('status_code' => $status_code,  'message' => $message, 'centerList' => $centerList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    public function rideType(Request $request)
    {
        try 
        {   
            $json       =   array();
            $ridetype[] = array('key' => 'regular', "value" => 'Regualr Ride');
            $ridetype[] = array('key' => 'long', "value" => 'Long Ride');
            $status_code = '1';
            $message = 'All Ride Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'ridetype' => $ridetype);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
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
            
            $json       =   array();
            $employee_id = $request->employee_id;
            $device_id = $request->device_id;
            $booking_id = $request->booking_id;

            $employee = DB::table('users')->where('id', $employee_id)->where('device_id', $device_id)->where('status', '=', 'Live')->first();
                if($employee){ 
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','vehicle', 'created_at')->where('user_id', $employee_id)->where('id', $booking_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->first();
                    
                   
                    if($booking){
                        
                         $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];

                        $status_code = '1';
                        $message = 'Booking Details';
                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'booking_no' => $booking->booking_no, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'vehicle_number' => $booking->vehicle, 'employee_name' => $employee->name, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time,  'total_amount' => $booking->total_amount, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)), 'vehicle_image_before_ride' => $booked_vehicle_before_list, 'vehicle_image_after_ride' => $booked_vehicle_after_list);
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

    
    //Make Payment
    public function make_payment(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $bike_model_id = $request->bike_id;
            $ride_type  = $request->ride_type;
            $city_id = $request->city_id;
            $station_id = $request->center_id;
            $hours = $request->hours;
            $coupon_code = $request->coupon_code;
            $total_amount = $request->total_amount;
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $document_status = 0;
            $error = "";
            if($ride_type == ""){
                $error = "Please choose ride type for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($bike_model_id == ""){
                $error = "Please choose bike model for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    $customer_doc = DB::table('customer_documents')->where('customer_id', $customer_id)->where('status', '=', 'Not Live')->first();
                    if($customer_doc){

                        $status_code = $success = '0';
                        $message = 'Customer Document not verified yet.';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                    }else{

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
                        $booking_status = '0';
                        $customer_name = $customer->name;
                        $phone = $customer->mobile;
                        $pick_up = date('Y-m-d',strtotime($from_date));
                        $pick_up_time = date('H:i',strtotime($from_date));
                        $expected_drop = date('Y-m-d',strtotime($to_date));
                        $expected_drop_time = date('H:i',strtotime($to_date));
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                        $user_id = DB::table('stations')->where('id', $station_id)->pluck('employee_id')[0];
                        $booking_id = VehicleRegister::insertGetId([
                            'user_id' => $user_id,
                            'station' => $station_name,
                            'customer_id' => $customer_id,
                            'vehicle_model_id' => $bike_model_id,
                            'customer_name' => $customer_name,
                            'phone' => $phone,
                            'pick_up' => $pick_up,
                            'pick_up_time' => $pick_up_time,
                            'expected_drop' => $expected_drop,
                            'expected_drop_time' => $expected_drop_time,
                            'coupon_code' => $coupon_code,
                            'coupon_discount' => $coupon_discount,
                            'total_amount' => $total_amount,
                            'booking_status' => $booking_status,
                            'status' => $status,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        $booking_no = "EZR".date('YmdHis').str_pad($booking_id, 5, "0", STR_PAD_LEFT);
            
                        VehicleRegister::where('id', $booking_id)->update(['booking_no' => $booking_no]);

                        $status_code = $success = '1';
                        $message = 'Bike Enquiry Booked Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id, 'booking_no' => $booking_no , 'total_amount' => $total_amount , 'booking_hours' => $hours." Hr" );
                    
                    }
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

    public function confirm_payment(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $error = "";
           if($booking_id == ""){
                $error = "Please send valid booking id.";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','total_amount', 'payment_status', 'created_at')->where('customer_id', $customer_id)->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){

                        $status = PaytmWallet::with('status');
                        $status->prepare(['order' => $booking_id]);
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
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);

                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Successfully Done.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);  

                        }else if($status->isFailed()){
                          //Transaction Failed
                            
                            $payment_status = 'failed';
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);


                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Failed.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message); 
                        }else if($status->isOpen()){
                          //Transaction Open/Processing
                            $payment_status = 'pending';
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);
                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction is Pending / Processing.';
                        
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

     public function customer_booking(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $bookingList = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','created_at')->where('customer_id', $customer_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->get();
                    $booking_list = array();
                    if($bookingList){
                        foreach($bookingList as $booking)
                        {
                            
                            $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];

                            $booking_list[] = array('id' => "".$booking->id, 'booking_no' => $booking->booking_no, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'total_amount' => $booking->total_amount, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at))); 
                           
                        } 

                        $status_code = '1';
                        $message = 'My Bookings List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'booking_list' => $booking_list);
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
