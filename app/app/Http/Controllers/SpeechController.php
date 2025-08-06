<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SpeechController extends Controller
{
    public function speechToText(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimetypes:audio/wav,audio/mp3,audio/x-wav'
        ]);

        $audioFile = $request->file('audio');
        $filename = Str::uuid()->toString();
        $tempPath = storage_path("app/public/$filename.mp3");
        $outputDir = storage_path('app/public');

        $audioFile->move(dirname($tempPath), basename($tempPath));

        $cmd = "whisper \"$tempPath\" --language English --output_format txt --output_dir \"$outputDir\"";
        exec($cmd);

        $transcriptPath = "$outputDir/$filename.txt";
        if (!file_exists($transcriptPath)) {
            return response()->json(['error' => 'Transcription failed.'], 500);
        }

        $text = file_get_contents($transcriptPath);

        return response()->json([
            'transcript' => trim($text),
            'filename' => "$filename.txt"
        ]);
    }

    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string'
        ]);

        $text = $request->input('text');
        $filename = Str::uuid()->toString();
        $outputPath = storage_path("app/public/{$filename}.mp3");

        // Run Python gTTS command
        $escapedText = escapeshellarg($text);
        $escapedPath = escapeshellarg($outputPath);
        $cmd = "python3 " . base_path("scripts/tts.py") . " $escapedText $escapedPath";
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            return response()->json(['error' => 'TTS failed.'], 500);
        }

        return response()->json([
            'audio_url' => asset("storage/{$filename}.mp3")
        ]);
    }
}
