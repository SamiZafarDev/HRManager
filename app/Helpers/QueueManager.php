<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class QueueManager
{
    public static function startQueueWorker($authToken)
    {
        try {
            // Check if a queue worker process is already running
            $existingProcess = new Process(['pgrep', '-f', 'queue:listen']);
            $existingProcess->run();

            if ($existingProcess->isSuccessful()) {
                // Stop the existing process
                $pid = trim($existingProcess->getOutput());
                Log::info("Stopping existing queue worker process with PID: $pid");
                $stopProcess = new Process(['kill', $pid]);
                $stopProcess->run();

                if (!$stopProcess->isSuccessful()) {
                    Log::error('Failed to stop the existing queue worker process.');
                    return;
                }
            }

            // Start a new queue worker process
            $process = new Process([
                'php',
                base_path('artisan'),
                'queue:listen',
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
                    Log::info('base url is:'.env("APP_URL"));
                    Log::info('auth token:'.$authToken);

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer $authToken"
                    ])->get(env("APP_URL").'/api/start-queue');

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
