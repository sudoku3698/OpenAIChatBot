<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
class ChatController extends Controller
{
    public function index()
    {
    $chats = Chat::whereDate('created_at', today())->latest()->take(20)->get()->reverse();
        return view('chat', compact('chats'));
    }

    public function stream(Request $request)
    {
        $message = $request->input('message');

        return response()->stream(function () use ($message) {

            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);

            $apiKey = env('OPENAI_API_KEY');

            $fullResponse = ""; // 🔥 capture full AI response

            $data = [
                "model" => "gpt-5.4-mini",
                "stream" => true,
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "You are a helpful Laravel developer assistant."
                    ],
                    [
                        "role" => "user",
                        "content" => $message
                    ]
                ]
            ];

            $ch = curl_init("https://api.openai.com/v1/chat/completions");

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey
            ]);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$fullResponse) {

                echo $chunk; // ✅ keep streaming

                // 🔥 Extract text from chunk
                $lines = explode("\n", $chunk);

                foreach ($lines as $line) {
                    if (strpos($line, 'data: ') === 0) {

                        $jsonStr = trim(str_replace('data: ', '', $line));

                        if ($jsonStr === "[DONE]") continue;

                        $json = json_decode($jsonStr, true);

                        if (isset($json['choices'][0]['delta']['content'])) {
                            $fullResponse .= $json['choices'][0]['delta']['content'];
                        }
                    }
                }

                ob_flush();
                flush();

                return strlen($chunk);
            });

            curl_exec($ch);
            curl_close($ch);

            // 🔥 SAVE TO DB AFTER STREAM FINISHES
            Chat::create([
                'user_message' => $message,
                'bot_response' => $fullResponse
            ]);

        }, 200, [
            "Content-Type" => "text/event-stream",
            "Cache-Control" => "no-cache",
            "Connection" => "keep-alive",
        ]);
    }
}