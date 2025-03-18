<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class LlamaService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('LLAMA_API_URL', 'http://localhost:11434/api/generate');
    }

    public function generateText($prompt)
    {
        // $response = Http::timeout(500)->post($this->apiUrl, [
        //     'model' => 'deepseek-v2', // 'llama3.2',
        //     'prompt' => $prompt,
        //     'stream' => false,
        // ]);
        // return $response->json();

        return $this->deepseekApi();
    }

    private function deepseekApi($prompt){
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
}
