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
        Schema::create('workout_plan_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedTinyInteger('sets');
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds');
            $table->decimal('target_weight', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workout_plan_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plan_items');
    }
};
