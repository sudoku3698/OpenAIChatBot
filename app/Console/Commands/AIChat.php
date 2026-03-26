<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AIChat extends Command
{
    protected $signature = 'ai:chat';
    protected $description = 'Interactive AI chat in terminal';

    public function handle()
    {
        $this->info("🤖 AI Chat Started (type 'exit' to quit)\n");

        $messages = [];
        $apiKey = config('services.openai.api_key');
        $baseUrl = 'https://api.openai.com/v1/responses';

        while (true) {
            $input = $this->ask("You");

            if (strtolower($input) === 'exit') {
                $this->info("Goodbye 👋");
                break;
            }

            // Add user message
            $messages[] = [
                "role" => "user",
                "content" => $input
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->post($baseUrl, [
                'model' => 'gpt-5-mini',
                'input' => $input,
                // 'max_output_tokens' => 200
            ]);

            $data = $response->json();
    
            $reply = $data['output'][0]['content'][0]['text'] ?? 'No response';

            // Add AI response to history
            $messages[] = [
                "role" => "assistant",
                "content" => $reply
            ];

            $this->line("\nAI: " . $reply . "\n");
        }

        return Command::SUCCESS;
    }
}