<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_activities', function (Blueprint $table) {
            $table->json('external_stakeholders')->nullable()->after('assigned_agents');
            $table->decimal('weight', 5, 2)->nullable()->change();
        });

        DB::table('project_activities')->update(['unit' => 'pourcentage']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('project_activities')->whereNull('weight')->update(['weight' => 100]);

        Schema::table('project_activities', function (Blueprint $table) {
            $table->dropColumn('external_stakeholders');
            $table->decimal('weight', 5, 2)->nullable(false)->change();
        });
    }
};
