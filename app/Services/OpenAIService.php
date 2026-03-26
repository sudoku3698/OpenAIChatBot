<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function chat($prompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl, [
            'model' => 'gpt-5.4-mini', // ✅ valid model
            'input' => $prompt,
            // 'max_output_tokens' => 200,
        ]);

        return $response->json();
    }

    public function ask(){
        $apiKey = $this->apiKey;

        $data = [
            "model" => "gpt-5.4-mini",
            "messages" => [
                ["role" => "user", "content" => "Create CRUD for products in Laravel"]
            ]
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        // dd($result);
        echo $result['choices'][0]['message']['content'];
    }
}