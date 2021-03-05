<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\VehicleRegister;
use App\OtpVerify;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class RegisterController extends BaseController
{
    public function saveReturn(Request $request)
    {
        /*dd($request->all());

        "return_id" => "3"
        "customer_name" => "Munjal Mayank"
        "phone" => "9999999999"
        "pick_up" => "01-Jan-2021 14:10"
        "expected_drop" => "01-Jan-2021 15:10"
        "station" => "Railway station"
        "vehicle" => "RJ14JJ7497"
        "total_amount" => "19.50"
        "punchout_time" => "01-Mar-2021 15:16"
        "additional_hours" => "1"
        "additional_amount" => "10"*/

        $return_time = date('Y-m-d H:i:s', strtotime($request->return_time));
        $additional_hours = $request->additional_hours;
        $additional_amount = $request->additional_amount;

        $updateData = array('return_time' => $return_time, 'additional_hours' => $additional_hours, 'additional_amount' => $additional_amount, 'status' => 'In');

        $updateEntry = \DB::table('vehicle_registers')->where('id', $request->return_id)->update($updateData);

        return redirect(url('/history'))->with('success', 'Return successfully!');

    }

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

    public function saveBookingVerify(Request $request)
    {
        $record = \DB::table('vehicle_registers')->where('id', $request->booking_id)->where('register_otp', $request->user_otp)->count();

        if($record)
        {
            $updateData = array('booking_status' => '1');

            $updateEntry = \DB::table('vehicle_registers')->where('id', $request->booking_id)->update($updateData);

            return redirect(url('/history'));
        }
        else
        {
            return redirect(url('booking_verify/'.$request->booking_id));
        }
    }
    

    public function RegisterResult(Request $request)
    {
        /*array:8 [
          "customer_name" => "Munjal Mayank"
          "phone" => "9462045321"
          "pick_up" => "01-Mar-2021 13:52"
          "expected_drop" => "02-Mar-2021 14:52"
          "station" => "Railway station"
          "vehicle" => "RJ14JJ5432"
          "total_amount" => "461.5"
        ]*/

        $customer_name = session()->get('ezeerides_name');
        $customer_user_id = session()->get('ezeerides_user_id');

        $pick_up = $request->pick_up;
        $expected_drop = $request->expected_drop;

        $pick_up_time = $request->pick_up_time;
        $expected_drop_time = $request->expected_drop_time;

        $pick_up1 = date('Y-m-d', strtotime($pick_up));
        $expected_drop1 =  date('Y-m-d', strtotime($expected_drop));

        $pick_up_time1 = date('H:i:s', strtotime($pick_up_time));
        $expected_drop_time1 =  date('H:i:s', strtotime($expected_drop_time));
        
        $station = $request->station;
        $vehicle = $request->vehicle;
        $total_amount = $request->total_amount;

        $customer_name = $request->customer_name;
        $phone = $request->phone;

        $otp = rand(111111, 999999);

        // Insert Data
        $register = new VehicleRegister;
        $register->user_id = $customer_user_id;
        $register->customer_name = $customer_name;
        $register->phone = $phone;
        $register->pick_up = $pick_up1;
        $register->register_otp = $otp;
        $register->expected_drop = $expected_drop1;
        $register->pick_up_time = $pick_up_time1;
        $register->expected_drop_time = $expected_drop_time1;
        $register->station = $station;
        $register->vehicle = $vehicle;
        $register->total_amount = $total_amount;
        $register->punchout_time = date('Y-m-d H:i:s');
        $register->status = 'Out';
        $register->save();

        $message = str_replace(" ", "%20", "your OTP is ".$otp);
        $this->httpGet("http://opensms.microprixs.com/api/mt/SendSMS?user=jmvd&password=jmvd&senderid=OALERT&channel=TRANS&DCS=0&flashsms=0&number=".$phone."&text=".$message."&route=15");
        
        return redirect(url('booking_verify/'.$register->id));
    }
}