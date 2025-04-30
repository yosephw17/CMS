<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('schedule_time_slot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_result_id');
            $table->unsignedBigInteger('time_slot_id');
            $table->timestamps();

            $table->foreign('schedule_result_id')->references('id')->on('schedule_results')->onDelete('cascade');
            $table->foreign('time_slot_id')->references('id')->on('time_slots')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('schedule_time_slot');
    }
};
