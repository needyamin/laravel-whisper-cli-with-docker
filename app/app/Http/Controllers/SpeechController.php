<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
// use Log;

class SpeechController extends Controller
{
    public function speechToText(Request $request)
    {
        // \Log::info('Speech to text called.');

        if (!$request->hasFile('audio')) {
            // \Log::error('No audio file in request.');
            return response()->json(['error' => 'No audio uploaded.'], 400);
        }

        $audioFile = $request->file('audio');
        // \Log::info('Audio file received: ' . $audioFile->getClientOriginalName());

        $filename = Str::uuid()->toString();
        $uploadedPath = storage_path("app/public/$filename.webm");
        $outputDir = storage_path('app/public');

        try {
            $audioFile->move(dirname($uploadedPath), basename($uploadedPath));
            // \Log::info("Audio file moved to: $uploadedPath");
        } catch (\Exception $e) {
            // \Log::error('Failed to move audio file: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save audio file.'], 500);
        }

        $mp3Path = storage_path("app/public/$filename.mp3");

        // Convert webm to mp3
        exec("ffmpeg -i \"$uploadedPath\" -ar 16000 -ac 1 \"$mp3Path\"", $output, $returnVar);
        // \Log::info("FFmpeg return var: $returnVar");
        // \Log::info("FFmpeg output: " . implode("\n", $output));

        if ($returnVar !== 0 || !file_exists($mp3Path)) {
            // \Log::error('FFmpeg conversion failed.');
            return response()->json(['error' => 'Audio conversion failed.'], 500);
        }

        // Run Whisper on mp3
        $cmd = "whisper \"$mp3Path\" --language English --output_format txt --output_dir \"$outputDir\"";
        exec($cmd, $whisperOutput, $whisperReturn);
        \Log::info("Whisper return var: $whisperReturn");
        \Log::info("Whisper output: " . implode("\n", $whisperOutput));

        $transcriptPath = "$outputDir/$filename.txt";
        if (!file_exists($transcriptPath)) {
            // \Log::error('Transcription file not found: ' . $transcriptPath);
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

        // Escape values for shell
        $escapedText = escapeshellarg($text);
        $escapedPath = escapeshellarg($outputPath);

        // Call Python gTTS script
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
