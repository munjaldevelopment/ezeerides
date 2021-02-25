<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Mail;
use Input;
use Redirect;
use Session;
use Validator;
use App\User;

use App\Models\Vehicle;
use App\Models\Station;

use Illuminate\Support\Facades\Hash;


class HomeController extends Controller
{
	public function dashboard()
    {
		$customer_name = session()->get('ezeerides_name');
		$customer_user_id = session()->get('ezeerides_user_id');
		
		if(!$customer_name)
		{
			return redirect(url('/'));
		}
		else
		{
			// Show vehicle
			$vehicles = Vehicle::leftJoin("model_has_vehicles", "vehicles.id", "=", "model_has_vehicles.vehicle_id")->where("user_id", $customer_user_id)->get();
			$stations = Station::leftJoin("model_has_stations", "stations.id", "=", "model_has_stations.station_id")->where("user_id", $customer_user_id)->get();

			return view('dashboard', ['customer_name' => $customer_name, 'vehicles' => $vehicles, 'stations' => $stations]);
		}
	}
	
	public function postLogin(Request $request)
	{
		$rules = array (
			'email' => 'required',
			'password' => 'required',
		);
		$validator = Validator::make ( Input::all (), $rules );
		if ($validator->fails ()) {
			return Redirect::back ()->withErrors ( $validator, 'login' )->withInput ();
		}
		else
		{
			if($request->email != "")
			{
				$checkRecord = \DB::table('users')->where(['email' => $request->email])->first();
					
				if($checkRecord)
				{
					$user_id = $checkRecord->id;
					
					//echo $request->email.",".$request->password; exit;
					
					if (Auth::attempt ( array (
						'email' => $request->email,
						'password' => $request->password
					) )) {
						$user = User::findOrFail($user_id);
						
						if($user) {
							// Find User ID
							
							session ( [
								'ezeerides_name' => $checkRecord->email,
								'ezeerides_user_id' => $user_id
							] );
							
							return redirect(url('/dashboard'));
						} else {
							Session::flash ( 'message', "Invalid Credentials, Please try again." );
							return Redirect::back ();
						}
					} else {
						Session::flash ( 'message', "Invalid Credentials, Please try again." );
						return Redirect::back ();
					}
				} else {
					Session::flash ( 'message', "Email not exists. Please try again." );
					return Redirect::back ();
				}
			}
		}
	}
}
