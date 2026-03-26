<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;

class AIController extends Controller
{
   public function ask(OpenAIService $openAI)
    {
        // $response = $openAI->chat("Create CRUD for products");
        $response = $openAI->ask();

        return response()->json($response);
    }   
}