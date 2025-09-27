<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SpeechController extends Controller
{
    /**
     * Convert speech (audio file) to text using Whisper API.
     */
    public function speechToText(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No audio file provided'], 400);
        }

        $file = $request->file('file');

        try {
            $response = Http::attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName()
            )->post('https://whisper.md-yamin-hossain.workers.dev');

            if ($response->ok()) {
                return response()->json([
                    'text' => $response->body()
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Worker returned error',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Request failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert text to speech (MP3) using Worker AI.
     */
    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string'
        ]);

        $text = $request->input('text');
        $filename = Str::uuid()->toString();
        $outputPath = public_path("mp3/{$filename}.mp3");

        if (!file_exists(public_path("mp3"))) {
            mkdir(public_path("mp3"), 0777, true);
        }

        try {
            // Send text to your worker AI for TTS
            $response = Http::post('https://whisper.md-yamin-hossain.workers.dev/tts', [
                'text' => $text,
                'output_format' => 'mp3'
            ]);

            if ($response->ok()) {
                // Save the audio response to file
                file_put_contents($outputPath, $response->body());
                
                return response()->json([
                    'audio_url' => asset("mp3/{$filename}.mp3")
                ]);
            } else {
                return response()->json([
                    'error' => 'TTS worker returned error',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'TTS request failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
