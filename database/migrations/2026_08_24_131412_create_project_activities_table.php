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
        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_tracking_id')->constrained()->cascadeOnDelete();
            $table->string('lot_name');
            $table->string('phase_name')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('assigned_agents')->nullable();
            $table->date('baseline_start_date')->nullable();
            $table->date('baseline_end_date')->nullable();
            $table->date('current_start_date');
            $table->date('current_end_date');
            $table->string('unit')->default('pourcentage');
            $table->decimal('planned_quantity', 14, 2)->default(100);
            $table->decimal('completed_quantity', 14, 2)->default(0);
            $table->decimal('weight', 5, 2);
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'suspended', 'blocked'])->default('not_started');
            $table->string('deliverable')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
