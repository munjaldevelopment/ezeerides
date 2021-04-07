<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookedVehicleImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booked_vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('booking_id');
            $table->string('title');
            $table->string('image')->nullable();
            $table->enum('image_type', ['Before Ride', 'After Ride'])->default('Before Ride');
            $table->enum('status', ['Live', 'Not Live'])->default('Not Live');
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
        Schema::dropIfExists('booked_vehicle_images');
    }
}
