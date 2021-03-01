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

        $pick_up1 = date('Y-m-d H:i:s', strtotime($pick_up));
        $expected_drop1 =  date('Y-m-d h:i:s', strtotime($expected_drop));
        
        $station = $request->station;
        $vehicle = $request->vehicle;
        $total_amount = $request->total_amount;

        $customer_name = $request->customer_name;
        $phone = $request->phone;

        // Insert Data
        $register = new VehicleRegister;
        $register->user_id = $customer_user_id;
        $register->customer_name = $customer_name;
        $register->phone = $phone;
        $register->pick_up = $pick_up1;
        $register->expected_drop = $expected_drop1;
        $register->station = $station;
        $register->vehicle = $vehicle;
        $register->total_amount = $total_amount;
        $register->punchout_time = date('Y-m-d H:i:s');
        $register->status = 'Out';
        $register->save();
        
        return back()->with('success', 'Register successfully!');
    }
}