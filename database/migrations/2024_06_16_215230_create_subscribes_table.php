<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscribesTable extends Migration
{

    public function up()
    {
        Schema::create('subscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->boolean('benefit')->default(0);
            $table->boolean('volunteering')->default(0);
            $table->boolean('request_status_vol')->default(0);
            $table->boolean('request_status_ben')->default(0);
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('subscribes');
    }
}
