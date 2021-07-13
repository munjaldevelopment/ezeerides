<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class VehicleRegister extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'vehicle_registers';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    
    protected $fillable = ['user_id', 'customer_name', 'phone', 'register_otp', 'pick_up', 'pick_up_time', 
    'expected_drop', 'expected_drop_time', 'station', 'vehicle', 'total_amount', 'punchout_time', 'return_time', 'additional_hours', 'additional_amount', 'status', 'booking_status', 'is_amount_receive', 'receive_date'];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function getFleetFare($hours = 0,$vehicle_amount=0)
    {
        if($hours > 4)
        {
            if($hours < 24){
                $firstFourAmount = ($vehicle_amount * 1.5) * 4;
                $diff = $hours - 4;
                $dayTotalAmount = $diff * $vehicle_amount;
                $amount = $firstFourAmount + $dayTotalAmount;
            
            }else if($hours == 24){
                
                $firstFourAmount = ($vehicle_amount * 1.5) * 4; 
                $diff = 17;
                $dayTotalAmount = $diff * $vehicle_amount;
                $amount = $firstFourAmount + $dayTotalAmount;

            }else if($hours  > 24 && $hours < 48){
                
                $firstFourAmount = ($vehicle_amount * 1.5) * 4; 
                $diff = 17;
                $dayTotalAmount = $diff * $vehicle_amount;

                $extraDays = ($hours - 24) * $vehicle_amount;
                
                $amount = $firstFourAmount + $dayTotalAmount + $extraDays;      

            }else if($hours  >= 48 && $hours < 72){
                
                $firstFourAmount = ($vehicle_amount * 1.5) * 4; 
                $diff = 17;
                $dayTotalAmount = $diff * $vehicle_amount;

                $diff = 21;
                $nextDayTotal = $diff * $vehicle_amount;

                $reminaingHour = ($hours - 48) * $vehicle_amount;

                
                $amount = $firstFourAmount + $dayTotalAmount + $nextDayTotal + $reminaingHour;

            }else if($hours  >= 72){
                
                $firstFourAmount = ($vehicle_amount * 1.5) * 4; 
                $diff = 17;
                 $dayTotalAmount = $diff * $vehicle_amount;

                $diff = 21;
                $daycount = floor($hours/24);
                $nextDayTotal = ($diff * $vehicle_amount) * ($daycount-1);

                $reminaingHour = ($hours - ($daycount*24)) * $vehicle_amount;

                
                $amount = $firstFourAmount + $dayTotalAmount + $nextDayTotal + $reminaingHour;          
            }

        }else{
            $hours = 4;
            $amount = $hours * ($vehicle_amount * 1.5);
        }

        return $amount;         
    }

    public function getBookedKM($hours = 0,$allowed_kmperhour=0)
    {
        //echo $hours; 
        if($hours >= 24)
        {
            $hourdiffer = ($hours%24);
            if($hourdiffer == 0){
                $bookedkm = ($hours * $allowed_kmperhour)-($hourdiffer*4*$allowed_kmperhour);
            }else{
                /*if($hourdiffer < 4){
                    $hourdiffer = 4;
                }*/
                
                $bookedkm = ($hours * $allowed_kmperhour)-($hourdiffer*$allowed_kmperhour);
            }
        }else if($hours < 4){
            $hours = 4;
            $bookedkm = $hours * $allowed_kmperhour;
        }else{
            $bookedkm = $hours * $allowed_kmperhour;
        }

        return $bookedkm;         
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
