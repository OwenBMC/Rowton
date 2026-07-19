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
        Schema::create('eligibility_rules', function (Blueprint $table) {

            $table->id();

            $table->foreignId('service_item_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('service_category_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->boolean('active')
                ->default(true);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eligibility_rules');
    }
};
