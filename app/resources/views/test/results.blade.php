<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - English Speaking Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">English Speaking Test</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('test.show') }}" class="text-gray-600 hover:text-gray-900">New Test</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Test Results</h2>
            <p class="text-gray-600">Detailed analysis of your speaking test performance</p>
        </div>

        @if($test->attempts->count() > 0)
        @php $attempt = $test->attempts->last(); @endphp
        
        <!-- Overall Results -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Overall Performance</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold {{ $attempt->overall_score >= 70 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $attempt->overall_score }}%
                        </div>
                        <div class="text-sm text-gray-600">Overall Score</div>
                        <div class="mt-2">
                            <span class="px-3 py-1 text-sm font-semibold rounded-full
                                {{ $attempt->overall_score >= 90 ? 'bg-green-100 text-green-800' :
                                   ($attempt->overall_score >= 80 ? 'bg-blue-100 text-blue-800' :
                                   ($attempt->overall_score >= 70 ? 'bg-yellow-100 text-yellow-800' :
                                   ($attempt->overall_score >= 60 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'))) }}">
                                {{ $attempt->grade }}
                            </span>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $attempt->accuracy_score }}%</div>
                        <div class="text-sm text-gray-600">Accuracy</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $attempt->fluency_score }}%</div>
                        <div class="text-sm text-gray-600">Fluency</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $attempt->pronunciation_score }}%</div>
                        <div class="text-sm text-gray-600">Pronunciation</div>
                    </div>
                </div>
                
                @if($attempt->isPassed())
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Congratulations! You passed!</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p>You have successfully passed the speaking test and earned a certificate.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Test not passed</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>You need to score at least 70% to pass. Keep practicing and try again!</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Test Content -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Test Content</h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Original Text:</h4>
                    <div class="p-4 bg-gray-50 rounded-md text-gray-900">
                        {{ $test->paragraph->content }}
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">What You Spoke:</h4>
                    <div class="p-4 bg-blue-50 rounded-md text-gray-900">
                        {{ $attempt->spoken_text }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Feedback -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Detailed Feedback</h3>
            </div>
            <div class="p-6">
                <div class="prose max-w-none">
                    <p class="text-gray-700">{{ $attempt->feedback }}</p>
                </div>
            </div>
        </div>

        <!-- Word-by-Word Analysis -->
        @if($attempt->word_scores && count($attempt->word_scores) > 0)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Word-by-Word Analysis</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($attempt->word_scores as $wordScore)
                    <div class="flex items-center justify-between p-3 border rounded-md
                        {{ $wordScore['correct'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                        <div>
                            <div class="font-medium text-gray-900">{{ $wordScore['word'] }}</div>
                            @if(!$wordScore['correct'])
                            <div class="text-sm text-gray-600">Spoke: "{{ $wordScore['spoken'] }}"</div>
                            @endif
                        </div>
                        <div class="text-sm font-medium
                            {{ $wordScore['correct'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $wordScore['score'] }}%
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Test Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Test Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Test Details</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div>Test Date: {{ $test->created_at->format('M d, Y H:i') }}</div>
                            <div>Completed: {{ $test->completed_at ? $test->completed_at->format('M d, Y H:i') : 'Not completed' }}</div>
                            @if($attempt->speaking_duration)
                            <div>Speaking Duration: {{ gmdate('i:s', $attempt->speaking_duration) }}</div>
                            @endif
                            <div>Difficulty: {{ ucfirst($test->paragraph->difficulty_level) }}</div>
                            <div>Word Count: {{ $test->paragraph->word_count }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Actions</h4>
                        <div class="space-y-2">
                            <a href="{{ route('test.show') }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Take Another Test
                            </a>
                            <a href="{{ route('test.history') }}" class="block w-full text-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                View Test History
                            </a>
                            @if($attempt->isPassed())
                            <a href="{{ route('certificates') }}" class="block w-full text-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                View Certificates
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No results available</h3>
                <p class="mt-1 text-sm text-gray-500">This test hasn't been completed yet.</p>
                <div class="mt-6">
                    <a href="{{ route('test.show') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Continue Test
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
