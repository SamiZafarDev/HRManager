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
            while (self::queueSize() > 0) {
                $process = new Process([
                    'php',
                    base_path('artisan'),
                    'queue:work',
                    '--max-jobs=1',       // Process 1 job at a time
                    '--stop-when-empty',  // Stop when the queue is empty
                    '--sleep=3',          // Sleep 3 seconds between jobs
                    '--tries=3'           // Retry failed jobs 3 times
                ]);

                $process->setTimeout(null); // Disable Symfony process timeout
                $process->start();          // Start the process

                // Monitor the process
                while ($process->isRunning()) {
                    // Optionally log or monitor the process here
                    Log::info('Queue worker is running...');
                    sleep(5); // Prevent tight looping
                }

                // Log process output for debugging
                if (!$process->isSuccessful()) {
                    Log::error('Queue worker error: ' . $process->getErrorOutput());
                } else {
                    Log::info('Queue worker output: ' . $process->getOutput());
                }
            }

            Log::info('Queue worker finished processing all jobs.');
        } catch (\Exception $e) {
            Log::error('Queue worker failed: ' . $e->getMessage());
        }
    }

    public static function queueSize()
    {
        return Queue::size();
    }
}
