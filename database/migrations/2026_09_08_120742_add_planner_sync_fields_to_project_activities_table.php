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
        Schema::table('project_activities', function (Blueprint $table) {
            $table->json('assigned_agent_emails')->nullable()->after('assigned_agents');
            $table->string('ms_planner_task_id')->nullable()->after('sort_order');
            $table->string('planner_sync_status')->default('not_configured')->after('ms_planner_task_id');
            $table->text('planner_sync_error')->nullable()->after('planner_sync_status');
            $table->timestamp('planner_synced_at')->nullable()->after('planner_sync_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_activities', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_agent_emails',
                'ms_planner_task_id',
                'planner_sync_status',
                'planner_sync_error',
                'planner_synced_at',
            ]);
        });
    }
};
