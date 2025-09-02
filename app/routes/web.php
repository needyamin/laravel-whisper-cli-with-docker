<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;

// TTS with ElevenLabs
#Route::post('/speech-to-text', [SpeechController::class, 'speechToText']);
Route::post('/text-to-speech', [SpeechController::class, 'textToSpeech']);

// STT with Whisper
Route::post('/speech-to-texts', [SpeechController::class, 'speechToTextWhisper']);

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tts', function () {
    return view('tts');
});


