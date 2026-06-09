<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('next_of_kins', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number', 20);
            $table->timestamps();

            $table->foreignId('service_user_id')
                ->constrained('service_users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('next_of_kins');
    }
};
