<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intervention_utes', function (Blueprint $table): void {
            $table->string('subsidiary', 20)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('intervention_utes', function (Blueprint $table): void {
            $table->dropColumn('subsidiary');
        });
    }
};
