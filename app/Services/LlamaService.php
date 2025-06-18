<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use OpenAI;

class LlamaService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('LLAMA_API_URL', 'http://localhost:11434/api/generate');
    }

    public function generateText($prompt, $file=null)
    {
        if($file != null){
            return $this->sendToChatDocGPT($prompt, $file);
        }
        return $this->sendToChatGPT($prompt);
    }

    private function deepseekApi($prompt)
    {
        $apiKey = "sk-707219faf3654169ab210340ab492e9e";
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $apiKey"
        ])->post($this->apiUrl, [
            "model" => "deepseek-chat",
            "messages" => [
                // ["role" => "system", "content" => "You are a helpful assistant."],
                ["role" => "user", "content" => $prompt]
            ],
            "stream" => false
        ]);


        return $response->json();
    }

    private function sendToChatGPT($prompt)
    {
        $key = env("OPENAI_API_KEY");
        Log::info("OPENAI_API_KEY {$key}");
        $client = OpenAI::client($key);

        $message = [
            "role" => "user",
            "content" =>  [
                ["type" => "text", "text" => $prompt]
            ]
        ];

        $response = $client->chat()->create([
            'model' => 'gpt-4.1',
            'messages' => [
                $message
            ],
            'max_tokens' => 500,  // Adjust to avoid cutoff
            'temperature' => 0.7,
        ]);

        Log::info("sendToChatGPT response: ", [
            'response' => $response,
        ]);

        return $response['choices'][0]['message'] ?? [];
    }

    public function sendToChatDocGPT($prompt, $file)
    {
        $key = env("OPENAI_API_KEY");
        Log::info("OPENAI_API_KEY {$key}");
        $client = OpenAI::client($key);

        $fileUploadData = $this->uploadPdfToOpenAI($file, $client);
        if ($fileUploadData instanceof JsonResponse) {
            return $fileUploadData;
        }
        $fileId = $fileUploadData['file_id'];
        Log::info("file uploaded to open ai fileId = $fileId");

        $thread = $client->threads()->create([]);

        $message = $client->threads()->messages()->create($thread->id, [
            'role' => 'user',
            'content' => $prompt,
            'attachments' => [
                [
                    'file_id' => $fileId,
                    'tools' => [['type' => 'file_search']]
                ]
            ]
        ]);
        Log::info("message sent");

        $run = $client->threads()->runs()->create($thread->id, [
            'assistant_id' => 'asst_FrvrpiGurf619Quu2QqAboek', // Must be created with file support
        ]);
        Log::info('running client thread');

        $attempts = 0;
        while ($attempts++ < 10) {
            $run = $client->threads()->runs()->retrieve($thread->id, $run->id);
            Log::info("attempts $attempts run->status: $run->status");

            if ($run->status === 'completed') {
                $messages = $client->threads()->messages()->list($thread->id);
                $assistantMessage = collect($messages->data)
                ->where('role', 'assistant')
                ->last();

                return response()->json([
                    'content' => $assistantMessage->content[0]->text->value ?? 'No response'
                ]);
            }

            sleep(1);
        }
        return response()->json(['error' => 'Assistant took too long to respond.'], 504);
    }
    public function uploadPdfToOpenAI($file, $client)
    {
        if (!$file) {
            return response()->json(['error' => 'PDF file is required.'], 400);
        }

        $path = $file->getPathname();
        $name = $file->getClientOriginalName();

        /**
         * Create a temporary copy of the file with its real name.
         * This ensures the OpenAI SDK sees the filename with .pdf.
         */
        $tmpFile = tempnam(sys_get_temp_dir(), 'openai_');
        $newTmpPath = $tmpFile . '_' . $name;
        copy($path, $newTmpPath);

        $stream = fopen($newTmpPath, 'r');

        $upload = $client->files()->upload([
            'purpose' => 'assistants',
            'file' => $stream // ✅ Now it's a valid resource with filename
        ]);

        Log::info("uploaded file: ", $upload->toArray());

        return [
            'file_id' => $upload->id,
            'filename' => $upload->filename,
        ];
    }
}
