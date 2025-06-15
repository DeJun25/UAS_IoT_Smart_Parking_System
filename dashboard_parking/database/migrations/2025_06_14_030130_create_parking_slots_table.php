<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('slot_number');  // 1, 2, 3 untuk slot parkir
            $table->date('date');            // Tanggal status slot
            $table->integer('status');       // 1 untuk Penuh, 0 untuk Kosong
            $table->timestamps();            // Tanggal pembuatan dan pembaruan
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_slots');
    }
}
