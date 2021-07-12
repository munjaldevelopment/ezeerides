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



class apiController extends Controller
{
    //START LOGIN
	public function customerLogin(Request $request)
    {
        try 
        {
            $mobile = $request->mobile;
            $device_id = $request->device_id;
            $fcmToken = $request->fcmToken;
            $refer_code = $request->refer_code;
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($device_id == ""){
                $error = "Device id not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $json = $userData = array();
                $mobile = $mobile;
                $date   = date('Y-m-d H:i:s');
                $customer = DB::table('customers')->where('mobile', $mobile)->first();
                if($customer) 
                {
                    
                    $customerid = $customer->id;
                    $deviceid = $customer->device_id;
                    $customer_status = $customer->status;
                    
                    if($customer_status == 'Live'){

                        $otp = rand(11111, 99999);
                        
                       $smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");

                        $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                    
                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => "".$otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                       
                        //$refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.krishimulya&referrer=krvrefer".$customerid;
                        
                        $status_code = '1';
                        $message = 'Customer login OTP Send';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' =>"".$customerid, "customer_type" => "already" , 'otp' => "".$otp);
                    }else{
                        $otp = rand(11111, 99999);
                        
                       $smsmessage = str_replace(" ", '%20', "Here is the new OTP ".$otp." for your login id. Please do not share with anyone.");

                        $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");
                    
                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                        $status_code = $success = '1';
                        $message = 'Customer Otp Send, Please Process Next Step';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => "".$customerid, 'mobile' => $mobile, "customer_type" => "already", 'otp' => "".$otp);
                    }
                        
                   
                }else{
                		
                    $otp = rand(11111, 99999);
                    $smsmessage = str_replace(" ", '%20', "Thank you for registering on AUTO AWAY RENTALS app. ".$otp." is the OTP for your Login id. Please do not share with anyone.");
                        
                    $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");

                    $customerid = DB::table('customers')->insertGetId(['mobile' => $mobile, 'otp' => "".$otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'created_at' => $date, 'status' => 'Not live',  'updated_at' => $date]); 

                    $date   = date('Y-m-d H:i:s');
                    if($refer_code != ""){
                        $referCustomerid = str_replace('ezeerdrefer', '', $refer_code); 
                        $referal_customer_id = $referCustomerid;
                        $refercustomerid = DB::table('customer_refer_register')->insertGetId(['customer_id' => $customerid, 'referal_customer_id' => $referal_customer_id, 'created_at' => $date]);    
                        
                    }

                    $status_code = $success = '1';
                    $message = 'Customer Otp Send, Please Process Next Step';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => "".$customerid, 'mobile' => $mobile, "customer_type" => "new", 'otp' => "".$otp);
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
        $saveNotification = DB::table('notifications')->insertGetId(['customer_id' => $customer_id,'notification_title' => $title, 'notification_content' => $message, 'notification_type' => 'customer_notification', 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);
        //echo $success.",".$fail.",".$total; exit;
    }

    //START VERIFY
    public function customerVerify(Request $request)
    {
        try 
        {
            $baseUrl = URL::to("/");
            $json = $userData = array();
            $mobile = $request->mobile;
            $otp = $request->otp;
            $date   = date('Y-m-d H:i:s');
            $error = "";
            if($mobile == ""){
                $error = "Please enter valid mobile number";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($otp == ""){
                $error = "otp not found";
                $json = array('status_code' => '0', 'message' => $error);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('mobile', $mobile)->where('otp', $otp)->first();
                if($customer) 
                {
                    $device_id = $customer->device_id;
                    $fcmToken = $customer->fcmToken;
                    $customerid = $customer->id;

                    
                     DB::table('customers')->where('id', '=', $customerid)->update(['status' => 'Live', 'updated_at' => $date]); 
                     $usersChk = DB::table('users')->where('phone', $mobile)->first();
                    if($usersChk) 
                    {    

                    }else{     
                            $userid = DB::table('users')->insertGetId(['phone' => $mobile, 'password' => Hash::make($mobile), 'created_at' => $date, 'updated_at' => $date]);
                            $role_id = 3;
                            $model_type = 'App\User';
                             $roleid = DB::table('model_has_roles')->insert(['role_id' => $role_id, 'model_type' => $model_type, 'model_id' => $userid]);

                            DB::table('customers')->where('id', '=', $customerid)->update(['user_id' => $userid, 'updated_at' => $date]);
                    }      

                     
                    
                    $refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.ezeerides&referrer=ezeerdrefer".$customer->id;

                    $name = '';
                    $email = '';
                    $city_id = '';
                    $city_name = '';
                    $station_id = '';
                    $station_name = '';
                    $address = "";
                    $customer_image  = "";
                    $rscustomer = DB::table('customers')->where('id', $customerid)->first();
                    if($rscustomer) 
                    {
                        $name = $rscustomer->name;
                        $email = $rscustomer->email;
                        if($rscustomer->address){
                            $address = $rscustomer->address;    
                        }
                        if($rscustomer->image){
                            $customer_image  =  $baseUrl."/public/".$rscustomer->image;
                        }else{
                           $customer_image  =  "";
                        }
                        if($rscustomer->city_id > 0){
                            $city_id = "".$rscustomer->city_id;
                            $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                        }
                        if($rscustomer->station_id > 0){
                            $station_id = "".$rscustomer->station_id;
                            $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                        }    
                        if($name != '' && $email != ''){
                            $profile_status = 'true';
                        }else{
                            $name = '';
                            $email = '';
                            $city_id = '';
                            $city_name = '';
                            $station_id = '';
                            $station_name = '';
                            $profile_status = 'false';
                        }
                    }   

                    $status_code = '1';
                    $message = 'Customer activated successfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => "".$customerid, 'mobile' => $mobile, 'referurl' => $refer_url, 'name' => $name, 'email' => $email, 'city_id' => $city_id, 'city_name' => $city_name, 'center_id' => $station_id, 'station_name' => $station_name, 'address' => $address, 'customer_image' => $customer_image, 'profile_status' => $profile_status);
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
                $customer = DB::table('customers')->where('mobile', $mobile)->first();
                if($customer) 
                {
                    $customerid = $customer->id;
                    $otp = rand(11111, 99999);
                    
                    $smsmessage = "Here is the new OTP ".$otp." for your login id. Please do not share with anyone. ";

                    $this->httpGet("http://sms.messageindia.in/sendSMS?username=ezeego&message=".$smsmessage."&sendername=EZEEGO&smstype=TRANS&numbers=".$mobile."&apikey=888b42ca-0d2a-48c2-bb13-f64fba81486a");


                     DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'updated_at' => $date]);

                    $status_code = '1';
                    $message = 'OTP Send sucessfully';
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => "".$customerid,  'mobile' => $mobile, 'otp' => "".$otp);
                } 
                else 
                {
                    $status_code = $success = '0';
                    $message = 'Sorry! Customer does not exists';
                    
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

    
     //START show feed list 
    public function birth_year(Request $request)
    {
        try 
        {   
            $json   =   array();
            $yearList       =   array();
            $year = date('Y');
            $year2 = date('Y')-60;
            for($y = $year; $y>$year2; $y--){
                $yearList[] = array('year' => "".$y."");
            }
            
            $status_code = '1';
            $message = 'Birth year list';
            $json = array('status_code' => $status_code,  'message' => $message, 'yearList' => $yearList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 


    
    //Customer Update
    public function customer_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
           
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
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
                if($customer->dob){
                 //$age = date("d-m-Y",strtotime($customer->age));
                   $dob = $customer->dob;
                }else{
                 $dob = "";
                } 
                $mobile = $customer->mobile;
                if($customer->address){
                    $address = $customer->address;    
                }else{
                    $address = "";
                }
                if($customer->city_id != 0){
                    $city_id = "".$customer->city_id;
                    $city_name = DB::table('cities')->where('id', $city_id)->pluck('city')[0];
                    
                }else{
                    $city_id = "";
                    $city_name = "";
                }
                if($customer->station_id != 0){
                    $station_id = "".$customer->station_id;
                    $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                }else{
                    $station_id = "";
                    $station_name = "";
                }
                
                $baseUrl = URL::to("/");
                $customer_image  = "";
                if($customer->image){
                    $customer_image  =  $baseUrl."/public/".$customer->image;
                
                }else{
                   $customer_image  =  "";
                }
                
                
                $status_code = $success = '1';
                $message = 'Customer Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id , 'name' => $name, 'email' => $email, 'dob' => $dob, 'mobile' => $mobile, 'address' => $address , 'city_id' => $city_id ,'city_name' => $city_name ,'center_id' => $station_id ,'station_name' => $station_name , 'customer_image' => $customer_image);


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

    //Customer Update
    public function update_profile(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $name = $request->name;
            $dob = $request->dob;
            $email = $request->email ;
            //$telephone = $request->telephone;
            $address = $request->address;
            $city_id = $request->city_id;
            $station_id = $request->center_id;
            $customer_image = $request->customer_image;
            
            
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
            if($customer){ 

                $chkemail = DB::table('customers')->where('email', $email)->where('id', '!=', $customer_id)->first();
                if($chkemail){
                    $status_code = $success = '0';
                    $message = 'Email already exists, please try another ';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                }else{
                    $customerimage = '';
                    /*if ($request->hasFile('customer_image')) {
                        $image = $request->file('customer_image'); 
                        if($image)
                        {
                            $customer_image = rand(10000, 99999).'-'.time().'.'.$image->getClientOriginalExtension();
                            $destinationPath = public_path('/uploads/customer_image/');
                            $image->move($destinationPath, $customer_image);
                            
                        }
                    }*/
                    if($customer_image != ''){
                        $image_parts = explode(";base64,", $customer_image);
                        $image_type_aux = explode("image/", $image_parts[0]);
                        $image_type = $image_type_aux[1];

                        $customerimage = rand(10000, 99999).'-'.time().'.'.$image_type;
                        $destinationPath = public_path('/uploads/customer_image/').$customerimage;

                        $data = base64_decode($image_parts[1]);
                       // $data = $image_parts[1];
                        file_put_contents($destinationPath, $data);

                        DB::table('customers')->where('id', '=', $customer_id)->update(['image' => 'uploads/customer_image/'.$customerimage, 'updated_at' => $date]);
                    }
                    if($city_id == ''){
                        $city_id = '0';
                    }
                    if($station_id == ''){
                        $station_id = '0';
                    }
                    DB::table('customers')->where('id', '=', $customer_id)->update(['name' => $name, 'dob' => $dob, 'email' => $email, 'address' => $address, 'city_id' => $city_id, 'station_id' => $station_id, 'updated_at' => $date]);
                    
                    /* user update */
                    $user_id = $customer->user_id;
                    if($user_id){
                        DB::table('users')->where('id', '=', $user_id)->update(['name' => $name, 'email' => $email, 'updated_at' => date('Y-m-d H:i:s')]);
                    }    
                    $status_code = $success = '1';
                    $message = 'Customer info updated successfully';
                    
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


    //Customer Update
    public function customer_logout(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
           
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
            if($customer){ 
                
                $status_code = $success = '1';
                $message = 'Customer logout successfully';
                
                $json = array('status_code' => $status_code, 'message' => $message);


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


    

    //START show customer_documents 
    public function customer_documents(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
            if($customer){
                $custname = $customer->name;
               
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
                    $customerDocArr[] = ['id' => (int)$doclist->id, 'title' => $doclist->title, 'front_image' => $front_image, 'back_image' => $back_image , 'other_image' => $other_image]; //'planning_isprogress' => 
                }
                
                $status_code = '1';
                $message = 'All Document list';
                $json = array('status_code' => $status_code,  'message' => $message, 'name' => $custname, 'customerDocList' => $customerDocArr);
            }else{
                $status_code = $success = '0';
                $message = 'Customer not exists or not verified';
                
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

    public function documentType(Request $request)
    {
        try 
        {   
            $json       =   array();
            $doctype[] = array('key' => 'Driving License', "value" => 'Driving License');
            $doctype[] = array('key' => 'ID Proof (Adhaar Card)', "value" => 'ID Proof (Adhaar Card)');
            $status_code = '1';
            $message = 'All Document Type';
            $json = array('status_code' => $status_code,  'message' => $message, 'doctype' => $doctype);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END
     //Customer Update
    public function upload_documents(Request $request)
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

    //START show feed list 
    public function all_center(Request $request)
    {
        $city_id = $request->city_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            $centerArr[] = array('id' => 0, "city_id" => (int)$city_id, "station_name" => 'All');
            $centerList = DB::table('stations')->select('id','city_id','station_name')->where('city_id', $city_id)->orderBy('station_name', 'ASC')->get();
            
            foreach($centerList as $rlist)
            {
                $centerArr[] = ['id' => $rlist->id, 'city_id' => $rlist->city_id,'station_name' =>$rlist->station_name]; 
            }    
            $status_code = '1';
            $message = 'Center list';
            $json = array('status_code' => $status_code,  'message' => $message, 'centerList' => $centerArr);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

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

    //START show cities 
    public function vehicleRides(Request $request)
    {
        try 
        {   
            $json       =   array();
            $baseUrl = URL::to("/");
            
            $rideList = DB::table('vehicle_rides')->select('id','title','description','vehicle_icon','ride_type')->orderBy('id', 'ASC')->get();
            $ride_list = array();
            foreach($rideList as $rlist)
            {
                $vehicle_icon  = "";
                if($rlist->vehicle_icon){
                    $vehicle_icon  =  $baseUrl."/public/".$rlist->vehicle_icon;
                
                }
               
                
                $ride_list[] = ['id' => (string)$rlist->id, 'title' =>$rlist->title,'description' =>$rlist->description,'vehicle_icon' => $vehicle_icon, 'ride_type' =>$rlist->ride_type,]; 
            }    
            $status_code = '1';
            $message = 'All Vehicle Ride';
            $json = array('status_code' => $status_code,  'message' => $message, 'ride_list' => $ride_list);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function centerByModel(Request $request)
    {
        $bike_model_id = $request->bike_model_id;
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            
            $centerList = DB::table('stations as s')->join('station_has_vehicles as sv', 's.id', '=', 'sv.station_id')->join('vehicles as v', 'sv.vehicle_id', '=', 'v.id')->select('s.id','s.city_id','s.station_name')->where('v.vehicle_model', $bike_model_id)->orderBy('station_name', 'ASC')->groupBy('s.id')->get();
            
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
    //vehicle filter Result
    public function vehicle_filter(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $city_id = $request->city_id;
            $center = $request->center_id;
            $ride_type = $request->ride_type;
            $from_date = date("Y-m-d H:i:s",strtotime($request->from_date));
            $to_date = date("Y-m-d  H:i:s",strtotime($request->to_date));
            $error = "";
            if($center == ""){
                $error = "Please enter center for ride";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 

                    $vehicleList = DB::table('vehicles as v')->join('vehicle_models as vm', 'v.vehicle_model', '=', 'vm.id')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->select('vm.id','vm.model','vm.allowed_km_per_hour','vm.charges_per_hour','vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('v.status','Live')->groupBy('vm.id');

                    if($center > 0){
                        $vehicleList = $vehicleList->where('sv.station_id',$center);    
                    }

                   /* if($ride_type){
                        $vehicleList = $vehicleList->where('v.ride_type',$ride_type);    
                    }*/

                    if($from_date){
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

                            $pick_upDateTime = $from_date;
                            $expected_dropDateTime = $to_date;
                            $timestamp1 = strtotime($pick_upDateTime);
                            $timestamp2 = strtotime($expected_dropDateTime);
                            if($ride_type == 'long'){
                                $hours = abs($timestamp2 - $timestamp1)/(60*60);
                            }else{
                                $hours = 4;
                            }

                            $bikecharges = $charges_per_hour;
                            $fleetFare = 0;
                            $total_price = 0;
                            if($hours > 0){
                               
                                $VehicleRegister = new VehicleRegister();
                                $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                                $total_price = $fleetFare+$insurance_charges_per_hour;
                            }
                            $day = floor($hours/24);
                            if($ride_type == 'long'){
                                
                               
                                $charges = '₹ '.$fleetFare.' / '.$day.'d';

                            }else{
                                
                                $charges = '₹ '.$fleetFare.' / Hr';
                            }
                           

                            $available_bike = '0';
                            $user_id = '';
                            $next_booking_time = '';
                            $station_name = '';
                            if($center > 0){

                                $user_id = DB::table('stations')->where('id', $center)->pluck('employee_id')[0];
                                $station_name = DB::table('stations')->where('id', $center)->pluck('station_name')[0];
                            
                            if($user_id != ''){
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
                            }else{
                                $available_bike = "".count($vehicle_list);
                            }
                        }

                        }else{
                            $usedVehList = array('01');
                            $bookedvehicle = \DB::table('vehicle_registers')->where('vehicle', '!=', '')->where('vehicle_model_id', $vlist->id)->where('status', 'Out')->distinct()->select('vehicle')->get();
                            if($bookedvehicle){
                                foreach ($bookedvehicle as $usedvehicle) {
                                    if($usedvehicle->vehicle){
                                        $usedVehList[] = $usedvehicle->vehicle;
                                    }
                                }
                            }
                            $onrideBike = implode(',', $usedVehList);    
                            $totavailablevehicle = DB::table('vehicles as v')->select('v.id')->whereNotIn('v.vehicle_number', $usedVehList)->where('v.vehicle_model', $vlist->id)->orderBy('v.id', 'DESC')->count();
                            $available_bike = "".$totavailablevehicle;
                        }
                        if($available_bike > 5){
                            $available_bike = '5+';
                        }    

                            $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'allowed_km_per_hour' =>$allowed_km_per_hour, 'charges_per_hour' =>$charges_per_hour, 'booking_hours' =>$hours, 'charges' =>$charges, 'available_bike' =>$available_bike, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'premium_charges_per_hour' => $premium_charges_per_hour, 'penalty_amount_per_hour' => $penalty_amount_per_hour, 'vehicle_image' => $vehicle_image,'next_booking_time'=>$next_booking_time]; 
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
                    
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
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

     

    //Bike Detail
    public function bike_detail(Request $request)
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
            $from_date = $request->from_date;
            $to_date = $request->to_date;
            $coupon_code = $request->coupon_code;
            $error = "";
            $current_date = date('Y-m-d H:i:s');
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
                    
                    $bikeDetail = DB::table('vehicle_models')->where('id', $bike_model_id)->where('status', '=', 'Live')->first();
                    $bike_feature = array();
                    if($bikeDetail){ 
                        $vehicle_model = $bikeDetail->model;
                        $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour.' / Hr';
                        $excess_km_charges = '₹ '.$bikeDetail->excess_km_penalty_charges." / KM";
                        $charges_per_hour = '₹ '.$bikeDetail->charges_per_hour.' / Hr';
                        $bikecharges = $bikeDetail->charges_per_hour;
                        $insurance_charges_per_hour =$bikeDetail->insurance_charges_per_hour;
                        $insurance_charges = $bikeDetail->insurance_charges_per_hour;
                        $penalty_amount_per_hour = '₹ '.$bikeDetail->penalty_amount_per_hour.' / Hr';
                        $helmet_charges = '₹ 0';
                        $helmet_status = '1';
                        $customer_doc = DB::table('customer_documents')->where('customer_id', $customer_id)->first();
                        if($customer_doc){
                            $document_status = 'Attached';
                        }else{
                            $document_status = 'Not Attached';
                        }

                        $pick_upDateTime = $from_date;
                        $expected_dropDateTime = $to_date;
                        $timestamp1 = strtotime($pick_upDateTime);
                        $timestamp2 = strtotime($expected_dropDateTime);

                        $hours = abs($timestamp2 - $timestamp1)/(60*60);

                        $fleetFare = 0;
                         $total_price = 0;
                        if($hours > 0){
                            //echo $bikecharges;
                            $VehicleRegister = new VehicleRegister();
                            $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                            $total_price = $fleetFare+$insurance_charges;
                        }

                        if($allowed_km_per_hour > 0){
                            $bike_feature[] =  ['title' => 'Allowed KM','subtitle' => $allowed_km_per_hour];
                            
                        }
                        if($excess_km_charges){
                             $bike_feature[] =  ['title' => 'Excess KM Charges', 'subtitle' => $excess_km_charges];
                        }

                        if($document_status){
                            $bike_feature[] =  ['title' => 'Documents Status', 'subtitle' => $document_status];

                        }

                        if($penalty_amount_per_hour){
                             
                             $bike_feature[] =  ['title' => 'Penalty', 'subtitle' => $penalty_amount_per_hour];

                        }

                        

                        if($charges_per_hour){
                          
                            $bike_feature[] =  ['title' => 'Charges', 'subtitle' => '₹ '.$fleetFare];
                            
                        }

                        if($helmet_charges){
                            $bike_feature[] =  ['title' => 'Helmet', 'subtitle' => '1 Compulsory / Subject to Availability'];

                        }

                        if($insurance_charges_per_hour > 0){
                            
                            $bike_feature[] =  ['title' => 'Insurance for your Ride', 'subtitle' => '₹ '.$insurance_charges_per_hour];

                            
                        }

                        

                        
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
                       
                        $bike_feature[] =  ['title' => 'Pre Penalty Amount', 'subtitle' => "".$customer_penalty];

                       
                        $total_price += $customer_penalty;
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                        $station_address = DB::table('stations')->where('id', $station_id)->pluck('station_address')[0];
                        if($station_address){
                            $station_name .= " (".$station_address.")";
                        }

                        $booking_time = $from_date."-".$to_date;

                        $start_trip_date = date('d-m-Y',strtotime($from_date));
                        $start_trip_time = date('H:i',strtotime($from_date));
                        $end_trip_date = date('d-m-Y',strtotime($to_date));
                        $end_trip_time = date('H:i',strtotime($to_date));

                        
                        

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
                        $referCouponList = DB::table('customer_referal_coupons')->select('id','customer_id','coupon_code', 'discount','description')->where('customer_id', $customer_id)->where('status', 'Live')->wheredate('end_date',' >= ',$current_date)->whereNotIn('coupon_code', $usedCouponList)->orderBy('id', 'ASC')->get();
                        $coupon_list = array();
                        if($referCouponList){
                            foreach($referCouponList as $couponlist)
                            {
                                
                                $coupon_list[] = array('coupon_code' => "".$couponlist->coupon_code, 'discount_type' => 'percentage', 'discount' => $couponlist->discount, 'description' => $couponlist->description); 
                               
                            }
                        } 

                        $generalCouponList = DB::table('coupons')->select('id','title','discount_type','discount','description')->where('status', 'Live')->wheredate('end_date',' >= ',$current_date)->whereNotIn('title', $usedCouponList)->orderBy('id', 'ASC')->get();
                        if($generalCouponList){
                            $custnumodr = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_status', '=', 'success')->count();
                            foreach($generalCouponList as $gencouponlist)
                            {
                                
                                if($gencouponlist->title == 'FIRSTRIDE' && $custnumodr > 0){
                                    
                                }else{
                                    $coupon_list[] = array('coupon_code' => "".$gencouponlist->title, 'discount_type' => $gencouponlist->discount_type, 'discount' => $gencouponlist->discount, 'description' => $gencouponlist->description); 
                                }
                               
                            }
                        }
                        /* Get wallet amount */
                        $orderWalletAmt = 0;
                        $expandWalletAmt = 0;
                        $upgradeWalletAmt = 0;

                        $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                        $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');

                        $expandWalletAmt = DB::table('booking_expended as be')->join('vehicle_registers as v', 'be.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('be.payment_type', '=', 'wallet')->where('be.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('be.expand_amount');

                        $upgradeWalletAmt = DB::table('booking_upgrade_bike as up')->join('vehicle_registers as v', 'up.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('up.payment_type', '=', 'wallet')->where('up.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('up.upgrade_amount');
                    
                        $totalwaletamount = "".($walletAmt-($orderWalletAmt+$expandWalletAmt+$upgradeWalletAmt));
                        
                        $wallet_amount = $totalwaletamount;

                        $status_code = $success = '1';
                        $message = 'Bike Details';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'ride_type' => $ride_type, 'city_id' => $city_id , 'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model,'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'bike_feature' => $bike_feature, 'helmet_status' => $helmet_status, 'document_status' => $document_status, 'pickup_station' => $station_name, 'booking_time' => $booking_time ,  'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time, 'coupon_list' => $coupon_list, 'without_insurance_price' => "".$fleetFare, 'wallet_amount' => $wallet_amount, 'total_price' => '₹ '.$total_price, 'booking_hours' => $hours." Hr" );
                    }else{
                        $status_code = $success = '0';
                        $message = 'Bike not valid';
                        
                        $json = array('status_code' => $status_code, 'message' => $message);
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
            $payment_type = $request->payment_type;
            $lattitude = $request->lattitude;
            $longitude = $request->longitude;
            $document_status = 0;
            $error = "";
            if($payment_type == ""){
                $error = "Please choose payment type for bike booking";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
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

                        if($payment_type == 'wallet'){
                            $payment_type = 'wallet';
                            $payment_status = 'success';
                        }else{
                            $payment_type = 'paytm';
                            $payment_status = 'pending';
                        }
                        $status = 'In';
                        $booking_status = '1';
                        $customer_name = $customer->name;
                        $phone = $customer->mobile;

                        $pick_up = date('Y-m-d',strtotime($from_date));
                        $pick_up_time = date('H:i',strtotime($from_date));
                        $expected_drop = date('Y-m-d',strtotime($to_date));
                        $expected_drop_time = date('H:i',strtotime($to_date));
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];
                        $user_id = DB::table('stations')->where('id', $station_id)->pluck('employee_id')[0];
                        
                        $otp = rand(111111, 999999);
                        
                        $allowed_km_per_hour = DB::table('vehicle_models')->where('id', $bike_model_id)->pluck('allowed_km_per_hour')[0];

                        $allowed_km = ($allowed_km_per_hour*$hours);
                        $booking_from = 'customer';
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
                            'booking_from' => $booking_from,
                            'status' => $status,
                            'payment_status' => $payment_status,
                            'payment_type' => $payment_type,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        $booking_no = "EZR".date('YmdHis').str_pad($booking_id, 5, "0", STR_PAD_LEFT);
            
                        VehicleRegister::where('id', $booking_id)->update(['booking_no' => $booking_no]);

                    if($payment_type != 'wallet'){
                        $enviroment='local';
                        $merchent_id ='FnAoux43246182437237';
                        $merchantKey='2fCkkMtPcbf###hr';
                        $merchantwebsite='WEBSTAGING';
                        $channel='WEB';
                        $industryType='Retail';

                       

                        $paytmParams = array();
                        $orderid = $booking_id;
                        $paytmParams["body"] = array(
                            'requestType' => 'Payment',
                            'mid' => $merchent_id,
                            'websiteName' => 'WEBSTAGING',
                            'orderId' => $orderid,
                            'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." ",
                            'txnAmount'     => array(
                                'value'     => $total_amount,
                                'currency'  => 'INR',
                            ),
                            'userInfo'      => array(
                                'custId'    => $customer_id,
                            )
                        );

                        /*
                        * Generate checksum by parameters we have in body
                        * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
                        */
                        $payment = PaytmWallet::with('receive');
                        $checksum = $payment->generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);

                        $paytmParams["head"] = array('signature'=>$checksum);

                        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

                        /* for Staging */
                        $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=FnAoux43246182437237&orderId=".$orderid." ";

                        /* for Production */
                        // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type':'application/json')); 
                        $response = curl_exec($ch);
                        $gettxnarr = json_decode($response);
                        $txnToken = $gettxnarr->body->txnToken;
                        $callbackUrl = "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid;
                    }else{
                        $enviroment = '';
                        $merchent_id = '';
                        $merchantKey = '';
                        $merchantwebsite = '';
                        $channel = '';
                        $industryType = '';
                        $txnToken = '';
                        $callbackUrl = '';

                    }
                       

                        $status_code = $success = '1';
                        $message = 'Bike Enquiry Booked Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id, 'booking_no' => $booking_no , 'total_amount' => $total_amount , 'booking_hours' => $hours." Hr", 'payment_type' => $payment_type, 'enviroment' => $enviroment, 'mid' => $merchent_id, 'merchantKey' => $merchantKey, 'merchantwebsite' => $merchantwebsite, 'channel' => $channel, 'industryType' => $industryType, "txnToken" => $txnToken, 'callbackUrl' => $callbackUrl );
                    
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
                            $payment_type = 'paytm';
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status,  'payment_type' => $payment_type, 'updated_at' => $date]);

                             /* paid due penalties */
                            $booked_vehicleList = DB::table('vehicle_registers')->select('id','customer_id','additional_amount','receive_amount')->where('customer_id',$customer_id)->where('booking_status','1')->where('additional_amount', '>', 0)->where('is_amount_receive', '=', 1)->get();
                            $customer_penalty = 0;
                            if(count($booked_vehicleList) >0){
                                foreach($booked_vehicleList as $vlist)
                                {
                                    if($vlist->receive_amount < $vlist->additional_amount){
                                        /* update penalties */
                                        $penaltybooking_id = $vlist->id;
                                        $penaltyamt = $vlist->additional_amount;
                                        $due_penalty = 'no';   
                                        DB::table('vehicle_registers')->where('id', '=', $penaltybooking_id)->update(['receive_amount' => "".$penaltyamt, 'due_penalty' => $due_penalty, 'updated_at' => $date]);
                                    }
                                }
                            }        
                            /* End */

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
            
            $json      =   array();
            $baseUrl = URL::to("/");
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    //$bookingList = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','register_otp','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount', 'booking_status', 'created_at')->where('customer_id', $customer_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->get();

                    $bookingList = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','register_otp','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount', 'booking_status','status', 'vehicle','is_expended', 'is_upgrade', 'created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();
                    $booking_list = array();
                    if($bookingList){
                        foreach($bookingList as $booking)
                        {
                            if($booking->is_expended == 'yes'){
                                $expand_booking = DB::table('booking_expended')->where('booking_id', $booking->id)->where('payment_status', 'success')->orderBy('id', 'DESC')->first();
                                $booking->expected_drop = $expand_booking->expand_date;
                                $booking->expected_drop_time = $expand_booking->expand_time;
                                $booking->total_amount += $expand_booking->expand_amount;
                            }
                            if($booking->is_upgrade == 'yes'){
                                $upgrade_vehicle_model_id = DB::table('booking_upgrade_bike')->where('booking_id', $booking->id)->where('payment_status', 'success')->pluck('vehicle_model_id')[0];
                                $vehicle_model_id = $upgrade_vehicle_model_id;
                                $vehicle_model = DB::table('vehicle_models')->where('id', $vehicle_model_id)->pluck('model')[0];
                            }else{
                                $vehicle_model_id = $booking->vehicle_model_id;
                                $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];
                            }
                            $vehicle_image = DB::table('vehicle_models')->where('id', $vehicle_model_id)->pluck('vehicle_image')[0];
                             $bike_image = '';
                             if($vehicle_image){
                                $bike_image = $baseUrl."/public/".$vehicle_image; 
                             }
                            $expand_button = 'f';
                            $upgrade_button = 'f'; 
                            $expected_dropdatetime = strtotime($booking->expected_drop." ".$booking->expected_drop_time);
                            $current_time = strtotime(date('Y-m-d H:i:s'));
                            if($booking->booking_status == 1){
                               $booking_status = 'Open';
                               if($booking->status == 'Out' && $expected_dropdatetime > $current_time){
                                   $expand_button = 't';
                               }
                               if($booking->status == 'In' && $booking->vehicle == '' && $expected_dropdatetime > $current_time){
                                   $upgrade_button = 't';
                               }

                            }else if($booking->booking_status == 0){
                               $booking_status = 'Canceled'; 
                            }else if($booking->booking_status == 2){
                               $booking_status = 'Completed';      
                            }
                            
                            $booking_list[] = array('id' => "".$booking->id, 'booking_no' => $booking->booking_no, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'booking_otp' => "".$booking->register_otp, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'center_name' => $booking->station, 'vehicle_image' => $bike_image, 'vehicle_model' => $vehicle_model, 'total_amount' => "".$booking->total_amount, 'booking_status' => $booking_status, 'is_expand_button' => $expand_button, 'is_upgrade_button' => $upgrade_button, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at))); 
                           
                        } 

                        $status_code = '1';
                        $message = 'My Bookings List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'booking_list' => $booking_list);
                    }else{
                         $status_code = '0';
                        $message = 'No booking found.';
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

    //Expand Date Bike Detail
    public function expand_drop(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $expand_date = $request->expand_date;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }


            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 

                    $booking = DB::table('vehicle_registers')->select('id','user_id','booking_no','customer_name','phone','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','vehicle', 'booking_status','is_upgrade')->where('customer_id', $customer_id)->where('vehicle', '!=', '')->where('status','Out')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){
                        $bike_model_id = $booking->vehicle_model_id;
                        $vehicle_number = $booking->vehicle;
                        if($booking->is_upgrade == 'yes'){
                            $upgrade_vehicle_model_id = DB::table('booking_upgrade_bike')->where('booking_id', $booking->id)->where('payment_status', 'success')->pluck('vehicle_model_id')[0];
                            $booking->vehicle_model_id = $upgrade_vehicle_model_id;
                            
                        }
                        /*Check Bike availability */
                        $usedVehList = array('01');
                        $bookedvehicle = \DB::table('vehicle_registers')->where('vehicle', '!=', '')->where('vehicle_model_id', $booking->vehicle_model_id)->where('user_id', $booking->user_id)->where('status', 'Out')->where('station', $booking->station)->distinct()->select('vehicle')->get();
                        if($bookedvehicle){
                            foreach ($bookedvehicle as $usedvehicle) {
                                if($usedvehicle->vehicle){
                                    $usedVehList[] = $usedvehicle->vehicle;
                                }
                            }
                        }
                        //print_r($usedVehList);
                        $vehiclelist = DB::table('vehicles as v')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->join('stations as s', 's.id', '=', 'sv.station_id')->select('v.id','v.vehicle_number')->whereNotIn('v.vehicle_number', $usedVehList)->where('v.vehicle_model', $booking->vehicle_model_id)->where('s.employee_id', $booking->user_id)->orderBy('v.id', 'DESC')->get();
                        $vehicle_list = array();
                        foreach($vehiclelist as $bikelist)
                        {
                            if($bikelist->vehicle_number){
                                $vehicle_list[] = array('vehicle_number' => $bikelist->vehicle_number); 
                            }
                        }
                       // print_r($vehicle_list);
                        /* get latest return bike booking id */ 
                        $latestreturnbookedvehicle = \DB::table('vehicle_registers')->where('vehicle', '!=', '')->where('vehicle_model_id', $booking->vehicle_model_id)->where('status', 'Out')->where('station', $booking->station)->orderBy('expected_drop', 'DESC')->orderBy('expected_drop_time', 'DESC')->first();
                         //echo count($vehicle_list)."-".$latestreturnbookedvehicle->id;
                        if(count($vehicle_list) > 0 || $booking_id != $latestreturnbookedvehicle->id){

                            $bikeDetail = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->where('status', '=', 'Live')->first();
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
                                
                                $pick_upDateTime = $booking->expected_drop." ".$booking->expected_drop_time;
                                $expand_DateTime = $expand_date;
                                $timestamp1 = strtotime($pick_upDateTime);
                                $timestamp2 = strtotime($expand_DateTime);

                                $hours = abs($timestamp2 - $timestamp1)/(60*60);

                                $allowed_km = ($bikeDetail->allowed_km_per_hour*$hours);

                                 $fleetFare = 0;
                                 $total_price = 0;
                                if($hours > 0){
                                    //echo $bikecharges;
                                    /*$VehicleRegister = new VehicleRegister();
                                    $fleetFare = $VehicleRegister->getFleetFare($hours,$bikecharges);
                                    $total_price = $fleetFare+$insurance_charges;*/
                                    $fleetFare = $hours*$bikecharges;
                                    $total_price = $fleetFare+$insurance_charges;

                                }
                                
                                $baseUrl = URL::to("/");
                                $vehicle_image  = "";
                                if($bikeDetail->vehicle_image){
                                    $vehicle_image  =  $baseUrl."/public/".$bikeDetail->vehicle_image;
                                
                                }
                                
                                $expanddate = date('Y-m-d',strtotime($expand_date));
                                $expand_time = date('H:i:s',strtotime($expand_date));
                                
                                /* Get wallet amount */
                                $orderWalletAmt = 0;
                                $expandWalletAmt = 0;
                                $upgradeWalletAmt = 0;

                                $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                                $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');

                                $expandWalletAmt = DB::table('booking_expended as be')->join('vehicle_registers as v', 'be.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('be.payment_type', '=', 'wallet')->where('be.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('be.expand_amount');

                                $upgradeWalletAmt = DB::table('booking_upgrade_bike as up')->join('vehicle_registers as v', 'up.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('up.payment_type', '=', 'wallet')->where('up.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('up.upgrade_amount');
                            
                                $totalwaletamount = "".($walletAmt-($orderWalletAmt+$expandWalletAmt+$upgradeWalletAmt));

                                $wallet_amount = $totalwaletamount;
                                $status_code = $success = '1';
                                $message = 'Expand Date Details';
                                
                                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id, 'vehicle_model' => $vehicle_model, 'vehicle_number' => $vehicle_number, 'vehicle_image' => $vehicle_image, 'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'expand_date' => $expanddate, 'expand_time' => $expand_time, 'center_name' => $booking->station,  'without_insurance_price' => "".$fleetFare, 'expand_amount' => '₹ '.$total_price, 'wallet_amount' => $wallet_amount, 'allowed_km' => $allowed_km, 'booking_hours' => $hours." Hr" );
                            }else{
                               $status_code = $success = '0';
                                
                                $message = 'Bike not valid';
                                $json = array('status_code' => $status_code, 'message' => $message); 
                            }
                        }else{
                            $status_code = $success = '0';
                            
                            $message = 'You can not extend Date Or Time for this vehicle. As this is assigned already to other inquiry.';
                            
                            $json = array('status_code' => $status_code, 'message' => $message);
                        }

                    }else{
                        $status_code = $success = '0';
                        $message = 'booking detail not valid';
                        
                        $json = array('status_code' => $status_code, 'message' => $message);
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

     //Make Payment
    public function make_expand_date_payment(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $expand_date  = $request->expand_date;
            $expand_time = $request->expand_time;
            $expand_amount = $request->expand_amount;
            $booking_hours = $request->booking_hours;
            $allowed_km = $request->allowed_km;
            $payment_type = $request->payment_type;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($expand_date == ""){
                $error = "Please enter expand date";
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
                        if($payment_type == 'wallet'){
                            $payment_type = 'wallet';
                            $payment_status = 'success';
                        }else{
                            $payment_type = 'paytm';
                            $payment_status = 'pending';
                        }
                        $expand_date = date('Y-m-d',strtotime($expand_date));
                        $expandbooking_id = DB::table('booking_expended')->insertGetId([
                            'booking_id' => $booking_id,
                            'expand_date' => $expand_date,
                            'expand_time' => $expand_time,
                            'expand_amount' => $expand_amount,
                            'expand_km' => $allowed_km,
                            'booking_hours' => $booking_hours,
                            'payment_type' => $payment_type,
                            'payment_status' => $payment_status,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                        if($payment_type != 'wallet'){
                            $enviroment='local';
                            $merchent_id ='FnAoux43246182437237';
                            $merchantKey='2fCkkMtPcbf###hr';
                            $merchantwebsite='WEBSTAGING';
                            $channel='WEB';
                            $industryType='Retail';

                           

                            $paytmParams = array();
                            $orderid = $expandbooking_id;
                            $paytmParams["body"] = array(
                                'requestType' => 'Payment',
                                'mid' => $merchent_id,
                                'websiteName' => 'WEBSTAGING',
                                'orderId' => $orderid,
                                'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." ",
                                'txnAmount'     => array(
                                    'value'     => $expand_amount,
                                    'currency'  => 'INR',
                                ),
                                'userInfo'      => array(
                                    'custId'    => $customer_id,
                                )
                            );

                            /*
                            * Generate checksum by parameters we have in body
                            * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
                            */
                            $payment = PaytmWallet::with('receive');
                            $checksum = $payment->generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);

                            $paytmParams["head"] = array('signature'=>$checksum);

                            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

                            /* for Staging */
                            $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=FnAoux43246182437237&orderId=".$orderid." ";

                            /* for Production */
                            // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";

                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type':'application/json')); 
                            $response = curl_exec($ch);
                            $gettxnarr = json_decode($response);
                            $txnToken = $gettxnarr->body->txnToken;
                            $callbackUrl = "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid;
                         }else{
                            $enviroment = '';
                            $merchent_id = '';
                            $merchantKey = '';
                            $merchantwebsite = '';
                            $channel = '';
                            $industryType = '';
                            $txnToken = '';
                            $callbackUrl = '';
                         }   

                       

                        $status_code = $success = '1';
                        $message = 'Bike Enquiry Booked Successfully';
                            
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id,'expand_booking_id' => $expandbooking_id, 'expand_amount' => $expand_amount , 'booking_hours' => $booking_hours." Hr", 'payment_type' => $payment_type, 'enviroment' => $enviroment, 'mid' => $merchent_id, 'merchantKey' => $merchantKey, 'merchantwebsite' => $merchantwebsite, 'channel' => $channel, 'industryType' => $industryType, "txnToken" => $txnToken, 'callbackUrl' => $callbackUrl );
                    
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

    public function confirm_expanddate_payment(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $expand_booking_id = $request->expand_booking_id;
            $error = "";
           if($expand_booking_id == ""){
                $error = "Please send valid expand booking id.";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    
                    $booking = DB::table('booking_expended')->select('id','expand_amount', 'created_at')->where('booking_id', $booking_id)->where('id', $expand_booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){

                        $status = PaytmWallet::with('status');
                        $status->prepare(['order' => $expand_booking_id]);
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
                            $payment_type = 'paytm';
                            DB::table('booking_expended')->where('id', '=', $expand_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status,  'payment_type' => $payment_type, 'updated_at' => $date]);

                            $is_expended = 'yes';
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['is_expended' => "".$is_expended,  'updated_at' => $date]);

                             
                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Successfully Done.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);  

                        }else if($status->isFailed()){
                          //Transaction Failed
                            
                            $payment_status = 'failed';
                            DB::table('booking_expended')->where('id', '=', $expand_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);


                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Failed.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message); 
                        }else if($status->isOpen()){
                          //Transaction Open/Processing
                            $payment_status = 'pending';
                            DB::table('booking_expended')->where('id', '=', $expand_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);
                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction is Pending / Processing.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);
                        }
                        

                        
                    }else{
                        $status_code = '0';
                        $message = 'Expand date booking id not valid';
                    
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

    //upgrade vehicle filter Result
    public function upgrade_vehicle_filter(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter booking id ";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $booking = DB::table('vehicle_registers')->select('id','user_id','booking_no','customer_name','vehicle_model_id','station', 'booking_status','pick_up','pick_up_time','expected_drop','expected_drop_time')->where('customer_id', $customer_id)->where('vehicle', '=', '')->where('status','In')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
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

                                    $v_list[] = ['id' => (string)$vlist->id, 'vehicle_model' =>$vehicle_model, 'allowed_km_per_hour' =>$allowed_km_per_hour, 'charges_per_hour' =>$charges_per_hour, 'booking_hours' =>$hours, 'charges' =>$charges, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'premium_charges_per_hour' => $premium_charges_per_hour, 'penalty_amount_per_hour' => $penalty_amount_per_hour, 'vehicle_image' => $vehicle_image,'next_booking_time'=>$next_booking_time]; 
                                 }

                                
                                
                                $status_code = $success = '1';
                                $message = 'Upgrade Vehicle Filter Result';
                                
                                $json = array('status_code' => $status_code, 'message' => $message, 'booking_id' => $booking_id, 'center_name' => $station_name, 'vehicle_list' => $v_list);
                            }else{
                                $status_code = $success = '0';
                                $message = 'Vehicle not available right now';
                                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
                            }
                        }else{
                            $status_code = $success = '0';
                            $message = 'Booking detail not found for this booking id';
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);    
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
            //$message = $e->getTraceAsString(); 
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
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
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $bike_model_id = $request->bike_model_id;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 

                     $booking = DB::table('vehicle_registers')->select('id','user_id','booking_no','customer_name','vehicle_model_id','station', 'booking_status','pick_up','pick_up_time','expected_drop','expected_drop_time','booking_hours','allowed_km','total_amount')->where('customer_id', $customer_id)->where('vehicle', '=', '')->where('status','In')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
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

                             /* Get wallet amount */
                            $orderWalletAmt = 0;
                            $expandWalletAmt = 0;
                            $upgradeWalletAmt = 0;

                            $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                            $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');

                            $expandWalletAmt = DB::table('booking_expended as be')->join('vehicle_registers as v', 'be.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('be.payment_type', '=', 'wallet')->where('be.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('be.expand_amount');

                            $upgradeWalletAmt = DB::table('booking_upgrade_bike as up')->join('vehicle_registers as v', 'up.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('up.payment_type', '=', 'wallet')->where('up.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('up.upgrade_amount');
                        
                            $totalwaletamount = "".($walletAmt-($orderWalletAmt+$expandWalletAmt+$upgradeWalletAmt));

                            $wallet_amount = $totalwaletamount;
                            
                            $status_code = $success = '1';
                            $message = 'Upgrade Bike Details';
                            
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id,  'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model,'charges_per_hour' =>$charges_per_hour, 'insurance_charges' => '₹ '.$insurance_charges, 'bike_feature' => $bike_feature, 'helmet_status' => $helmet_status, 'document_status' => $document_status, 'pickup_station' => $station_name, 'booking_time' => $booking_time ,  'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time,  'old_model_booking_amount' => '₹ '.$booking_amount, 'upgrade_bike_model_price' => '₹ '.$total_price, 'differance_amount' => "".$differance_amount, 'wallet_amount' => $wallet_amount, 'booking_hours' => $hours." Hr" );
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


    //Make Payment for upgrade bike
    public function make_payment_upgrade_bike(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $upgrade_model_id = $request->upgrade_model_id;
            $differance_amount = $request->differance_amount;
            $allowed_km = $request->allowed_km;
            $payment_type = $request->payment_type;
            $error = "";
            if($booking_id == ""){
                $error = "Please enter valid booking id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }

            if($upgrade_model_id == ""){
                $error = "Please enter upgrade bike model ";
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
                        
                        if($payment_type == 'wallet'){
                            $payment_type = 'wallet';
                            $payment_status = 'success';
                        }else{
                            if($differance_amount > 0){
                                $payment_type = 'paytm';
                                $payment_status = 'pending';
                            }else{
                                $payment_status = 'success';
                                $payment_type = 'wallet';
                            }    
                        }

                        
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

                        if($payment_type == 'paytm'){

                            $order_id = $upgradeBikebooking_id.'_'.time();
                            //$order_id = $upgradeBikebooking_id;

                            $enviroment='local';
                            $merchent_id ='FnAoux43246182437237';
                            $merchantKey='2fCkkMtPcbf###hr';
                            $merchantwebsite='WEBSTAGING';
                            $channel='WEB';
                            $industryType='Retail';
                            $paytmParams = array();
                            $orderid = $order_id;
                            $paytmParams["body"] = array(
                                'requestType' => 'Payment',
                                'mid' => $merchent_id,
                                'websiteName' => 'WEBSTAGING',
                                'orderId' => $orderid,
                                'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." ",
                                'txnAmount'     => array(
                                    'value'     => round($differance_amount),
                                    'currency'  => 'INR',
                                ),
                                'userInfo'      => array(
                                    'custId'    => $customer_id,
                                )
                            );

                            /*
                            * Generate checksum by parameters we have in body
                            * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
                            */
                            $payment = PaytmWallet::with('receive');
                            $checksum = $payment->generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);

                            $paytmParams["head"] = array('signature'=>$checksum);

                            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

                            /* for Staging */
                            $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=FnAoux43246182437237&orderId=".$orderid." ";

                            /* for Production */
                            // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";

                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type':'application/json')); 
                            $response = curl_exec($ch);
                            $gettxnarr = json_decode($response);
                            $txnToken = $gettxnarr->body->txnToken;

                        ################################################
                            $status_code = $success = '1';
                            $message = 'Bike Model Upgrade Successfully';
                                
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id,'upgrade_booking_id' => $upgradeBikebooking_id, 'differance_amount' => $differance_amount , 'payment_type' => $payment_type, 'enviroment' => $enviroment, 'mid' => $merchent_id, 'merchantKey' => $merchantKey, 'merchantwebsite' => $merchantwebsite, 'channel' => $channel, 'industryType' => $industryType, "txnToken" => $txnToken, 'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." ", 'orderid' => "".$orderid );
                        }else{
                           $status_code = $success = '1';
                           $message = 'Bike Model Upgrade Successfully';
                                
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'booking_id' => $booking_id,'upgrade_booking_id' => $upgradeBikebooking_id, 'differance_amount' => $differance_amount , 'payment_type' => $payment_type, 'enviroment' => '', 'mid' => '', 'merchantKey' => '', 'merchantwebsite' => '', 'channel' => '', 'industryType' => '', "txnToken" => '', 'callbackUrl' => "", 'orderid' => "" ); 
                        }    
                    
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

    public function confirm_upgrade_bike_payment(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $upgrade_bike_booking_id = $request->upgrade_bike_booking_id;
            $orderid = $request->orderid;
            $error = "";
           if($upgrade_bike_booking_id == ""){
                $error = "Please send valid upgrade bike booking id.";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){
                    
                    $booking = DB::table('booking_upgrade_bike')->select('id','upgrade_amount', 'created_at')->where('booking_id', $booking_id)->where('id', $upgrade_bike_booking_id)->orderBy('id', 'DESC')->first();
                    if($booking){

                        $status = PaytmWallet::with('status');
                        $status->prepare(['order' => $orderid]);
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
                            $payment_type = 'paytm';
                            DB::table('booking_upgrade_bike')->where('id', '=', $upgrade_bike_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status,  'payment_type' => $payment_type, 'updated_at' => $date]);

                            $is_upgrade = 'yes';
                            DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['is_upgrade' => "".$is_upgrade,  'updated_at' => $date]);

                             
                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Successfully Done.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);  

                        }else if($status->isFailed()){
                          //Transaction Failed
                            
                            $payment_status = 'failed';
                            DB::table('booking_upgrade_bike')->where('id', '=', $upgrade_bike_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);


                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction Failed.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message); 
                        }else if($status->isOpen()){
                          //Transaction Open/Processing
                            $payment_status = 'pending';
                            DB::table('booking_upgrade_bike')->where('id', '=', $upgrade_bike_booking_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);
                            $status_code = $success = '1';
                            $message = 'Your Booking Transaction is Pending / Processing.';
                        
                            $json = array('status_code' => $status_code, 'message'  => $message);
                        }else{
                            $status_code = '0';
                            $message = 'Paytm order id not valid';
                        
                            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                        }
                        

                        
                    }else{
                        $status_code = '0';
                        $message = 'Upgrade bike model booking id not valid';
                    
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

    public function booking_detail(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $baseUrl = URL::to("/");
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    //$booking = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','register_otp','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','coupon_code','coupon_discount','vehicle', 'booking_status', 'created_at')->where('customer_id', $customer_id)->where('id', $booking_id)->where('payment_status', 'success')->orderBy('id', 'DESC')->first();
                    
                    $booking = DB::table('vehicle_registers')->select('id','booking_no','customer_name','phone','register_otp','pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle_model_id','total_amount','coupon_code','coupon_discount','vehicle', 'is_expended', 'is_upgrade', 'booking_status', 'created_at')->where('customer_id', $customer_id)->where('id', $booking_id)->orderBy('id', 'DESC')->first();
                    
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
                             $upgrade_vehicle_model_id = DB::table('booking_upgrade_bike')->where('booking_id', $booking->id)->where('payment_status', 'success')->pluck('vehicle_model_id')[0];
                             $vehicle_model = DB::table('vehicle_models')->where('id', $upgrade_vehicle_model_id)->pluck('model')[0];
                        }else{
                            $vehicle_model = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('model')[0];
                        }
                         $vehicle_image = DB::table('vehicle_models')->where('id', $booking->vehicle_model_id)->pluck('vehicle_image')[0];
                         $bike_image = '';
                         if($vehicle_image){
                            $bike_image = $baseUrl."/public/".$vehicle_image; 
                         }

                         if($booking->booking_status == 1){
                            $booking_status = 'Open';
                         }else if($booking->booking_status == 0){
                            $booking_status = 'Canceled'; 
                         }else if($booking->booking_status == 2){
                               $booking_status = 'Completed';     
                         }

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

                          /* Get wallet amount */
                                $orderWalletAmt = 0;
                                $expandWalletAmt = 0;
                                $upgradeWalletAmt = 0;

                                $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                                $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');

                                $expandWalletAmt = DB::table('booking_expended as be')->join('vehicle_registers as v', 'be.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('be.payment_type', '=', 'wallet')->where('be.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('be.expand_amount');

                                $upgradeWalletAmt = DB::table('booking_upgrade_bike as up')->join('vehicle_registers as v', 'up.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('up.payment_type', '=', 'wallet')->where('up.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('up.upgrade_amount');
                            
                                $totalwaletamount = "".($walletAmt-($orderWalletAmt+$expandWalletAmt+$upgradeWalletAmt));

                                $wallet_amount = $totalwaletamount;

                        $status_code = '1';
                        $message = 'My Bookings List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'id' => "".$booking->id, 'bike_image' => $bike_image, 'booking_no' => $booking->booking_no, 'customer_name' => $booking->customer_name, 'phone' => "".$booking->phone, 'booking_otp' => "".$booking->register_otp, 'pick_up_date' => date('d-m-Y', strtotime($booking->pick_up)), 'pick_up_time' => $booking->pick_up_time, 'expected_drop_date' => date('d-m-Y', strtotime($booking->expected_drop)), 'expected_drop_time' => $booking->expected_drop_time, 'center_name' => $booking->station, 'vehicle_model' => $vehicle_model, 'vehicle_number' => $booking->vehicle, 'coupon_code' => $booking->coupon_code, 'booking_amount' => $booking->total_amount, 'wallet_amount' => $wallet_amount, 'customer_penalty' => $customer_penalty, 'total_amount' => "".$total_amount, 'booking_date' => date('d-m-Y H:i:s', strtotime($booking->created_at)), 'booking_status' => $booking_status, 'vehicle_image_before_ride' => $booked_vehicle_before_list, 'vehicle_image_after_ride' => $booked_vehicle_after_list, 'is_expended' => $booking->is_expended, 'extendhistory' => $extendhistory, 'is_upgrade' => $booking->is_upgrade,'upgradeBikehistory' => $upgradeBikehistory  );
                    }else{
                         $status_code = '0';
                        $message = 'No booking found.';
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

    // Cancel Booking reason  
    public function canceledBooking(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $booking_id = $request->booking_id;
            $reason = $request->reason;  
            $error = "";
            if($booking_id == ""){
                $error = "Please enter booking id";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $today = date('Y-m-d');
                    $current_time = date('H:i:s');
                    $chkbooking = DB::table('vehicle_registers')->where('id', $booking_id)->wheredate('pick_up', '>=', $today)->where('pick_up_time', '<=', $current_time)->first();
                    if($chkbooking){
                        $booking_status = 0;
                        DB::table('vehicle_registers')->where('id', '=', $booking_id)->update(['booking_status' => $booking_status, 'cancel_date' => $date, 'cancel_reason' => $reason, 'updated_at' => $date]);
                        $status_code = $success = '1';
                        $message = 'Your booking canceled successfully';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
                    }else{
                         $status_code = $success = '0';
                        $message = 'You can not cancel this booking as pickup time is over.';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id);
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

                    $enviroment='local';
                    $merchent_id ='FnAoux43246182437237';
                    $merchantKey='2fCkkMtPcbf###hr';
                    $merchantwebsite='WEBSTAGING';
                    $channel='WEB';
                    $industryType='Retail';
                    $paytmParams = array();
                        $orderid = $order_id;
                        $paytmParams["body"] = array(
                            'requestType' => 'Payment',
                            'mid' => $merchent_id,
                            'websiteName' => 'WEBSTAGING',
                            'orderId' => $orderid,
                            'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." ",
                            'txnAmount'     => array(
                                'value'     => $amount,
                                'currency'  => 'INR',
                            ),
                            'userInfo'      => array(
                                'custId'    => $customer_id,
                            )
                        );

                        /*
                        * Generate checksum by parameters we have in body
                        * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
                        */
                        $payment = PaytmWallet::with('receive');
                        $checksum = $payment->generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);

                        $paytmParams["head"] = array('signature'=>$checksum);

                        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

                        /* for Staging */
                        $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=FnAoux43246182437237&orderId=".$orderid." ";

                        /* for Production */
                        // $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type':'application/json')); 
                        $response = curl_exec($ch);
                        $gettxnarr = json_decode($response);
                        $txnToken = $gettxnarr->body->txnToken;

                    $status_code = '1';
                    $message = 'Wallet Amount';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'order_id' => $order_id, 'amount' => $amount, 'enviroment' => $enviroment, 'mid' => $merchent_id, 'merchantKey' => $merchantKey, 'merchantwebsite' => $merchantwebsite, 'channel' => $channel, 'industryType' => $industryType, "txnToken" => $txnToken, 'callbackUrl' => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderid." "); 
                    
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
                     /* Get wallet amount */
                    $orderWalletAmt = 0;
                    $expandWalletAmt = 0;
                    $upgradeWalletAmt = 0;

                    $walletAmt = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->where('isactive', '=', '1')->where('payment_status', '=', 'success')->sum('amount');

                    $orderWalletAmt = DB::table('vehicle_registers')->where('customer_id', $customer_id)->where('payment_type', '=', 'wallet')->where('payment_status', '=', 'success')->sum('total_amount');

                    $expandWalletAmt = DB::table('booking_expended as be')->join('vehicle_registers as v', 'be.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('be.payment_type', '=', 'wallet')->where('be.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('be.expand_amount');

                    $upgradeWalletAmt = DB::table('booking_upgrade_bike as up')->join('vehicle_registers as v', 'up.booking_id', '=', 'v.id')->where('v.customer_id', $customer_id)->where('up.payment_type', '=', 'wallet')->where('up.payment_status', '=', 'success')->where('v.payment_status', '=', 'success')->sum('up.upgrade_amount');
                
                    $totalwaletamount = '₹ '.($walletAmt-($orderWalletAmt+$expandWalletAmt+$upgradeWalletAmt));

                    $wallet_amount = $totalwaletamount;


                    
                    $status_code = '1';
                    $message = 'Total Wallet Amount';
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'wallet_amount' => $wallet_amount); 
                    
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

     public function wallet_history(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $walletPaymentExists = DB::table('customer_wallet_payments')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->count();
                    $wallet_List = array();
                    if($walletPaymentExists > 0){
                        $walletList = DB::table('customer_wallet_payments')->select('id','amount','comment','payment_status','created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();

                        
                        foreach($walletList as $rswallet)
                        {
                            
                            $wallet_List[] = array('id' => "".$rswallet->id, 'comment' => $rswallet->comment,'payment_status' => $rswallet->payment_status, 'amount' => "".$rswallet->amount, 'date' => date('d-m-Y H:i:s', strtotime($rswallet->created_at))); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'wallet History';
                        $json = array('status_code' => $status_code,  'message' => $message, 'wallet_history' => $wallet_List);
                    }else{
                         $status_code = '0';
                        $message = 'No history found.';
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

    public function home_notification_list(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $notificationExists = DB::table('notifications')->where('user_type', 'home')->where('notification_type', 'home-screen')->orderBy('id', 'DESC')->count();
                    $notify_List = array();
                    if($notificationExists > 0){
                        $notifyList = DB::table('notifications')->select('id','notification_title','notification_content','notification_type','created_at')->where('user_type', 'home')->where('notification_type', 'home-screen')->orderBy('id', 'DESC')->get();

                        
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
            $current_date = date('Y-m-d H:i:s');
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
                    $referCouponList = DB::table('customer_referal_coupons')->select('id','customer_id','coupon_code', 'discount','description')->where('customer_id', $customer_id)->where('status', 'Live')->wheredate('start_date',' > ',$current_date)->wheredate('end_date',' <= ',$current_date)->whereNotIn('coupon_code', $usedCouponList)->orderBy('id', 'ASC')->get();
                    $coupon_list = array();
                    if($referCouponList){
                        foreach($referCouponList as $couponlist)
                        {
                            
                            $coupon_list[] = array('coupon_code' => "".$couponlist->coupon_code, 'discount_type' => 'percentage', 'discount' => $couponlist->discount, 'description' => $couponlist->description); 
                           
                        }
                    } 

                    $generalCouponList = DB::table('coupons')->select('id','title','discount_type','discount','description')->where('status', 'Live')->wheredate('start_date',' > ',$current_date)->wheredate('end_date',' <= ',$current_date)->whereNotIn('title', $usedCouponList)->orderBy('id', 'ASC')->get();
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

    public function need_help(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $faqs = DB::table('faqs')->where('status', 'Live')->orderBy('id', 'ASC')->get();

                    $faq_List = array();
                    if($faqs){
                        foreach($faqs as $rs)
                        {
                            
                            $faq_List[] = array('id' => "".$rs->id, 'question' => $rs->title, 'answer' => $rs->description, 'date' => date('d-m-Y H:i:s', strtotime($rs->created_at))); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Need Help';
                        $json = array('status_code' => $status_code,  'message' => $message, 'help_list' => $faq_List);
                    }else{
                         $status_code = '0';
                        $message = 'No help data found.';
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
    

    //Add Support Query
    public function add_support_query(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $title = $request->title;
            $description = $request->comment;
            $error = "";
            if($description == ""){
                $error = "Please enter comment for feedback";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    
                    $supportid = DB::table('customer_supports')->insertGetId(['customer_id' => $customer_id, 'title' => $title, 'description' => $description, 'status' => 'open', 'created_at' => $date, 'updated_at' => $date]);

                    $ticket_no = "STKT".date('YmdHis').str_pad($supportid, 3, "0", STR_PAD_LEFT);
            
                    DB::table('customer_supports')->where('id', '=', $supportid)->update(['ticket_no' => "".$ticket_no, 'updated_at' => $date]);
                    $status_code = $success = '1';
                    $message = 'Your Support Ticket ('.$ticket_no.') created successfully';
                    
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

     public function ticket_history(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $tickets = DB::table('customer_supports')->where('customer_id', $customer_id)->where('status', 'solved')->orderBy('id', 'ASC')->get();
                    $ticket_List = array();
                    if($tickets){
                        foreach($tickets as $rs)
                        {
                            $answer = '';
                            if($rs->answer){
                                $answer = $rs->answer;
                            }
                            $ticket_List[] = array('id' => "".$rs->id, 'ticket_no' => $rs->ticket_no, 'question' => $rs->title, 'comment' => $rs->description, 'answer' => $answer, 'status' => $rs->status, 'date' => date('d-m-Y H:i:s', strtotime($rs->created_at))); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Customer Ticket List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'ticket_List' => $ticket_List);
                    }else{
                         $status_code = '0';
                        $message = 'No ticket data found.';
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

    public function policies(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 
                    $policies = DB::table('policies')->where('status', 'Live')->orderBy('id', 'ASC')->get();

                    $policies_List = array();
                    if($policies){
                        foreach($policies as $rs)
                        {
                            
                            $policies_List[] = array('id' => "".$rs->id, 'title' => $rs->title, 'description' => $rs->description, 'date' => date('d-m-Y H:i:s', strtotime($rs->created_at))); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Policies';
                        $json = array('status_code' => $status_code,  'message' => $message, 'policies_list' => $policies_List);
                    }else{
                         $status_code = '0';
                        $message = 'No policies data found.';
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
