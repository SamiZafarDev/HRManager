<?php

namespace App\Jobs;

use App\Helpers\QueueManager;
use App\Models\Documents;
use App\Services\LlamaService;
use App\Http\Controllers\DocManagerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RankDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;
    protected $userid;
    protected $llama;

    /**
     * Create a new job instance.
     *
     * @param Documents $document
     * @param LlamaService $llama
     */
    public function __construct($document, $userid, LlamaService $llama)
    {
        $this->document = $document;
        $this->userid = $userid;
        $this->llama = $llama;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $docManager = new DocManagerController();
        $filePath = storage_path("app/public/documents/{$this->document->name}");

        try {
            Log::info("extractText {$this->document->name}: ");
            $text = \App\Helpers\DocumentProcessor::extractText($filePath);
            $extractedText = [
                'id' => $this->document->id,
                'name' => $this->document->name,
                'content' => $text,
            ];

            $rankedDocs = $docManager->sendToAI([$extractedText], $this->userid, $this->llama);
            $rankedData = $docManager->sortResponseInRanks($rankedDocs);

            $docManager->createDetailsOfRankedDocs($rankedData);
            Log::info("Creating details of RankedDocs {$this->document->name}: ");
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Error processing document {$this->document->name}: " . $e->getMessage());
        }

        // QueueManager::startQueueWorker();
    }
}
