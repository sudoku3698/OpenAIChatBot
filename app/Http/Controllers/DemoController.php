<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DemoController extends Controller
{

    public function index()
    {
        return view('stream-demo');
    }
   public function streamPost(Request $request)
    {
        $message = $request->input('message');

        return response()->stream(function () use ($message) {

            // 🔥 Disable all buffering
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', 0);
            @ini_set('implicit_flush', 1);

            // 🔥 Clear any existing buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            echo "data: Start\n\n";
            flush();

            for ($i = 1; $i <= 50; $i++) {
                echo "data: $message - Part $i\n\n";
                flush();
                sleep(1);
            }

            echo "data: [DONE]\n\n";
            flush();

        }, 200, [
            "Content-Type" => "text/event-stream",
            "Cache-Control" => "no-cache, no-transform",
            "Connection" => "keep-alive",
            "X-Accel-Buffering" => "no",
        ]);
    }


    function ob_test(){
       ob_start();
        echo "Hello World";
        $content = ob_get_clean();

        echo $content; // now output happens here
    }
}