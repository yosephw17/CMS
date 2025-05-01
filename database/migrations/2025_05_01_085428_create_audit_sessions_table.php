<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Before Mid Exam"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_sessions');
    }
}
