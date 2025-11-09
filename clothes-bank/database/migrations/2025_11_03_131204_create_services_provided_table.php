<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services_provided', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('service_category');
            $table->string('service_name');
            $table->date('attendance_date')->default(now());
            $table->unsignedBigInteger('service_user_id');
            $table->foreign('service_user_id')->references('id')->on('service_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_provided');
    }
};
