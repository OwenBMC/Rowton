<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();

            $table->string('gp_name');
            $table->string('gp_practice');
            $table->string('gp_address');
            $table->string('contact_number', 20);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
