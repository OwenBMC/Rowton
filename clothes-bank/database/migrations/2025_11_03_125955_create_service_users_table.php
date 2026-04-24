<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('first_name')->nullable();
            $table->string('middle_names')->nullable();
            $table->string('surname')->nullable();
            $table->string('nickname')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('postcode', 20)->nullable();
            $table->boolean('food_allergies')->nullable();
            $table->string('housing_status')->default('unknown');
        });
        DB::statement('
    ALTER TABLE service_users
    ADD CONSTRAINT service_users_name_parts_or_nickname_not_null
    CHECK (
        first_name IS NOT NULL 
        OR middle_names IS NOT NULL 
        OR surname IS NOT NULL 
        OR nickname IS NOT NULL
    )
');

        DB::statement('
    ALTER TABLE service_users
    ADD CONSTRAINT service_users_full_name_and_nickname_not_equal
    CHECK (
        nickname IS NULL 
        OR (COALESCE(first_name, \'\') || \' \' || COALESCE(middle_names, \'\') || \' \' || COALESCE(surname, \'\')) <> nickname
    )
');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_users');
    }
};
