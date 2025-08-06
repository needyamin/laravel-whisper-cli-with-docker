<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;

Route::post('/speech-to-text', [SpeechController::class, 'speechToText']);
Route::post('/text-to-speech', [SpeechController::class, 'textToSpeech']);



Route::get('/', function () {
    return view('welcome');
});
