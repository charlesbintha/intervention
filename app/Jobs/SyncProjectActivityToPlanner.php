<?php

namespace App\Jobs;

use App\Models\ProjectActivity;
use App\Services\MicrosoftGraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncProjectActivityToPlanner implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 180];

    public function __construct(public int $activityId) {}

    /**
     * Execute the job.
     */
    public function handle(MicrosoftGraphService $microsoftGraphService): void
    {
        $activity = ProjectActivity::query()->with('projectTracking')->find($this->activityId);

        if (! $activity) {
            return;
        }

        $activity->update(['planner_sync_status' => 'syncing', 'planner_sync_error' => null]);

        try {
            $taskId = $microsoftGraphService->syncActivity($activity);
            $activity->update([
                'ms_planner_task_id' => $taskId,
                'planner_sync_status' => 'synced',
                'planner_sync_error' => null,
                'planner_synced_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $activity->update([
                'planner_sync_status' => 'failed',
                'planner_sync_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
