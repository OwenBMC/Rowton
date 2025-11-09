<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


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
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('housing_status')->default('unknown');
        });
        DB::statement("
            ALTER TABLE service_users
            ADD CONSTRAINT service_users_name_or_nickname_not_null
            CHECK (name IS NOT NULL OR nickname IS NOT NULL)
        ");

        DB::statement("
            ALTER TABLE service_users
            ADD CONSTRAINT service_users_name_and_nickname_not_equal
            CHECK (name IS NULL OR nickname IS NULL OR name <> nickname)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_users');
    }
};
