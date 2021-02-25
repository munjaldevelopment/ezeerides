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
    public function RegisterResult(Request $request)
    {
        $customer_name = session()->get('ezeerides_name');
        $customer_user_id = session()->get('ezeerides_user_id');

        /*Dropping Time*/
        $pick_up = $request->pick_up;
        $drop_time = $request->drop_time;
        $pick_up1 = date('Y-m-d H:i:s', strtotime($pick_up));
        
        $droppingval =  date('Y-m-d h:i:s', strtotime($pick_up1.'+'.$drop_time));
        
        $vehicle = $request->vehicle;
        
        $masterData = DB::table('vehicles')->where('vehicle_number', $vehicle)->first();

        if ($masterData && $drop_time == '7') {
            $total_amount =  $masterData->charges * $drop_time * 21 * 30 /100;
        }
        elseif($masterData && $drop_time == '15')
        {
            $total_amount =  $masterData->charges * $drop_time * 21 * 30 /100;
        }
        elseif ($masterData && $drop_time == '30') {
            $total_amount =  $masterData->charges * $drop_time * 21 * 60 /100;
        }
        else{
            $total_amount =  $masterData->charges * $drop_time;   
        }
        
       
        $data = array('customer_name' => $request->customer_name, 'phone' => $request->phone, 'pick_up' => $request->pick_up, 'drop_time' => $request->drop_time, 'vehicle' => $request->vehicle, 'proof' => $request->proof, 'status' => $request->status, 'dropping' => $droppingval, 'total_amount' => $total_amount, 'station' => $request->station);

       
        //print_r($dropping);
        $register = new VehicleRegister;
        $register->user_id = $customer_user_id;
        $register->customer_name = $data['customer_name'];
        $register->phone = $data['phone'];
        $register->pick_up = date('Y-m-d h:i:s', strtotime($data['pick_up']));
        $register->drop_time = $data['drop_time'];
        $register->vehicle = $data['vehicle'];
        $register->proof = $data['proof'];
        $register->status = 'Out';
        $register->dropping = $data['dropping'];
        $register->total_amount = $data['total_amount'];
        $register->station = $data['station'];
        //dd($data);exit;
        $register->save();
        return back()->with('success', 'Register successfully!');
    }
}