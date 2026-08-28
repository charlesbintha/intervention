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
        Schema::create('project_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('external_project_code')->index();
            $table->string('external_project_name');
            $table->string('external_opportunity_id')->nullable();
            $table->enum('subsidiary', ['GUT', 'CP', 'UTA', 'UA', 'UTE', 'UC']);
            $table->string('client_name')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->date('baseline_start_date')->nullable();
            $table->date('baseline_end_date')->nullable();
            $table->date('current_start_date')->nullable();
            $table->date('current_end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'suspended', 'completed'])->default('draft');
            $table->timestamp('baseline_approved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'external_project_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_trackings');
    }
};
