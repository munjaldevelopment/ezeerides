<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class HistoryController extends BaseController
{
    public function Index()
    {
		$customer_name = session()->get('ezeerides_name');
		$customer_user_id = session()->get('ezeerides_user_id');

		if(!$customer_name)
		{
			return redirect(url('/'));
		}
		else
		{
			$base_url = env('APP_URL');
			$historyData = DB::table('vehicle_registers')->where("user_id", $customer_user_id)->get();

			return view('history', ['customer_name' => $customer_name, 'base_url' => $base_url, 'histories' => $historyData]);
		}
    }
}