<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->integer('model');
            $table->string('description')->nullable();
            $table->string('allowed_km_per_hour')->nullable();
            $table->decimal('charges_per_hour',10, 2);
            $table->decimal('insurance_charges_per_hour',10, 2);
            $table->decimal('penalty_amount_per_hour',10, 2);
            $table->string('vehicle_image')->nullable();
            $table->enum('status', ['done', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_models');
    }
}
