<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationPremiumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_premiums', function (Blueprint $table) {
            $table->id();
            $table->string('city_id');
            $table->string('station_id');
            $table->datetime('from_date')->nullable();
            $table->datetime('to_date')->nullable();
            $table->string('charges')->nullable();
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
        Schema::dropIfExists('station_premiums');
    }
}
