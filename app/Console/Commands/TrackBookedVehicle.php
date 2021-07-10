<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
use App;

use App\Mail\SendMail;

class TrackBookedVehicle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:vehiclepermin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get gps location of booked vehicle in every minute';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $booked_vehicleList = DB::table('vehicle_registers')->select('id','user_id','booking_no','customer_id', 'customer_name', 'vehicle_model_id', 'pick_up','pick_up_time','expected_drop','expected_drop_time','station','vehicle','status','receive_date','is_amount_receive','is_upgrade')->where('vehicle','!=','')->where('status','Out')->where('booking_status','1')->where('is_amount_receive','0')->orderBy('id', 'asc')->get();
        if($booked_vehicleList){
            $error = '';
            foreach ($booked_vehicleList as $vlist) {
                $vehicle_number = $vlist->vehicle;
                $booking_id = $vlist->id;

                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => 'http://13.127.228.11/webservice?token=getLiveData&vehicle_no='.$vehicle_number.'&format=json',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_HTTPHEADER => array(
                    'Cookie: JSESSIONID=5139FFE98C395187DB84A00C18508065'
                  ),
                ));

                $response = curl_exec($curl);
                $trackdetail = json_decode($response);
                curl_close($curl);
                //print_r($trackdetail);
                
                if(isset($trackdetail->root->error)){
                }else{    
                    $vehicle_No = $trackdetail->root->VehicleData[0]->Vehicle_No;
                    $vehicle_Name = $trackdetail->root->VehicleData[0]->Vehicle_Name;
                    $vehicletype = $trackdetail->root->VehicleData[0]->Vehicletype;
                    $imeino = $trackdetail->root->VehicleData[0]->Imeino;
                    $deviceModel = $trackdetail->root->VehicleData[0]->DeviceModel;
                    $location = $trackdetail->root->VehicleData[0]->Location;
                    $datetime = $trackdetail->root->VehicleData[0]->Datetime;
                    $latitude = $trackdetail->root->VehicleData[0]->Latitude;
                    $longitude = $trackdetail->root->VehicleData[0]->Longitude;
                    $status = $trackdetail->root->VehicleData[0]->Status;
                    $speed = $trackdetail->root->VehicleData[0]->Speed;
                    $gps = $trackdetail->root->VehicleData[0]->GPS;
                    $ignission = $trackdetail->root->VehicleData[0]->IGN;
                    $power = $trackdetail->root->VehicleData[0]->Power;
                    $fuel = $trackdetail->root->VehicleData[0]->Fuel;
                    $odometer = $trackdetail->root->VehicleData[0]->Odometer;

                    $track_vehicle = DB::table('booking_vehicle_gpstrack_log')->insert([
                        'booking_id' => $booking_id,
                        'vehicle_No' => $vehicle_No,
                        'vehicle_Name' => $vehicle_Name,
                        'vehicletype' => $vehicletype,
                        'imeino' => $imeino,
                        'deviceModel' => $deviceModel,
                        'location' => $location,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'status' => $status,
                        'speed' => $speed,
                        'gps' => $gps,
                        'ignission' => $ignission,
                        'power' => $power,
                        'fuel' => $fuel,
                        'odometer' => $odometer,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
             }   
        } 
        $this->info('Successfully tracked vehicle detail.'); 
    }
}
