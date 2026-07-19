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
        Schema::create('eligibility_conditions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('eligibility_rule_group_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            /*
             * The attribute being tested
             *
             * e.g.
             * housing_status
             * gender
             * dob
             */
            $table->string('attribute');

            /*
             * Operator
             *
             * =
             * !=
             * contains
             * >
             * <
             * in
             */
            $table->string('operator');

            /*
             * Stored as text
             *
             * Handles:
             * enums
             * dates
             * numbers
             */
            $table->string('value');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eligibility_conditions');
    }
};
