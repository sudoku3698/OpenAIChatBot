<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DemoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ask-ai', [AIController::class, 'ask']);

Route::get('/chat', [ChatController::class, 'index']);
Route::post('/stream-chat', [ChatController::class, 'stream']);

Route::get('/stream-get', [DemoController::class, 'index']);
Route::post('/stream-post', [DemoController::class, 'streamPost']);
Route::get('/ob-test', [DemoController::class, 'ob_test']);