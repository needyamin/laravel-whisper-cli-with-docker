<?php

namespace App\Http\Controllers;

use App\Models\EnglishParagraph;
use App\Models\SpeakingTest;
use App\Models\TestAttempt;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpeakingTestController extends Controller
{
    /**
     * Show the test interface.
     */
    public function showTest()
    {
        try {
            $user = Auth::user();
            
            // Check if user can take test
            if (!$user->canTakeTest()) {
                return redirect()->route('payment.index')
                    ->with('error', 'You need to purchase a test plan to continue. Your first test is free!');
            }
            
            // Check if user has an active test
            $activeTest = $user->speakingTests()
                ->whereIn('status', ['pending', 'in_progress'])
                ->latest()
                ->first();

            if ($activeTest) {
                \Log::info('Found active test', ['test_id' => $activeTest->id, 'status' => $activeTest->status]);
                return view('test.interface', compact('activeTest'));
            }

            // Create a new test with random paragraph
            $paragraph = EnglishParagraph::active()
                ->inRandomOrder()
                ->first();

            if (!$paragraph) {
                \Log::error('No paragraphs available');
                return redirect()->route('dashboard')
                    ->with('error', 'No test paragraphs available. Please contact administrator.');
            }

            $test = SpeakingTest::create([
                'user_id' => $user->id,
                'paragraph_id' => $paragraph->id,
                'status' => 'pending'
            ]);

            \Log::info('Created new test', ['test_id' => $test->id, 'paragraph_id' => $paragraph->id]);
            return view('test.interface', compact('test'));
        } catch (\Exception $e) {
            \Log::error('Error in showTest', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id()
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'Error loading test: ' . $e->getMessage());
        }
    }

    /**
     * Start the test.
     */
    public function startTest(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Check if user can take test (payment check)
            if (!$user->canTakeTest()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Payment required',
                        'message' => 'You need to purchase a test plan to continue. Your first test is free!',
                        'redirect' => route('payment.index')
                    ], 402);
                }
                return redirect()->route('payment.index')
                    ->with('error', 'You need to purchase a test plan to continue. Your first test is free!');
            }
            
            $request->validate([
                'test_id' => 'required|exists:speaking_tests,id'
            ]);

            $test = SpeakingTest::where('id', $request->test_id)
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'in_progress'])
                ->firstOrFail();

            // Only update status if it's pending
            if ($test->status === 'pending') {
                $test->update([
                    'status' => 'in_progress',
                    'started_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'test' => $test->load('paragraph'),
                'time_limit' => $test->time_limit
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Start test error', [
                'message' => $e->getMessage(),
                'test_id' => $request->test_id ?? 'unknown',
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to start test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit test attempt.
     */
    public function submitTest(Request $request)
    {
        try {
            $request->validate([
                'test_id' => 'required|exists:speaking_tests,id',
                'audio_file' => 'required|file|mimes:webm,mp3,wav,ogg|max:10240', // 10MB max
            ]);

            $user = Auth::user();
            
            // Debug: Log the test lookup
            \Log::info('Looking for test', [
                'test_id' => $request->test_id,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            // First, try to find the test regardless of status
            $test = SpeakingTest::where('id', $request->test_id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$test) {
                \Log::error('Test not found', [
                    'test_id' => $request->test_id,
                    'user_id' => $user->id,
                    'all_user_tests' => SpeakingTest::where('user_id', $user->id)->pluck('id', 'status')->toArray()
                ]);
                throw new \Exception('Test not found or does not belong to user');
            }
            
            \Log::info('Test found', [
                'test_id' => $test->id,
                'status' => $test->status,
                'user_id' => $test->user_id
            ]);
            
            // If test is not in_progress, update it to in_progress
            if ($test->status !== 'in_progress') {
                \Log::info('Updating test status to in_progress', ['test_id' => $test->id]);
                $test->update([
                    'status' => 'in_progress',
                    'started_at' => now()
                ]);
            }

            $audioFile = $request->file('audio_file');
            $filename = Str::uuid()->toString();
            
            // Debug: Log file details
            \Log::info('Audio file details', [
                'test_id' => $test->id,
                'original_name' => $audioFile->getClientOriginalName(),
                'extension' => $audioFile->getClientOriginalExtension(),
                'mime_type' => $audioFile->getMimeType(),
                'size' => $audioFile->getSize(),
                'real_path' => $audioFile->getRealPath(),
                'is_valid' => $audioFile->isValid(),
                'error' => $audioFile->getError()
            ]);
            
            // Get the actual file extension from the uploaded file
            $extension = $audioFile->getClientOriginalExtension();
            if (empty($extension)) {
                // Fallback: determine extension from MIME type
                $mimeType = $audioFile->getMimeType();
                $extension = match($mimeType) {
                    'audio/webm' => 'webm',
                    'audio/ogg' => 'ogg',
                    'audio/mp3' => 'mp3',
                    'audio/wav' => 'wav',
                    default => 'webm'
                };
            }
            
            $audioPath = "test_audio/{$filename}.{$extension}";
            
            \Log::info('Generated audio path', [
                'test_id' => $test->id,
                'filename' => $filename,
                'extension' => $extension,
                'audio_path' => $audioPath,
                'path_length' => strlen($audioPath)
            ]);
            
            // Store audio file
            try {
                \Log::info('Storing audio file', [
                    'test_id' => $test->id,
                    'filename' => $filename,
                    'extension' => $extension,
                    'audio_path' => $audioPath,
                    'file_size' => $audioFile->getSize(),
                    'mime_type' => $audioFile->getMimeType()
                ]);
                
                // Check if file content is readable
                $fileContent = $audioFile->getContent();
                if ($fileContent === false) {
                    throw new \Exception('Failed to read file content');
                }
                
                if (empty($fileContent)) {
                    throw new \Exception('File content is empty');
                }
                
                \Log::info('File content read successfully', [
                    'test_id' => $test->id,
                    'content_size' => strlen($fileContent),
                    'audio_path' => $audioPath
                ]);
                
                Storage::disk('public')->put($audioPath, $fileContent);
                
                \Log::info('Audio file stored successfully', ['audio_path' => $audioPath]);
            } catch (\Exception $e) {
                \Log::error('Failed to store audio file', [
                    'test_id' => $test->id,
                    'error' => $e->getMessage(),
                    'audio_path' => $audioPath
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to store audio file: ' . $e->getMessage()
                ], 500);
            }

            // Send to Whisper API for transcription
            $response = Http::attach(
                'file',
                $fileContent,
                $audioFile->getClientOriginalName()
            )->post('https://whisper.md-yamin-hossain.workers.dev');

            if (!$response->ok()) {
            \Log::error('Whisper API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'test_id' => $request->test_id,
                'user_id' => $user->id
            ]);
                throw new \Exception('Transcription failed: HTTP ' . $response->status() . ' - ' . $response->body());
            }

            $spokenText = $response->body();
            $originalText = $test->paragraph->content;

            // Calculate scores
            $scores = $this->calculateScores($originalText, $spokenText);

            // Create test attempt
            $attempt = TestAttempt::create([
                'test_id' => $test->id,
                'original_text' => $originalText,
                'spoken_text' => $spokenText,
                'accuracy_score' => $scores['accuracy'],
                'fluency_score' => $scores['fluency'],
                'pronunciation_score' => $scores['pronunciation'],
                'overall_score' => $scores['overall'],
                'audio_file_path' => $audioPath,
                'feedback' => $scores['feedback'],
                'word_scores' => $scores['word_scores'],
                'speaking_duration' => $request->duration ?? null
            ]);

            // Update test status
            $test->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Track test usage for payment purposes
            $this->trackTestUsage($user);

            // Generate certificate if passed
            if ($attempt->isPassed()) {
                $this->generateCertificate($test, $attempt);
            }

            return response()->json([
                'success' => true,
                'attempt' => $attempt,
                'passed' => $attempt->isPassed(),
                'certificate_generated' => $attempt->isPassed()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error in submitTest', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Test submission error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'test_id' => $request->test_id ?? 'unknown',
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Test submission failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate test scores.
     */
    private function calculateScores($originalText, $spokenText)
    {
        // Simple scoring algorithm - can be enhanced with more sophisticated methods
        $originalWords = str_word_count(strtolower($originalText), 1);
        $spokenWords = str_word_count(strtolower($spokenText), 1);
        
        $totalWords = count($originalWords);
        $correctWords = 0;
        $wordScores = [];

        foreach ($originalWords as $index => $word) {
            $spokenWord = $spokenWords[$index] ?? '';
            $isCorrect = $word === $spokenWord;
            
            if ($isCorrect) {
                $correctWords++;
            }
            
            $wordScores[] = [
                'word' => $word,
                'spoken' => $spokenWord,
                'correct' => $isCorrect,
                'score' => $isCorrect ? 100 : 0
            ];
        }

        $accuracy = $totalWords > 0 ? round(($correctWords / $totalWords) * 100) : 0;
        
        // Simple fluency calculation based on word count ratio
        $fluency = min(100, round((count($spokenWords) / $totalWords) * 100));
        
        // Pronunciation score (simplified)
        $pronunciation = $accuracy; // In real implementation, use speech recognition APIs
        
        $overall = round(($accuracy + $fluency + $pronunciation) / 3);
        
        $feedback = $this->generateFeedback($accuracy, $fluency, $pronunciation);

        return [
            'accuracy' => $accuracy,
            'fluency' => $fluency,
            'pronunciation' => $pronunciation,
            'overall' => $overall,
            'feedback' => $feedback,
            'word_scores' => $wordScores
        ];
    }

    /**
     * Generate feedback based on scores.
     */
    private function generateFeedback($accuracy, $fluency, $pronunciation)
    {
        $feedback = [];
        
        if ($accuracy >= 80) {
            $feedback[] = "Excellent accuracy! You pronounced most words correctly.";
        } elseif ($accuracy >= 60) {
            $feedback[] = "Good accuracy. Focus on pronouncing difficult words more clearly.";
        } else {
            $feedback[] = "Work on improving your pronunciation accuracy.";
        }

        if ($fluency >= 80) {
            $feedback[] = "Great fluency! You spoke at a good pace.";
        } elseif ($fluency >= 60) {
            $feedback[] = "Good fluency. Try to maintain a steady speaking pace.";
        } else {
            $feedback[] = "Practice speaking more fluently and at a consistent pace.";
        }

        return implode(' ', $feedback);
    }

    /**
     * Generate certificate for passed test.
     */
    private function generateCertificate($test, $attempt)
    {
        Certificate::create([
            'user_id' => $test->user_id,
            'test_id' => $test->id,
            'certificate_number' => Certificate::generateCertificateNumber(),
            'score_achieved' => $attempt->overall_score,
            'grade' => $attempt->grade,
            'issued_at' => now(),
            'expires_at' => now()->addYear(), // Certificate valid for 1 year
            'is_valid' => true
        ]);
    }

    /**
     * Track test usage for payment purposes.
     */
    private function trackTestUsage($user)
    {
        // If user has active waiver, no tracking needed
        if ($user->hasActiveWaiver()) {
            return;
        }

        // If user can take free test, increment free test usage
        if ($user->canTakeFreeTest()) {
            $user->increment('free_tests_used');
            \Log::info('Free test used', [
                'user_id' => $user->id,
                'free_tests_used' => $user->free_tests_used
            ]);
            return;
        }

        // If user has active subscription, increment tests used
        $activeSubscription = $user->activeSubscription();
        if ($activeSubscription && $activeSubscription->hasTestsRemaining()) {
            $activeSubscription->incrementTestsUsed();
            \Log::info('Subscription test used', [
                'user_id' => $user->id,
                'subscription_id' => $activeSubscription->id,
                'tests_used' => $activeSubscription->tests_used,
                'remaining_tests' => $activeSubscription->remaining_tests
            ]);
        }
    }

    /**
     * Get test results.
     */
    public function getResults($testId)
    {
        $test = SpeakingTest::where('id', $testId)
            ->where('user_id', Auth::id())
            ->with(['paragraph', 'attempts', 'certificate'])
            ->firstOrFail();

        return view('test.results', compact('test'));
    }
}
