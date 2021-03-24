<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_service', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('description')->nullable();
            $table->integer('service_by')->nullable();
            $table->decimal('service_amount',10, 2);
            $table->datetime('service_date')->nullable();
            $table->datetime('next_date')->nullable();
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
        Schema::dropIfExists('vehicle_service');
    }
}
