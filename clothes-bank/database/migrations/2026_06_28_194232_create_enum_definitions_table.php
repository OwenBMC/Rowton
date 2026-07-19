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
        Schema::create('enum_definitions', function (Blueprint $table) {

            $table->id();

            $table->string('group');

            $table->string('key');

            $table->string('label');

            $table->boolean('active')->default(true);
            $table->integer('sort_order')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enum_definitions');
    }
};
