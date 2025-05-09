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
    public static function queueSize()
    {
        return Queue::size();
    }
}
