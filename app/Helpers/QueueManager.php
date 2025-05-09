<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class QueueManager
{
    public static function startQueueWorker()
    {
        try {
            $queueSize = self::queueSize();
            $process = null;
            $process = new Process([
                'php',
                base_path('artisan'),
                'queue:work',
                '--max-jobs=1',       // Process 10 jobs at a time
                '--stop-when-empty',
                '--sleep=3',            // Sleep 3 seconds between jobs
                '--tries=3'             // Retry failed jobs 3 times
            ]);
            while ($queueSize > 0) {

                if ($queueSize > 0 && !$process->isRunning()) {

                    $process->setTimeout(null);
                    $process->run();

                    // Log process output for debugging
                    Log::info('Queue worker output: ' . $process->getOutput());
                    Log::error('Queue worker error: ' . $process->getErrorOutput());
                }
                else {
                    // No jobs in queue, sleep before checking again
                    sleep(30);
                }
            }
        } catch (\Exception $e) {
            Log::error('Queue worker failed: ' . $e->getMessage());
        }
    }

    public static function queueSize()
    {
        return Queue::size();
    }
}
