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
        Schema::table('project_trackings', function (Blueprint $table) {
            $table->string('ms_group_id')->nullable()->after('external_opportunity_id');
            $table->string('ms_plan_id')->nullable()->after('ms_group_id');
            $table->string('ms_bucket_id')->nullable()->after('ms_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_trackings', function (Blueprint $table) {
            $table->dropColumn(['ms_group_id', 'ms_plan_id', 'ms_bucket_id']);
        });
    }
};
