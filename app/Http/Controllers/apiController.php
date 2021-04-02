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
                           $customer_image  =  $baseUrl."/public/uploads/customer_image/user.png";
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
                    $json = array('status_code' => $status_code,  'message' => $message, 'customer_id' => "".$customerid, 'mobile' => $mobile, 'referurl' => $refer_url, 'name' => $name, 'email' => $email, 'city_id' => $city_id, 'city_name' => $city_name, 'station_id' => $station_id, 'station_name' => $station_name, 'address' => $address, 'customer_image' => $customer_image, 'profile_status' => $profile_status);
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
                   $customer_image  =  $baseUrl."/public/uploads/customer_image/user.png";
                }
                
                
                $status_code = $success = '1';
                $message = 'Customer Profile Info';
                
                $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id , 'name' => $name, 'email' => $email, 'dob' => $dob, 'mobile' => $mobile, 'address' => $address , 'city_id' => $city_id ,'city_name' => $city_name ,'station_id' => $station_id ,'station_name' => $station_name , 'customer_image' => $customer_image);


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
                    }
                    if($city_id == ''){
                        $city_id = '0';
                    }
                    if($station_id == ''){
                        $station_id = '0';
                    }
                    DB::table('customers')->where('id', '=', $customer_id)->update(['name' => $name, 'dob' => $dob, 'email' => $email, 'address' => $address, 'city_id' => $city_id, 'station_id' => $station_id, 'image' => 'uploads/customer_image/'.$customerimage, 'updated_at' => $date]);
                    
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
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'city_name' => $city_name, 'station_name' => $station_name, 'vehicle_list' => $v_list);
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
                    if($bikeDetail){ 
                        $vehicle_model = $bikeDetail->vehicle_model;
                        $allowed_km_per_hour = $bikeDetail->allowed_km_per_hour;
                        $charges_per_hour = $bikeDetail->charges_per_hour;
                        $insurance_charges_per_hour = $bikeDetail->insurance_charges_per_hour;
                        $penalty_amount_per_hour = $bikeDetail->penalty_amount_per_hour;

                        $station_name = DB::table('stations')->where('id', $station_id)->pluck('station_name')[0];

                        $booking_time = $from_date."-".$to_date;

                        $start_trip_date = date('d-m-Y',strtotime($from_date));
                        $start_trip_time = date('H:i',strtotime($from_date));
                        $end_trip_date = date('d-m-Y',strtotime($to_date));
                        $end_trip_time = date('H:i',strtotime($to_date));

                        $end_trip_time = date('H:i',strtotime($to_date));

                        $total_price = $charges_per_hour+$insurance_charges_per_hour;

                        $baseUrl = URL::to("/");
                        $vehicle_image  = "";
                        if($bikeDetail->vehicle_image){
                            $vehicle_image  =  $baseUrl."/public/".$bikeDetail->vehicle_image;
                        
                        }
                        
                        $bikegallery = DB::table('vehicle_galleries')->where('vehicle_id', $bike_id)->where('status', '=', 'Live')->get();
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
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'city_id' => $city_id , 'center_id' => $station_id , 'vehicle_image' => $vehicle_image, 'vehicle_gallery' => $bgallery, 'vehicle_model' => $vehicle_model, 'vehicle_model' => $vehicle_model, 'charges_per_hour' => $charges_per_hour, 'insurance_charges_per_hour' => $insurance_charges_per_hour, 'pickup_station' => $station_name, 'booking_time' => $booking_time , 'allow_km' => $allowed_km_per_hour, 'penalty_amount' => $penalty_amount_per_hour, 'start_trip_date' => $start_trip_date, 'start_trip_time' => $start_trip_time,'end_trip_date' => $end_trip_date, 'end_trip_time' => $end_trip_time, 'total_price' => $total_price);
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

    //Purchase Old Enquiry
    public function purchase_old_results(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $what_need = $request->what_need;
            $location = $request->location;
            $other_city = $request->other_city;
            $company_name = $request->company_name;
            $other_company = $request->other_company;
            $hourse_power = $request->hourse_power;
            $model = $request->model;
            $year_manufacturer = $request->year_manufacturer;
            $error = "";
            if($what_need == ""){
                $error = "Please select what need to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $purchaseOldList = DB::table('tractor_sell_enquiry')->select('id','customer_id','name','mobile','company_name','other_company','model','hourse_power','hrs', 'exp_price', 'image','sale_type','location', 'other_city')->where('isactive', '=', 1);

                    if($what_need){
                        $purchaseOldList = $purchaseOldList->where('sale_type',$what_need);    
                    }

                    if($company_name){
                        $purchaseOldList = $purchaseOldList->where('company_name',$company_name);    
                    }

                    if($other_company){
                        $purchaseOldList = $purchaseOldList->where('other_company',$other_company);    
                    }

                    if($location){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($model){
                        $purchaseOldList = $purchaseOldList->orWhere('model',$model);    
                    }

                    if($year_manufacturer){
                        $purchaseOldList = $purchaseOldList->orWhere('year_manufacturer',$year_manufacturer);    
                    }

                    if($other_city){
                        $purchaseOldList = $purchaseOldList->where('other_city',$other_city);    
                    }

                    if($hourse_power){
                        $hparr = explode('-', $hourse_power);
                        $hpfrom = $hparr[0];
                        $hpto = $hparr[1];
                        //$purchaseOldList = $purchaseOldList->whereBetween('hourse_power', [$hpfrom, $hpto]);
                        $purchaseOldList = $purchaseOldList->where('hourse_power','LIKE',$hourse_power);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            
                         
                            $customer_name = $plist->name;
                            $customer_telphone = $plist->mobile;
                            $baseUrl = URL::to("/");
                            $tractor_image  = "";
                            if($plist->image){
                                $tractor_image  =  $baseUrl."/public/uploads/tractor_image/".$plist->image;
                            
                            }
                            $other_company = ($plist->other_company != '') ? $plist->other_company : "";
                            $othercity = ($plist->other_city != '') ? $plist->other_city : "";
                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'company_name' =>$plist->company_name, 'other_company' =>$other_company, 'what_need' =>$plist->sale_type, 'location' =>$plist->location, 'other_city' =>$othercity, 'model' => $plist->model, 'hourse_power' => $plist->hourse_power, 'hrs' => $plist->hrs, 'exp_price' => $plist->exp_price, 'image' => $tractor_image]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Old Purchase enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Item for purchase not available right now';
                    
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

    public function all_purpose(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            

             $purposeType = DB::table('purpose_type')->select('title as name')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();


           /* $purposeType[] = array('name' => "Farming work");
            $purposeType[] = array('name' => "Non-Farming work");*/
            
            
            $status_code = '1';
            $message = 'purpose type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'purposeType' => $purposeType);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function all_labour_need(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $needType = DB::table('labour_type')->select('title as name')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();
           
           /* $needType[] = array('name' => "Normal");
            $needType[] = array('name' => "Urgent");*/
            
            
            $status_code = '1';
            $message = 'labour need type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'needType' => $needType);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Labour Enquiry
    public function labour_enquiry(Request $request)
    {
       header('Content-Type: text/html; charset=utf-8');
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $location = $request->location;
            $other_city = $request->other_city;
            $purpose = $request->purpose;
            $need = $request->need;
            $labour_no = $request->labour_no;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";

            if($labour_no == ""){
                $error = "Please enter no of labour";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('labour_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'purpose' => $purpose, 'need' => $need, 'labour_no' => $labour_no, 'comments' => $comments,  'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Labour enquiry added successfully';
                    
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


     //Rent IN Result
    public function labour_result(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $labour_no = $request->labour_no;
            $need = $request->need;
            $location = $request->location;
            $other_city = $request->other_city;
            $purpose = $request->purpose;
            //$available_date = date("Y-m-d",strtotime($request->available_date));
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $labourList = DB::table('labour_enquiry')->select('id','customer_id','location', 'other_city', 'purpose','labour_no','comments')->where('isactive', '=', 1);

                    if($labour_no){
                        $labour_noto = 0;
                        $labourList = $labourList->where('labour_no','>=',$labour_no);    
                        //$labourList = $labourList->whereBetween('labour_no', [$labour_noto, $labour_no]);
                    }

                    if($location){
                        $labourList = $labourList->where('location','LIKE',$location);    
                    }
                    if($other_city){
                        $labourList = $labourList->where('other_city','LIKE',$other_city);    
                    }

                    if($purpose){
                        $labourList = $labourList->where('purpose','LIKE',$purpose);    
                    }

                    if($need){
                        $labourList = $labourList->where('need','LIKE',$need);    
                    }

                    /*if($available_date){
                        $rentinList = $rentinList->wheredate('available_date',$available_date);    
                    }*/
                    $labourList = $labourList->orderBy('id', 'desc')->get(); 
                    if(count($labourList) >0){
                        $r_list = array();
                        foreach($labourList as $rlist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $rlist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $othercity = ($rlist->other_city != '') ? $rlist->other_city : "";
                            //$available_date = date("d-m-Y",strtotime($rlist->available_date));
                            $r_list[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'location' =>$rlist->location, 'other_city' =>$othercity, 'labour_no' => $rlist->labour_no, 'purpose' => $rlist->purpose, 'comment' => $rlist->comments]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Labour result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'rentin_list' => $r_list);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Labour not available right now';
                    
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

     //START show feed list 
    public function insurance_type(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
            //$insTypList = array('1' => "Tractor",'2' => "Equipment");
            
            $insTypList = DB::table('insurance_type')->select('id','title')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();

            $status_code = '1';
            $message = 'Insurance Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'insTypList' => $insTypList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

    //Labour Enquiry
    public function insurance_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $insurance_type = $request->insurance_type;
            $other_insurance_type = $request->other_insurance_type;
            $comments = $request->comments;
            $isactive = 1;
            $error = "";
            if($insurance_type == ""){
                $error = "Please enter insurance type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                    DB::table('insurance_enquiry')->insert(['customer_id' => $customer_id, 'name' => $name, 'mobile' => $mobile, 'insurance_type' => $insurance_type, 'other_insurance_type' => $other_insurance_type, 'comments' => $comments, 'user_type' => 'customer', 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Insurance enquiry added successfully';
                    
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

     //START show feed list 
    public function land_type(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            
             $landTypeList = DB::table('land_type')->select('title as name')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();

           /* $landTypeList[] = array('name' => "agriculture");
            $landTypeList[] = array('name' => "non-agriculture");
            */
            $status_code = '1';
            $message = 'Land Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'landTypeList' => $landTypeList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }
    //END 

     public function all_land_size(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
             $landsize = DB::table('land_size')->select('title as name')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();
            
            /*$landsize[] = array('name' => "1-3 acre");
            $landsize[] = array('name' => "3-5 acre");
            $landsize[] = array('name' => "5-8 acre");
            $landsize[] = array('name' => "8-10 acre");
            $landsize[] = array('name' => "10-12 acre");
            $landsize[] = array('name' => "12-15 acre");
            $landsize[] = array('name' => "15-20 acre");
            $landsize[] = array('name' => "20-25 acre");
            $landsize[] = array('name' => "25-50 acre");*/
            
           
            
            $status_code = '1';
            $message = 'land Size list';
            $json = array('status_code' => $status_code,  'message' => $message, 'landsize' => $landsize);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    public function all_rent_time(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
             $rent_time = DB::table('rent_time')->select('title as name')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();
           
           /* $rent_time[] = array('name' => "1-2 Year");
            $rent_time[] = array('name' => "2-5 Year");
            $rent_time[] = array('name' => "5-8 Year");
            $rent_time[] = array('name' => "8-10 Year");
            $rent_time[] = array('name' => "10-12 Year");
            $rent_time[] = array('name' => "12-15 Year");
            $rent_time[] = array('name' => "15-20 Year");
           */ 
           
            
            $status_code = '1';
            $message = 'Rent Time list';
            $json = array('status_code' => $status_code,  'message' => $message, 'rent_time' => $rent_time);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

    //Agri Land Rent Enquiry
    public function agri_land_rent_enquiry(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;
            $how_much_time = $request->how_much_time;
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location for tractor";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('agriland_rent_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acore' => $size_in_acre, 'how_much_time' => $how_much_time,   'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri land rent enquiry added successfully';
                    
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

    //Purchase Old Enquiry
    public function agriland_rent_results(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $rent_time = $request->rent_time;
            $error = "";
            if($location == ""){
                $error = "Please select location to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $rentListquery = DB::table('agriland_rent_enquiry')->select('id','customer_id','land_type','size_in_acore','how_much_time','comment', 'location','other_city')->where('isactive', '=', 1);

                    if($land_type){
                        $rentListquery = $rentListquery->where('land_type',$land_type);    
                    }

                    if($location){
                        $rentListquery = $rentListquery->where('location',$location);    
                    }

                    if($other_city){
                        $rentListquery = $rentListquery->where('other_city',$other_city);    
                    }

                    if($size_in_acre){
                        $rentListquery = $rentListquery->where('size_in_acore',$size_in_acre);    
                    }

                    if($rent_time){
                        $rentListquery = $rentListquery->where('how_much_time','LIKE',$rent_time);    
                    }

                   
                    $rentListquery = $rentListquery->orderBy('id', 'desc')->get(); 

                    if(count($rentListquery) >0){
                        $r_List = array();
                        foreach($rentListquery as $rlist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $rlist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $pimage = '';
                            $othercity = ($rlist->other_city != '') ? $rlist->other_city : "";
                            $r_List[] = ['id' => (string)$rlist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$rlist->land_type, 'location' => $rlist->location, 'other_city' => $othercity, 'size_in_acre' => $rlist->size_in_acore, 'rent_time' => $rlist->how_much_time, 'comment' => $rlist->comment]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Agri land Rent enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'sale_rent_list' => $r_List);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Agri land for rent not available right now';
                    
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

    //Agri Land Sale Enquiry
    public function agri_land_sale_enquiry(Request $request)
    {
        try 
        {
          // header('Content-Type: text/html; charset=UTF-8');
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $comment = $request->comment;
           //print_r($request->all(), 1);
            //exit;
            //$exp_price = $request->exp_price;
            $exp_price = 0;
            $isactive = 1;
            $error = "";
            if($location == ""){
                $error = "Please enter location";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('agriland_sale_enquiry')->insert(['customer_id' => $customer_id, 'location' => $location, 'other_city' => $other_city, 'land_type' => $land_type, 'size_in_acre' => $size_in_acre, 'exp_price' => $exp_price, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Agri land sale enquiry added successfully';
                    
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

    //Agri land Purchase Old Enquiry
    public function agriland_purchase_result(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_type = $request->land_type;
            $location = $request->location;
            $other_city = $request->other_city;
            $size_in_acre = $request->size;
            $error = "";
            if($location == ""){
                $error = "Please select location to search";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 

                    
                    
                    $purchaseOldList = DB::table('agriland_sale_enquiry')->select('id','customer_id','land_type','size_in_acre','comment', 'location','other_city')->where('isactive', '=', 1);

                    if($land_type){
                        $purchaseOldList = $purchaseOldList->where('land_type',$land_type);    
                    }

                    if($location){
                        $purchaseOldList = $purchaseOldList->where('location',$location);    
                    }

                    if($other_city){
                        $purchaseOldList = $purchaseOldList->where('other_city',$other_city);    
                    }

                    if($size_in_acre){
                        $purchaseOldList = $purchaseOldList->where('size_in_acre','LIKE',$size_in_acre);    
                    }

                   
                    $purchaseOldList = $purchaseOldList->orderBy('id', 'desc')->get(); 

                    if(count($purchaseOldList) >0){
                        $purchaseList = array();
                        foreach($purchaseOldList as $plist)
                        {
                            
                            $rscustomer = DB::table('customers')->where('id', $plist->customer_id)->first();
                            $customer_name = $rscustomer->name;
                            $customer_telphone = $rscustomer->telephone;
                            $pimage = '';
                            $othercity = ($plist->other_city != '') ? $plist->other_city : "";

                            $purchaseList[] = ['id' => (string)$plist->id, 'customer_name' =>$customer_name, 'customer_telphone' =>$customer_telphone, 'land_type' =>$plist->land_type, 'size_in_acre' => $plist->size_in_acre, 'location' => $plist->location, 'other_city' => $othercity, 'comment' => $plist->comment]; 
                        }

                        $status_code = $success = '1';
                        $message = 'Agri land Purchase enquiry result';
                        
                        $json = array('status_code' => $status_code, 'message' => $message, 'purchase_list' => $purchaseList);
                    }else{
                        $status_code = $success = '0';
                        $message = 'Agri land for purchase not available right now';
                    
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

    //START show feed list 
    public function feedList(Request $request)
    {
        try 
        {   
            $baseUrl = URL::to("/");
            $json       =   array();
            $language = $request->language;
            $customer_id = $request->customer_id;
            $pincode = '';
            if($customer_id){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $pincode = $customer->pincode;
                }
            }       
                    
            if($pincode ==""){
                $pincode = '302022';
            }
            //$user_id    =   $request->user_id;
            //$role_id    =   $request->role_id;
            //$is_emp     =   (int)$request->is_emp;
        
			// 2 = process one, 3 = process two, 4 = process three, 5= process four, 6 = process complete, 9 = planning
			//echo $role_id; exit;
			
            $feedList = array();
            $rsfeeds = DB::table('feeds')->where('language', $language)->where('status', '=', 'PUBLISHED')->orderBy('id', 'DESC')->get();
			
            if(count($rsfeeds) >0)
            {
                foreach($rsfeeds as $showFeed)
                {
					$feedimage  =  $baseUrl."/public/".$showFeed->image;
                    $feedcat = DB::table('feed_categories')->where('id', $showFeed->category_id)->first();
                    $feed_catname = $feedcat->name;
					$feedList[] = ['id' => (int)$showFeed->id, 'heading' =>$feed_catname, 'title' =>$showFeed->title, 'content' => strip_tags($showFeed->content), 'date' => date("d-m-Y",strtotime($showFeed->date)), 'feedimage' => $feedimage]; //'planning_isprogress' => $planning_isprogress, 
                }
                $appurl = 'api.openweathermap.org/data/2.5/weather?zip='.$pincode.',IN&units=metric&appid=acfd0186948c7adf0c9c87a2ebcc004b';
                $wheatherRespone = $this->httpGet($appurl);
                
                $wheather = json_decode($wheatherRespone);
                //print_r($wheather->main);
                //print_r($wheather->weather[0]);
                $mainval =  $wheather->weather[0]->main;
                $wheatherType =  $wheather->weather[0]->description;
                $wheathericon =  $wheather->weather[0]->icon;
                $todaytemp =  $wheather->main->temp;
                $todayhumidity =  $wheather->main->humidity;
                $todayhumidity =  $wheather->main->humidity;
                $locationName =  $wheather->name;
                $iconurl = "http://openweathermap.org/img/w/" . $wheathericon . ".png";
                $status_code = '1';
                $message = 'Show feed list';
                $apptext = 'Krishimulya | कृषिमूल्य';
                $json = array('status_code' => $status_code,  'message' => $message, 'apptext' => $apptext, 'processList' => $feedList, 'pincode' => $pincode, 'wheatherType' => $wheatherType, 'wheathericon' => $iconurl, 'todaytemp' => "".$todaytemp."°C" , 'todayhumidity' => "".$todayhumidity, 'locationName' => "".$locationName);
            }
            else
            {
                $status_code = '0';
                $message = 'Sorry! no feed exists .';
                $json = array('status_code' => $status_code,  'message' => $message);
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

    //Agri Land Feedback
    public function agriland_feedback(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $comment = $request->comment;
            $isactive = 1;
            $error = "";
            if($comment == ""){
                $error = "Please enter comment for feedback";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('feedback')->insert(['customer_id' => $customer_id, 'comment' => $comment, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);

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

     public function enquiry_type(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $enquiryTypeList[] = array('name' => "Rent");
            $enquiryTypeList[] = array('name' => "Sale");
            $enquiryTypeList[] = array('name' => "Purchase");
            $enquiryTypeList[] = array('name' => "Agriland Rent");
            $enquiryTypeList[] = array('name' => "Agriland Purchase");
            $enquiryTypeList[] = array('name' => "Labour");
            $enquiryTypeList[] = array('name' => "Insurance");
            
            $status_code = '1';
            $message = 'Enquiry Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'enquiryTypeList' => $enquiryTypeList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    }

   //Agri Land Feedback
    public function enquiry_tracking(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $enquiry_type = $request->enquiry_type;
            $error = "";
            if($enquiry_type == ""){
                $error = "Please enter enquiry type";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                    DB::table('enquiry_tracking')->insert(['customer_id' => $customer_id, 'enquiry_type' => $enquiry_type, 'created_at' => $date, 'updated_at' => $date]);

                    $status_code = $success = '1';
                    $message = 'Enquiry Type added successfully';
                    
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

     public function soiltest_type(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $soiltestTypList = DB::table('soil_test_type')->select('id','title','price')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();


            $status_code = '1';
            $message = 'Enquiry Type list';
            $json = array('status_code' => $status_code,  'message' => $message, 'soiltestTypList' => $soiltestTypList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    } 

     public function get_sevakendra(Request $request)
    {
        try 
        {   
            
            $json       =   array();
            $language = $request->language;
            
            $sevaKendraList = DB::table('seva_kendra')->select('id','name','contact_no','location','city','email' ,'latitude','langitude')->where('isactive', '=', 1)->orderBy('id', 'ASC')->get();


            $status_code = '1';
            $message = 'Seva Kendra list';
            $json = array('status_code' => $status_code,  'message' => $message, 'sevakendraList' => $sevaKendraList);
        }
        catch(\Exception $e) {
            $status_code = '0';
            $message = $e->getMessage();//$e->getTraceAsString(); getMessage //
    
            $json = array('status_code' => $status_code, 'message' => $message);
        }
    
        return response()->json($json, 200);
    } 

    //Agri Land Sale Enquiry
    public function create_soiltest_order(Request $request)
    {
        try 
        {
            $json = $userData = array();
            
            $date   = date('Y-m-d H:i:s');
            $customer_id = $request->customer_id;
            $land_size = $request->land_size;
            $location = $request->location;
            $khasra_no = $request->khasra_no;
            $test_type = $request->test_type;
            $amount = $request->amount;
            //$comments = $request->comment;
            //$exp_price = $request->exp_price;
            $order_status = 'pending';
            $isactive = 1;
            $error = "";
            if($test_type == ""){
                $error = "Please enter valid data.";
                $json = array('status_code' => '0', 'message' => $error, 'customer_id' => $customer_id);
            }
            
            if($error == ""){
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    
                  
                   /* get order no */
                   $maxorderno = DB::table('soil_test_orders')->select('id','order_no')->where('isactive', '=', 1)->orderBy('id', 'DESC')->first();
                   //print_r($maxorderno);
                  
                   if(!empty($maxorderno) && $maxorderno->id != '') {
                        
                        if($maxorderno->order_no != ''){
                            $orderno = $maxorderno->order_no;
                            
                            $orderno = $orderno+1;
                        }else{
                            $orderno = 1;
                        }
                        
                   }else{
                        $orderno = 1;
                   }
                  
                    $order_no = str_pad($orderno, 3, "0", STR_PAD_LEFT);
                    $name = $customer->name;
                    $mobile = $customer->telephone;
                   $orderid = DB::table('soil_test_orders')->insertGetId(['customer_id' => $customer_id,'order_no' => $order_no, 'name' => $name, 'mobile' => $mobile, 'land_size' => $land_size, 'location' => $location, 'khasra_no' => $khasra_no, 'test_type' => $test_type, 'amount' => $amount, 'order_status' => $order_status, 'isactive' => $isactive, 'created_at' => $date, 'updated_at' => $date]);
                   //DB::table('soil_test_orders')->where('id', '=', $orderid)->update(['order_no' => $order_no]);
                   
                   /* FCM Notification */
                   $customerToken = $customer->fcmToken; 
                   //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                   $customerName = $customer->name; 
                   $notification_title = "Soil Test Order";
                   $notification_body = $order_no." Your soil test order has been successfully created! Thanks for order with us.";
                   $notification_type = "soil_order";
                   $notif_data = array($notification_title,$customerName,$notification_body,"","");
                
                   $customerNotify = $this->push_notification($notif_data,$customerToken);
                   $saveNotification = DB::table('tbl_notification')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                   /* End */
                    $status_code = $success = '1';
                    $message = 'Soil Test Order Added Successfully';
                    
                    $json = array('status_code' => $status_code, 'message' => $message, 'customer_id' => $customer_id, 'order_id' => "".$orderid);


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

    public function get_customer_soilOdr(Request $request)
    {
        try 
        {   
             $baseUrl = URL::to("/");
            $json       =   array();
            $customer_id = $request->customer_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrExists = DB::table('soil_test_orders')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->count();

                    if($soilodrExists >0){
                        $soilodrList = DB::table('soil_test_orders')->select('id','order_no','name', 'mobile', 'amount','land_size','location','khasra_no','test_type','report_file','order_status','created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();

                        $odr_List = array();
                        $testype_name = "";
                        foreach($soilodrList as $odrlist)
                        {
                            //$soiltest_type = DB::table('soil_test_type')->where('id', $odrlist->test_type)->first();
                            $testype_name = $odrlist->test_type; 
                            /*if($odrlist->report_file != ''){
                                
                                $report_file = $baseUrl."/public/order_report/".$odrlist->report_file;
                            }else{
                                $report_file =  $baseUrl."/public/order_report/dummy.pdf";
                            }*/
                            
                            if($odrlist->order_status == 'done'){
                                $report_file =  $baseUrl."/public/order_report/".$odrlist->report_file;
                            }else{
                                $report_file = '';
                            }                           
                            if($odrlist->khasra_no != ''){
                                $khasra_no = $odrlist->khasra_no;
                            }else{
                                $khasra_no = "";
                            }
                            $odr_List[] = array('id' => "".$odrlist->id, 'order_no' => $odrlist->order_no, 'name' => $odrlist->name, 'mobile' => $odrlist->mobile, 'testypeName' => $testype_name,'amount' => "".$odrlist->amount, 'land_size' => $odrlist->land_size, 'location' => $odrlist->location, 'khasra_no' => $khasra_no, 'report_file' => $report_file, 'date' => date('d-m-Y H:i:s', strtotime($odrlist->created_at)),'order_status' => $odrlist->order_status); 
                           
                        } 

                        //print_r($odr_List);
                        //exit;
                        $status_code = '1';
                        $message = 'Soil Order List';
                        $json = array('status_code' => $status_code,  'message' => $message, 'odr_List' => $odr_List);
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

    public function updateOdrTestType(Request $request)
    {
        try 
        {   
            
            $json      =   array();
            $customer_id = $request->customer_id;
            $order_id = $request->order_id;
            $test_type = $request->test_type;
            $amount = $request->amount;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $customer_id)->where('id', $order_id)->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                         DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['test_type' => $test_type, 'amount' => $amount, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $customerToken = $customer->fcmToken; 
                       $customerName = $customer->name; 
                       $notification_title = "Soil Test Order";
                       $notification_body = $soilodrList->order_no." Your soil test order has been successfully Updated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('tbl_notification')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Type Updated';
                        $json = array('status_code' => $status_code,  'message' => $message, 'test_type' => $test_type, 'amount' => $amount , 'order_id' => $order_id, 'customer_id' => $customer_id);
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

    public function create_soil_report(Request $request)
    {

         $soilodr = DB::table('soil_test_orders')->join('customers', 'customers.id', '=', 'soil_test_orders.customer_id')->where('soil_test_orders.kt_report_id', "!=", '')->where('soil_test_orders.order_status', 'pending')->select('soil_test_orders.*', 'customers.fcmToken')->get();
         
        if(count($soilodr) > 0){
            foreach ($soilodr as $order) {
                $kt_report_id = $order->kt_report_id; 
                $order_no = $order->order_no; 
                $order_id = $order->id; 
                $customer_id = $order->customer_id; 
                if($kt_report_id != ''){
                  

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://soil-api-staging.krishitantra.com/graphql',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>'{"query":"query Query {\\r\\n  getTest(id:\\"'.$kt_report_id.'\\"){id,html}\\r\\n}\\r\\n","variables":{}}',
                      CURLOPT_HTTPHEADER => array(
                        'authorization: bearer:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJvcmdhbml6YXRpb24iOiI2MDM4YmE5MTMwYThjZDAwMTIwMDExMGQiLCJ1c2VyIjoiNjAzOGJiNDgzMGE4Y2QwMDEyMDAxMTBlIiwiaWF0IjoxNjE1MjExODk2fQ.NWkhgpGIZY6Ty60CShFoJhGp5pbHgwjIJnziAJ06TBk',
                        'Content-Type: application/json'
                      ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);
                    
                    $res =  json_decode($response);
                   /* echo '<pre>';
                    print_r($res);*/
                    $replace = '<img src="http://krishimulya.com/uploads/logo/512-png-short.png" width="50" height="50">';
                    $reporthtml =  str_replace('krishimulya', $replace, $res->data->getTest->html);
                   if($reporthtml != ''){ 
                       $pdf = App::make('dompdf.wrapper');
                       $pdf->loadHTML($reporthtml);
                       $pdf->setPaper('a4', '')->setWarnings(false);
                       $filename = $order_no.'_report.pdf';
                       $pdf->save(public_path().'/order_report/'.$filename);

                       /*Update Soil order */ 
                        $date   = date('Y-m-d H:i:s');
                        $order_status = 'done';
                        DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['report_file' => $filename, 'order_status' => $order_status, 'report_date' => $date]);
                       /* End */
                      /* FCM Notification */
                       $customerToken = $order->fcmToken; 
                       
                       //$customerToken = 'e2k1jCT_Ty2qOLk4gSX_Hz:APA91bHXhqvz5KlPW6EW9vDNeldzJR-yQcIarygjgn8fo2b08ihcEIFiu-NzHI-1A3L7MJMYyI4ehSWzBwimX5T0ExRbooa6-UxGrfckSdD-F49FzJxwWcU4M58qRu8yeRduTk62eBMW';
                       $customerName = $order->name; 
                       $notification_title = "Soil Test Report";
                       $notification_body = $order_no." Your soil test report has been successfully created! Thanks for soil test with us.";
                       $notification_type = "soil_order_report";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                       $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('tbl_notification')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */
                    }
                }
            }
            $status_code = $success = '1';
            $message = 'Successfully sent soil report to customers';
            $json = array('status_code' => $status_code, 'message' => $message); 
            
        }else{
            $status_code = $success = '0';
            $message = 'Soil report not found for any customers';
            $json = array('status_code' => $status_code, 'message' => $message);
        }
        return response()->json($json, 200);
    }  



    public function orderReportCreated(Request $request)
    {
        try 
        {   
            
            $json      =   array();
            $customer_id = $request->customer_id;
            $order_id = $request->order_id;
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilodrList = DB::table('soil_test_orders')->select('id','order_no')->where('customer_id', $customer_id)->where('id', $order_id)->orderBy('id', 'DESC')->first();

                    if($soilodrList){
                        $date   = date('Y-m-d H:i:s');
                        $order_status = 'done';
                         DB::table('soil_test_orders')->where('id', '=', $order_id)->update(['order_status' => $order_status, 'updated_at' => $date]);
                        
                        /* FCM Notification */
                       $customerToken = $customer->fcmToken; 
                       $customerName = $customer->name; 
                       $notification_title = "Soil Test Report Created";
                       $notification_body = $soilodrList->order_no." Your soil test order report has been successfully generated! Thanks for order with us.";
                       $notification_type = "soil_order";
                       $notif_data = array($notification_title,$customerName,$notification_body,"","");
                    
                        $customerNotify = $this->push_notification($notif_data,$customerToken);
                       $saveNotification = DB::table('tbl_notification')->insertGetId(['customer_id' => $customer_id,'notification_title' => $notification_title, 'notification_content' => $notification_body, 'notification_type' => $notification_type, 'user_type' => 'customer', 'isactive' => '1', 'created_at' => $date, 'updated_at' => $date]);

                       /* End */    
                        $status_code = '1';
                        $message = 'Soil Order Test Report Created';
                        $json = array('status_code' => $status_code,  'message' => $message, 'order_status' => $order_status, 'order_id' => $order_id, 'customer_id' => $customer_id);
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
            $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                if($customer){ 
                    $soilnotificationExists = DB::table('tbl_notification')->where('customer_id', $customer_id)->where('user_type', 'customer')->orderBy('id', 'DESC')->count();
                    $notify_List = array();
                    if($soilnotificationExists >0){
                        $soilNotifyList = DB::table('tbl_notification')->select('id','notification_title','notification_content','notification_type','created_at')->where('customer_id', $customer_id)->orderBy('id', 'DESC')->get();

                        
                        foreach($soilNotifyList as $notifylist)
                        {
                            $notification_type = "";
                            if($notifylist->notification_type == 'soil_order'){

                                $notification_type = 'Soil Order';
                            }

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


        public function todayWheateher(Request $request)
        {
            try 
            {   
                
                $json = array();
                $customer_id = $request->customer_id;
                $pincode = $request->pincode;
                $customer = DB::table('customers')->where('id', $customer_id)->where('status', '=', '1')->first();
                    if($customer){

                        $appurl = 'api.openweathermap.org/data/2.5/weather?zip='.$pincode.',IN&units=metric&appid=acfd0186948c7adf0c9c87a2ebcc004b';
                        $wheatherRespone = $this->httpGet($appurl);
                        
                        $wheather = json_decode($wheatherRespone);
                        print_r($wheather);
                        //print_r($wheather->weather[0]);
                        $mainval =  $wheather->weather[0]->main;
                        $wheatherType =  $wheather->weather[0]->description;
                        $todaytemp =  $wheather->main->temp;
                        $todayhumidity =  $wheather->main->humidity;
                        $locationName =  $wheather->name;
                        $status_code = $success = '1';
                        $message = 'Today Wheather';
                        $json = array('status_code' => $status_code, 'message' => $message, 'wheatherType' => $wheatherType, 'todaytemp' => "".$todaytemp."°C" , 'todayhumidity' => "".$todayhumidity,'locationName' => "".$locationName);
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
    
}
