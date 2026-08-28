<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('end_time');
            $table->dateTime('ended_at')->nullable()->after('started_at');
        });

        DB::table('work_logs')
            ->select(['id', 'work_date', 'start_time', 'end_time'])
            ->orderBy('id')
            ->chunkById(100, function ($workLogs): void {
                foreach ($workLogs as $workLog) {
                    $startTime = $workLog->start_time ?: '00:00:00';
                    $endTime = $workLog->end_time ?: $startTime;

                    DB::table('work_logs')
                        ->where('id', $workLog->id)
                        ->update([
                            'started_at' => Carbon::parse($workLog->work_date.' '.$startTime),
                            'ended_at' => Carbon::parse($workLog->work_date.' '.$endTime),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ended_at']);
        });
    }
};
