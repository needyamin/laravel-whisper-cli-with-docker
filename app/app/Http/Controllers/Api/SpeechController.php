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
     * Convert text to speech (MP3) using Python TTS script.
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

        $escapedText = escapeshellarg($text);
        $escapedPath = escapeshellarg($outputPath);
        $python = "python";

        $cmd = "$python " . base_path("scripts/tts.py") . " $escapedText $escapedPath 2>&1";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            return response()->json([
                'error' => 'TTS failed',
                'details' => implode("\n", $output)
            ], 500);
        }

        return response()->json([
            'audio_url' => asset("mp3/{$filename}.mp3")
        ]);
    }
}
