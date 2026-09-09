<?php

namespace App\Jobs;

use App\Services\MicrosoftGraphService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeletePlannerTask implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 180];

    public function __construct(public string $taskId) {}

    /**
     * Execute the job.
     */
    public function handle(MicrosoftGraphService $microsoftGraphService): void
    {
        $microsoftGraphService->deletePlannerTask($this->taskId);
    }
}
