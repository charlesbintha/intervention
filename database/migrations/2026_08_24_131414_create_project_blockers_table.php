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
        Schema::create('project_blockers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_tracking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('impact')->nullable();
            $table->text('proposed_solution')->nullable();
            $table->enum('status', ['open', 'analysing', 'processing', 'resolved', 'closed'])->default('open');
            $table->date('opened_at');
            $table->date('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_blockers');
    }
};
