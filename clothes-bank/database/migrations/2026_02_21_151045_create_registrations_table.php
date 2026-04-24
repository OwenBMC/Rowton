<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            // Basic Fields
            $table->date('referral_date')->nullable();
            $table->date('service_user_signature_date')->nullable();
            $table->date('volunteer_signature_date')->nullable();

            // Foreign Keys
            $table->foreignId('service_user_id')
                ->constrained('service_users')
                ->onDelete('cascade');

            $table->foreignId('next_of_kin_id')
                ->nullable()
                ->constrained('next_of_kins')
                ->nullOnDelete();

            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
