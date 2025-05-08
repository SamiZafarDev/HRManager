<?php

namespace App\Helpers;

use App\Enums\StorageFolder;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class QueueManager
{
    public static function startQueueWorker()
    {
        if(self::isQueueWorkerRunning()) return;
        $process = new Process([
            'php',
            base_path('artisan'),
            'queue:work',
            '--stop-when-empty'
        ]);
        $process->setTimeout(null);
        $process->disableOutput();
        $process->start();

        while ($process->isRunning()) {
        }
    }
    /**
     * Check if a queue worker is already running.
     *
     * @return bool
     */
    private static function isQueueWorkerRunning(): bool
    {
        // Check the queue size or use a custom mechanism to detect running workers
        return Queue::size() > 0; // Example: Modify this logic based on your requirements
    }
}
