<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_gallery', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('title');
            $table->string('image')->nullable();
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
        Schema::dropIfExists('vehicle_gallery');
    }
}
