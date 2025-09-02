<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

// use Log;

class SpeechController extends Controller
{

    public function speechToTextWhisper(Request $request)
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
                // Return JSON containing transcription text
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


    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string'
        ]);

        $text = $request->input('text');
        $filename = Str::uuid()->toString();
        $outputPath = public_path("mp3/{$filename}.mp3"); // public/mp3 folder
        if (!file_exists(public_path("mp3")))
            mkdir(public_path("mp3"), 0777, true);

        $escapedText = escapeshellarg($text);
        $escapedPath = escapeshellarg($outputPath);
        $python = "python";

        // Redirect stderr to stdout to capture errors
        $cmd = "$python " . base_path("scripts/tts.py") . " $escapedText $escapedPath 2>&1";

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            return response()->json([
                'error' => 'TTS failed',
                'details' => implode("\n", $output) // show Python errors
            ], 500);
        }
        return response()->json([
            'audio_url' => asset("mp3/{$filename}.mp3")
        ]);

    }



    /*** WORKING CODE IN DOCKER - TTS with gTTS
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

      */

    /***  STT with gTTS in Docker
    public function speechToText(Request $request)
    {

        if (!$request->hasFile('audio')) {
            return response()->json(['error' => 'No audio uploaded.'], 400);
        }

        $audioFile = $request->file('audio');
        $filename = Str::uuid()->toString();
        $uploadedPath = storage_path("app/public/$filename.webm");
        $outputDir = storage_path('app/public');

        try {
            $audioFile->move(dirname($uploadedPath), basename($uploadedPath));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save audio file.'], 500);
        }

        $mp3Path = storage_path("app/public/$filename.mp3");
        // Convert webm to mp3
        exec("ffmpeg -i \"$uploadedPath\" -ar 16000 -ac 1 \"$mp3Path\"", $output, $returnVar);
        if ($returnVar !== 0 || !file_exists($mp3Path)) {
            return response()->json(['error' => 'Audio conversion failed.'], 500);
        }
        // Run Whisper on mp3
        $cmd = "whisper \"$mp3Path\" --language English --output_format txt --output_dir \"$outputDir\"";
        exec($cmd, $whisperOutput, $whisperReturn);
        \Log::info("Whisper return var: $whisperReturn");
        \Log::info("Whisper output: " . implode("\n", $whisperOutput));

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
    */


}
