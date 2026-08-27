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
        Schema::create('workout_set_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_plan_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->unsignedSmallInteger('reps')->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->unique(['workout_session_id', 'workout_plan_item_id', 'set_number'], 'workout_set_logs_session_item_set_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_set_logs');
    }
};
