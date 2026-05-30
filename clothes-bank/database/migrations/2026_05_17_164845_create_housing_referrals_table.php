<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_referrals', function (Blueprint $table) {
            $table->id();

            // Core service user info
            $table->foreignId('service_user_id')->constrained()->cascadeOnDelete();

            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('contact_number')->nullable();

            // Additional fields
            $table->string('national_insurance_number')->nullable();
            $table->string('nationality')->nullable();
            $table->text('previous_address')->nullable();

            $table->boolean('prison')->default(false);
            $table->boolean('hospital')->default(false);
            $table->text('fda')->nullable();

            $table->integer('housing_points')->nullable();

            $table->text('medical_conditions')->nullable();

            $table->string('first_contact')->nullable();
            $table->string('second_contact')->nullable();
            $table->string('third_contact')->nullable();

            $table->text('notes')->nullable();

            $table->string('outcome')->nullable();

            $table->boolean('sleeping_bag')->default(false);

            $table->date('referral_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_referrals');
    }
};
