<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class QueueManager
{
    public static function startQueueWorker($authToken)
    {
        try {
            if (self::queueSize() == 0)
                return;

            function isWorkerRunning()
            {
                $process = new Process(['pgrep', '-f', 'queue:work']);
                $process->run();
                $output = trim($process->getOutput());
                return !empty($output);
            }
            $processPID = null;
            try {
                $processPID = Cache::get('queue_worker_pid');
                Log::info("Queue worker PID :". $processPID);

                if ($processPID != null) {
                    // Check if process is still running
                    if (function_exists('posix_kill')) {
                        $pid = (int) $processPID;

                        if (isWorkerRunning()) {
                            Log::info("Process $pid is running.");
                        } else {
                            Log::warning("Process $pid is NOT running.");
                            $processPID = null; // Reset if process is not running
                        }
                    }
                    else{
                        Log::warning("posix_kill function does not exist. Cannot check process status.");
                    }
                } else {
                    Log::warning("Queue worker PID file not found.");
                }
            } catch (\Throwable $th) {
                Log::error('Error checking existing queue worker process: ' . $th->getMessage());
            }

            // Start a new queue worker process
            $process = null;
            if($processPID == null){
                $command = [
                    'php',
                    base_path('artisan'),
                    'queue:listen',
                    '--sleep=3',          // Sleep 3 seconds between jobs
                    '--tries=3'           // Retry failed jobs 3 times
                ];

                $process = new Process($command);
                $process->setTimeout(null); // No timeout for the overall process
                $process->start();
                $pid = $process->getPid();
                Log::info("Queue worker started with PID: " . $pid);

                Cache::put('queue_worker_pid', $pid);
            }

            $startTime = time(); // Record the start time
            while (self::queueSize() > 0) {
                // Check if 1 minute has passed
                if (time() - $startTime >= 60) {
                    // Hit the API
                    Log::info('base url is:' . env("APP_URL"));
                    if (self::queueSize() == 0)
                        break;
                    Log::info(self::queueSize());
                    try {
                        $apiUrl = env("APP_URL") . '/api/start-queue';
                        $cmd = "curl -X GET -H 'Authorization: Bearer $authToken' 'Content-Type: application/json' '$apiUrl' > /dev/null 2>&1 &";
                        Log::info('Executing command: ' . $cmd);
                        exec($cmd);
                        Log::info('API called asynchronously: ' . $apiUrl);
                        return;
                    } catch (\Throwable $th) {
                        log::error('Error hitting API: ' . $th->getMessage());
                    }

                    // Reset the start time
                    $startTime = time();
                }

                // Optionally log or monitor the process here
                Log::info('Queue worker is running...');
                sleep(5); // Prevent tight looping
            }

            Log::info("Queue is running " . isWorkerRunning() . " , queue size is: " . self::queueSize());

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


