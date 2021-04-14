<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class VehicleService extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'vehicle_service';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function allVehicle()
    {
        return $this->belongsTo('App\Models\Vehicle', 'vehicle_id');
    }

    public function roles()
    {
        return $this->belongsTo(config('permission.models.role'), 'service_by');
    }

    public function all_service_type()
    {
        return $this->belongsToMany('App\Models\ServiceType', 'services_has_vehicles');
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

    public function GetVehicleModelAttribute()
    {
        $vehicle_model = $this->allVehicle()->first()->vehicle_model;
        $modelName = \DB::table('vehicle_models')->where('id', $vehicle_model)->pluck('model')[0];
        return $modelName;
    }
}
