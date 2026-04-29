<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_service_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('practice_id')
                ->constrained()
                ->cascadeOnDelete();

            // optional context
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->unique(['service_user_id', 'practice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_service_user');
    }
};
