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
                        
                       $smsmessage = str_replace(" ", "%20", "Dear Customer, Your verify OTP is ".$otp.". Please DO NOT share OTP with anyone.");
                        
                         $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=jmvd&password=jmvd&senderid=OALERT&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");
                    
                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => "".$otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                       
                        //$refer_url = "https://play.google.com/store/apps/details?id=com.microprixs.krishimulya&referrer=krvrefer".$customerid;
                        
                        $status_code = '1';
                        $message = 'Customer login OTP Send';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' =>"".$customerid, "customer_type" => "already" , 'otp' => "".$otp);
                    }else{
                        $otp = rand(11111, 99999);
                        
                       $smsmessage = str_replace(" ", "%20", "Dear Customer, Your verify OTP is ".$otp.". Please DO NOT share OTP with anyone.");
                        
                         $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=jmvd&password=jmvd&senderid=OALERT&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");
                    
                        DB::table('customers')->where('id', '=', $customerid)->update(['otp' => $otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'updated_at' => $date]);

                        $status_code = $success = '1';
                        $message = 'Customer Otp Send, Please Process Next Step';
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => "".$customerid, 'mobile' => $mobile, "customer_type" => "already", 'otp' => "".$otp);
                    }
                        
                   
                }else{
                		
                    $otp = rand(11111, 99999);
                    $smsmessage = str_replace(" ", "%20", "Dear Customer, Your verify OTP is ".$otp.". Please DO NOT share OTP with anyone.");
                        
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=jmvd&password=jmvd&senderid=OALERT&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");

                    $customerid = DB::table('customers')->insertGetId(['mobile' => $mobile, 'otp' => "".$otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'created_at' => $date, 'status' => 'Not live',  'updated_at' => $date]); 

                    $date   = date('Y-m-d H:i:s');
                    /*if($refer_code != ""){
                        $referCustomerid = str_replace('krvrefer', '', $refer_code); 
                        $referal_customer_id = $referCustomerid;
                        $refercustomerid = DB::table('customer_refer_register')->insertGetId(['customer_id' => $customerid, 'referal_customer_id' => $referal_customer_id, 'created_at' => $date]);    
                        
                    }*/

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
        return $head;
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

                    //$customerid = DB::table('customers')->insertGetId(['mobile' => $mobile, 'otp' => $otp, 'device_id' => $device_id, 'fcmToken' => $fcmToken, 'created_at' => $date, 'updated_at' => $date, 'status' => 'Live']); 
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

                     //DB::table('customers_tmp')->where('mobile', $mobile)->delete();
                    
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
                    
                    $smsmessage = str_replace(" ", "%20", "Dear Customer, Your verify OTP is ".$otp.". Please DO NOT share OTP with anyone.");
                        
                    $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=jmvd&password=jmvd&senderid=OALERT&channel=TRANS&DCS=0&flashsms=0&number=".$mobile."&text=".$smsmessage."&route=15");


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

    


    //Rent IN Result
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
            $from_date = date("Y-m-d",strtotime($request->from_date));
            $to_date = date("Y-m-d",strtotime($request->to_date));
            $error = "";
            if($center == ""){
                $error = "Please enter center for ride";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', 'Live')->first();
                if($customer){ 

                    $vehicleList = DB::table('vehicles as v')->join('vehicle_models as vm', 'v.vehicle_model', '=', 'vm.id')->join('station_has_vehicles as sv', 'v.id', '=', 'sv.vehicle_id')->select('vm.id','vm.model','vm.allowed_km_per_hour','vm.charges_per_hour','vm.insurance_charges_per_hour', 'vm.penalty_amount_per_hour','vm.vehicle_image')->where('v.status','Live')->groupBy('vm.id');

                    if($center){
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
                    
                    $bikeDetail = DB::table('vehicle_models')->where('id', $bike_model_id)->where('status', '=', 'Live')->first();
                    $bike_feature = array();
                    if($bikeDetail){ 
                        $vehicle_model = $bikeDetail->model;
                        $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour.' KM';
                        $excess_km_charges = '₹ 0 / KM';
                        $charges_per_hour = '₹ '.$bikeDetail->charges_per_hour;
                        $bikecharges = $bikeDetail->charges_per_hour;
                        $insurance_charges_per_hour = '₹ '.$bikeDetail->insurance_charges_per_hour;
                        $insurance_charges = $bikeDetail->insurance_charges_per_hour;
                        $penalty_amount_per_hour = '₹ '.$bikeDetail->penalty_amount_per_hour;
                        $helmet_charges = '₹ 0';
                        $helmet_status = '1';
                        $document_status = '1';

                        $bike_feature = ['Allowed KM' => $allowed_km_per_hour, 'Excess KM Charges' => $excess_km_charges, 'Number of Helmet (2)' => $helmet_charges , 'Insurance for your Ride' => $insurance_charges_per_hour , 'Documents Status ' => $document_status];
                        
                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];

                        $booking_time = $from_date."-".$to_date;

                        $start_trip_date = date('d-m-Y',strtotime($from_date));
                        $start_trip_time = date('H:i',strtotime($from_date));
                        $end_trip_date = date('d-m-Y',strtotime($to_date));
                        $end_trip_time = date('H:i',strtotime($to_date));

                        $end_trip_time = date('H:i',strtotime($to_date));



                        $total_price = $bikecharges+$insurance_charges;

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
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'city_id' => $city_id , 'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model, 'bike_feature' => $bike_feature, 'helmet_status' => $helmet_status, 'document_status' => $document_status, 'pickup_station' => $station_name, 'booking_time' => $booking_time ,  'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time, 'total_price' => $total_price);
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
            $message = $e->getTraceAsString();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => '');
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
                            $notification_type = "";
                            
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
