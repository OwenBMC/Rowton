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
        Schema::create('service_policies', function (Blueprint $table) {

            $table->id();

            $table->foreignId('service_item_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('cooldown_days')
                ->nullable();

            $table->boolean('manager_override')
                ->default(false);

            $table->boolean('enabled')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_policies');
    }
};
