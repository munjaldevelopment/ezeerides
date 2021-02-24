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
        $HistoryResult = DB::table('vehicle_registers')->get();
        echo "<table style='border-collapse: collapse;border:1px solid;border-collapse: collapse;width:100%;'>";
           echo "<tr><th>ID</th>"; 
           echo "<th>Customer Name</th>"; 
           echo "<th>Phone</th>"; 
           echo "<th>Pick Up</th>"; 
           echo "<th>Drop</th>"; 
           echo "<th>Vehicle</th>"; 
           echo "<th>Status</th>"; 
           echo "<th>Dropping</th>"; 
           echo "<th>Total Amount</th>"; 
           echo "<th>Station</th></tr>"; 
        foreach ($HistoryResult as $history) {
           echo "<tr><td style='border:1px solid;'>".$history->id."</td>";
           echo "<td style='border:1px solid;'>".$history->customer_name."</td>";
           echo "<td style='border:1px solid;'>".$history->phone."</td>";
           echo "<td style='border:1px solid;'>".$history->pick_up."</td>";
           echo "<td style='border:1px solid;'>".$history->drop_time."</td>";
           echo "<td style='border:1px solid;'>".$history->vehicle."</td>";
           echo "<td style='border:1px solid;'>".$history->status."</td>";
           echo "<td style='border:1px solid;'>".$history->dropping."</td>";
           echo "<td style='border:1px solid;'>".$history->total_amount."</td>";
           echo "<td style='border:1px solid;'>".$history->station."</td></tr>";
        }
        echo "</table>";   
    }
}