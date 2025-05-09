<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class QueueManager
{
    public static function startQueueWorker()
    {
        try {
            $process = new Process([
                'php',
                base_path('artisan'),
                'queue:listen',
                '--max-jobs=1',
                '--sleep=3',          // Sleep 3 seconds between jobs
                '--tries=3'           // Retry failed jobs 3 times
            ]);
            $process->setTimeout(null); // Allow the process to run indefinitely
            $process->start();

            $startTime = time(); // Record the start time
            while ($process->isRunning()) {
                // Check if 1 minute has passed
                if (time() - $startTime >= 60) {
                    // Hit the API
                    $response = Http::post(env("APP_URL").'/api/start-queue');

                    // Log the API response
                    if ($response->successful()) {
                        Log::info('API hit successfully: ' . $response->body());
                    } else {
                        Log::error('Failed to hit API: ' . $response->body());
                    }

                    // Reset the start time
                    $startTime = time();
                }

                // Optionally log or monitor the process here
                Log::info('Queue worker is running...');
                sleep(5); // Prevent tight looping
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
