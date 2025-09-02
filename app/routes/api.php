<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpeechController;

Route::post('/speech-to-text', [SpeechController::class, 'speechToText']);
Route::post('/text-to-speech', [SpeechController::class, 'textToSpeech']);



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
