<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Enum;
use OpenAI;

class LlamaService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('LLAMA_API_URL', 'http://localhost:11434/api/generate');
        // $this->apiUrl = env('LLAMA_API_URL', 'https://api.deepseek.com/chat/completions');
    }

    public function generateText($prompt)
    {
        // $response = Http::timeout(500)->post($this->apiUrl, [
        //     'model' => 'deepseek-v2', // 'llama3.2',
        //     'prompt' => $prompt,
        //     'stream' => false,
        // ]);
        // return $response->json();

        return $this->sendToChatGPT($prompt);
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

    private function sendToChatGPT($prompt)
    {
        // $client = OpenAI::client(env('OPENAI_API_KEY'));
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        // $response = Http::timeout(500)->post($this->apiUrl, [
        //     'model' => 'gpt-4',
        //     'messages'=> [
        //         [
        //             "role"=> "user",
        //             "content"=>  $prompt,
        //         ],
        //     ],
        //     'max_tokens' => 500,  // Adjust to avoid cutoff
        //     'temperature' => 0.7,
        // ]);

        $response = $client->chat()->create([
            'model' => 'gpt-4',
            'messages'=> [
                [
                    "role"=> "user",
                    "content"=>  $prompt,
                ],
            ],
            'max_tokens' => 500,  // Adjust to avoid cutoff
            'temperature' => 0.7,
        ]);

        // if($response['choices'][0]['message']['content'] != '' && strlen($response['choices'][0]['message']['content'])>100)
        //  dd($response['choices'][0]['message']['content']);

        return $response['choices'][0]['message'] ?? [];
    }
}
