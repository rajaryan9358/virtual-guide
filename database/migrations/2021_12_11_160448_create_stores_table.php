<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->nullable();
            $table->string('store_phone')->unique();
            $table->string('store_profile')->nullable();
            $table->string('otp')->nullable();
            $table->string('token')->nullable();
            $table->string('mobile')->nullable();
            $table->string('store_address')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->string('store_location')->nullable();
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
        Schema::dropIfExists('stores');
    }
}
