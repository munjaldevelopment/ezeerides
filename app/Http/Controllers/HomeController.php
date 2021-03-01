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
	public function showVehicle(Request $request)
	{
		$customer_user_id = session()->get('ezeerides_user_id');

		$station_name = $request->station_id;

		$output = '<option value="">Select</option>';
		if($station_name != "")
		{
			$stationInfo = Station::where('station_name', '=', $station_name)->first();

			if($stationInfo)
			{
				$station_id = $stationInfo->id;
				$stationsVehicle = \DB::table('station_has_vehicles')->leftJoin("vehicles", "station_has_vehicles.vehicle_id", "=", "vehicles.id")->where("station_id", $station_id)->get();

				if($stationsVehicle)
				{
					foreach($stationsVehicle as $row)
					{
						// Check if already assigned
						$isExists = \DB::table('vehicle_registers')->where("user_id", $customer_user_id)->where("vehicle", $row->vehicle_number)->where('status', 'Out')->count();

						if($isExists == 0)
						{
							$output.= '<option data_model="'.$row->vehicle_number.'" value="'.$row->vehicle_number.'" data-charge="'.$row->charges.'">'.$row->vehicle_number.$row->vehicle_model.'</option>';
						}
					}
				}
			}
		}

		return $output;
	}

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
			$base_url = env('APP_URL');
			$stations = Station::leftJoin("model_has_stations", "stations.id", "=", "model_has_stations.station_id")->where("user_id", $customer_user_id)->get();
			$today = date('Y-m-d H:i');


			return view('dashboard', ['customer_name' => $customer_name, 'base_url' => $base_url, 'stations' => $stations, 'today' => $today]);
		}
	}

	public function returnVehicle($return_id)
    {
		$customer_name = session()->get('ezeerides_name');
		$customer_user_id = session()->get('ezeerides_user_id');
		
		if(!$customer_name)
		{
			return redirect(url('/'));
		}
		else
		{
			$record = \DB::table('vehicle_registers')->find($return_id);

			if($record)
			{
				// Show vehicle
				$base_url = env('APP_URL');
				$today = date('Y-m-d H:i');

				return view('return_vehicle', ['customer_name' => $customer_name, 'base_url' => $base_url, 'registerRecord' => $record, 'today' => $today]);
			}
			else
			{
				return redirect(url('/history'));
			}
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
