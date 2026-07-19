<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_rule_groups', function (Blueprint $table) {

            $table->id();

            $table->foreignId('eligibility_rule_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('operator', [
                'AND',
                'OR',
            ])
                ->default('AND');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_rule_groups');
    }
};
