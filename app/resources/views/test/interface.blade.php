<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speaking Test - English Speaking Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Modern Audio Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/recordrtc@5.6.2/RecordRTC.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wavesurfer.js/6.6.3/wavesurfer.min.js"></script>
    
    <!-- Text-to-Speech API -->
    <script>
        // Text-to-Speech function
        function speakText(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 0.8; // Slower speech for better understanding
                utterance.pitch = 1;
                utterance.volume = 0.8;
                speechSynthesis.speak(utterance);
                return true;
            }
            return false;
        }
        
        // Stop speech
        function stopSpeech() {
            if ('speechSynthesis' in window) {
                speechSynthesis.cancel();
            }
        }
        
        // Auto-play paragraph when page loads
        function autoPlayParagraph() {
            setTimeout(() => {
                const text = paragraphText.textContent.trim();
                if (text && speakText(text)) {
                    speakBtn.textContent = "🔊 Playing...";
                    speakBtn.disabled = true;
                    stopSpeakBtn.disabled = false;
                    
                    // Auto-enable buttons after speech ends
                    setTimeout(() => {
                        speakBtn.textContent = "🔊 Listen to Paragraph";
                        speakBtn.disabled = false;
                        stopSpeakBtn.disabled = true;
                    }, 10000); // Approximate speech duration
                }
            }, 1000); // Wait 1 second after page load
        }
        
        // Initialize microphone status
        function initializeMicrophoneStatus() {
            updatePermissionStatus(false);
            updateConnectionStatus(false);
            updateWorkingStatus(false);
            
            // Check if microphone permission is already granted
            navigator.permissions.query({ name: 'microphone' }).then(function(result) {
                if (result.state === 'granted') {
                    updatePermissionStatus(true);
                    // Try to get stream to test connection
                    navigator.mediaDevices.getUserMedia({ audio: true })
                        .then(stream => {
                            updateConnectionStatus(true);
                            updateWorkingStatus(true);
                            currentStream = stream;
                            updateDeviceList();
                            requestPermBtn.textContent = "✅ Permission Granted";
                            requestPermBtn.disabled = true;
                            startBtn.disabled = false;
                            progressText.textContent = "Microphone ready! Click 'Start Test' to begin.";
                        })
                        .catch(err => {
                            console.log('Microphone test failed:', err);
                            updateConnectionStatus(false);
                            updateWorkingStatus(false);
                        });
                } else {
                    updatePermissionStatus(false);
                }
            }).catch(err => {
                console.log('Permission query failed:', err);
                updatePermissionStatus(false);
            });
        }
    </script>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .wave-animation {
            animation: wave 1.5s ease-in-out infinite;
        }
        @keyframes wave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(1.5); }
        }
        
        /* Enhanced Audio Visualization */
        .audio-visualizer {
            height: 100px;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            position: relative;
            overflow: hidden;
        }
        
        .waveform-container {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .recording-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            display: none;
        }
        
        .recording-indicator.active {
            display: block;
            animation: recording-pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes recording-pulse {
            0%, 100% { 
                transform: translate(-50%, -50%) scale(1);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }
            50% { 
                transform: translate(-50%, -50%) scale(1.05);
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">English Speaking Test</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <!-- Test Status -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-gray-900">Speaking Test</h2>
                        <div id="timer" class="text-lg font-mono text-gray-600">05:00</div>
                    </div>
                    <div class="mt-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ isset($activeTest) && $activeTest->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ isset($activeTest) && $activeTest->status === 'in_progress' ? 'In Progress' : 'Ready to Start' }}
                        </span>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Instructions:</h3>
                    <ul class="list-disc list-inside text-blue-800 space-y-1">
                        <li><strong>First:</strong> Click "Request Microphone Permission" and allow access when prompted</li>
                        <li>Select your preferred microphone from the dropdown</li>
                        <li>Read the paragraph below carefully</li>
                        <li>Click "Start Test" when you're ready to begin recording</li>
                        <li>Speak clearly and at a natural pace</li>
                        <li>You have 5 minutes to complete the test</li>
                        <li>Click "Stop Recording" when you're finished</li>
                    </ul>
                </div>

                <!-- Microphone Status Panel -->
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Microphone Status</h3>
                    
                    <!-- Status Indicators -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Connection Status -->
                        <div class="flex items-center space-x-2">
                            <div id="connection-indicator" class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <span id="connection-text" class="text-sm text-gray-600">Not Connected</span>
                        </div>
                        
                        <!-- Permission Status -->
                        <div class="flex items-center space-x-2">
                            <div id="permission-indicator" class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <span id="permission-text" class="text-sm text-gray-600">No Permission</span>
                        </div>
                        
                        <!-- Working Status -->
                        <div class="flex items-center space-x-2">
                            <div id="working-indicator" class="w-3 h-3 rounded-full bg-gray-400"></div>
                            <span id="working-text" class="text-sm text-gray-600">Not Tested</span>
                        </div>
                    </div>
                    
                    <!-- Audio Level Visualization -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Microphone Level</span>
                            <span id="level-value" class="text-sm text-gray-500">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div id="level-bar" class="h-full bg-gradient-to-r from-green-400 to-red-500 transition-all duration-100" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Silent</span>
                            <span>Normal</span>
                            <span>Loud</span>
                        </div>
                    </div>
                    
                    <!-- Microphone Test Area -->
                    <div id="mic-test-area" class="hidden">
                        <div class="text-center">
                            <button id="testMicBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Test Microphone
                            </button>
                            <p class="text-sm text-gray-600 mt-2">Speak into your microphone to see the level indicator</p>
                        </div>
                    </div>
                </div>

                <!-- Paragraph to Read -->
                <div class="mb-6 p-6 bg-gray-50 border border-gray-200 rounded-md">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">Read this paragraph:</h3>
                        <div class="flex space-x-2">
                            <button id="speakBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                🔊 Listen to Paragraph
                            </button>
                            <button id="stopSpeakBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                ⏹️ Stop
                            </button>
                        </div>
                    </div>
                    <div id="paragraph-text" class="text-gray-700 leading-relaxed text-lg">
                        @if(isset($activeTest))
                            {{ $activeTest->paragraph->content }}
                        @elseif(isset($test))
                            {{ $test->paragraph->content }}
                        @endif
                    </div>
                </div>

                <!-- Recording Controls -->
                <div class="mb-6">
                    <div class="flex flex-col items-center space-y-4">
                        <!-- Microphone Selection -->
                        <div class="w-full max-w-md">
                            <label for="micSelect" class="block text-sm font-medium text-gray-700 mb-2">Select Microphone:</label>
                            <select id="micSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option>Loading microphones...</option>
                            </select>
                        </div>

                        <!-- Permission Button -->
                        <div class="w-full max-w-md">
                            <button id="requestPermBtn" 
                                    class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Request Microphone Permission
                            </button>
                        </div>

                        <!-- Reset Microphone Button -->
                        <div class="w-full max-w-md">
                            <button id="resetMicBtn" 
                                    class="w-full px-4 py-2 bg-orange-600 text-white font-medium rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                Reset Microphone
                            </button>
                        </div>


                        <!-- Recording Status -->
                        <div id="recording-status" class="text-center">
                            <div id="recording-indicator" class="hidden mb-2">
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></div>
                                    Recording...
                                </div>
                            </div>
                            <div id="audio-level" class="hidden mb-2">
                                <div class="w-64 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="level-bar" class="h-full bg-green-500 transition-all duration-100"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Control Buttons -->
                        <div class="flex space-x-4">
                            <button id="startBtn" 
                                    class="px-6 py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                Start Test
                            </button>
                            <button id="stopBtn" 
                                    class="px-6 py-3 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                Stop Recording
                            </button>
                        </div>

                        <!-- Audio Player -->
                        <div id="audio-player" class="hidden w-full max-w-md">
                            <audio id="player" controls class="w-full"></audio>
                        </div>
                    </div>
                </div>

                <!-- Test Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                        <span>Test Progress</span>
                        <span id="progress-text">Ready to start</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Results Area -->
                <div id="results-area" class="hidden">
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-md">
                        <h3 class="text-lg font-medium text-gray-900 mb-3">Test Results</h3>
                        <div id="results-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple & Robust Microphone Control
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const testId = {{ isset($activeTest) ? $activeTest->id : (isset($test) ? $test->id : 'null') }};
        
        // Get the correct base URL for API requests
        const baseUrl = window.location.origin + window.location.pathname.replace('/test', '');
        console.log('Base URL for API requests:', baseUrl);
        
        // Debug: Log the test ID being used
        console.log('Test ID from server:', testId);
        console.log('Active test:', @json(isset($activeTest) ? $activeTest : null));
        console.log('New test:', @json(isset($test) ? $test : null));
        
        // Debug: Show current user info
        console.log('Current user info:', @json(Auth::user()));
        console.log('Current user ID:', @json(Auth::id()));
        
        // Microphone Status Elements
        const connectionIndicator = document.getElementById('connection-indicator');
        const connectionText = document.getElementById('connection-text');
        const permissionIndicator = document.getElementById('permission-indicator');
        const permissionText = document.getElementById('permission-text');
        const workingIndicator = document.getElementById('working-indicator');
        const workingText = document.getElementById('working-text');
        
        // Update microphone status functions
        function updateConnectionStatus(connected) {
            if (connected) {
                connectionIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                connectionText.textContent = 'Connected';
            } else {
                connectionIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                connectionText.textContent = 'Not Connected';
            }
        }
        
        function updatePermissionStatus(granted) {
            if (granted) {
                permissionIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                permissionText.textContent = 'Permission Granted';
            } else {
                permissionIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                permissionText.textContent = 'No Permission';
            }
        }
        
        function updateWorkingStatus(working) {
            if (working) {
                workingIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                workingText.textContent = 'Working';
            } else {
                workingIndicator.className = 'w-3 h-3 rounded-full bg-yellow-500';
                workingText.textContent = 'Not Tested';
            }
        }
        
        let recordRTC = null;
        let currentStream = null;
        let timerInterval = null;
        let timeRemaining = 300;
        let testStarted = false;
        let isRecording = false;
        
        // DOM Elements
        const micSelect = document.getElementById('micSelect');
        const requestPermBtn = document.getElementById('requestPermBtn');
        const resetMicBtn = document.getElementById('resetMicBtn');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const player = document.getElementById('player');
        const timer = document.getElementById('timer');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultsArea = document.getElementById('results-area');
        const resultsContent = document.getElementById('results-content');
        const speakBtn = document.getElementById('speakBtn');
        const stopSpeakBtn = document.getElementById('stopSpeakBtn');
        const paragraphText = document.getElementById('paragraph-text');
        
        // Speech Functions
        function playParagraph() {
            const text = paragraphText.textContent.trim();
            if (text && speakText(text)) {
                speakBtn.textContent = "🔊 Playing...";
                speakBtn.disabled = true;
                stopSpeakBtn.disabled = false;
                } else {
                alert('Text-to-speech not supported in this browser');
            }
        }
        
        function stopPlaying() {
            stopSpeech();
            speakBtn.textContent = "🔊 Listen to Paragraph";
            speakBtn.disabled = false;
            stopSpeakBtn.disabled = true;
        }
        
        async function requestMicrophonePermission() {
            try {
                console.log('Requesting microphone permission...');
                updatePermissionStatus(false);
                updateConnectionStatus(false);
                updateWorkingStatus(false);
                
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                currentStream = stream;
                updateDeviceList();
                
                // Update status indicators
                updatePermissionStatus(true);
                updateConnectionStatus(true);
                updateWorkingStatus(true);
                
                requestPermBtn.textContent = "✅ Permission Granted";
                requestPermBtn.disabled = true;
                startBtn.disabled = false;
                progressText.textContent = "Microphone ready! Click 'Start Test' to begin.";
                
                console.log('Microphone permission granted successfully');
                return true;
            } catch (err) {
                console.error('Microphone permission error:', err);
                updatePermissionStatus(false);
                updateConnectionStatus(false);
                updateWorkingStatus(false);
                
                let errorMessage = 'Microphone access denied. ';
                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Please allow microphone access and refresh the page.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'No microphone found. Please connect a microphone.';
                } else {
                    errorMessage += 'Error: ' + err.message;
                }
                
                alert(errorMessage);
                return false;
            }
        }
        
        async function updateDeviceList() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const inputs = devices.filter(d => d.kind === 'audioinput');
                
                micSelect.innerHTML = "";
                inputs.forEach((device, index) => {
                    const opt = document.createElement('option');
                    opt.value = device.deviceId;
                    opt.textContent = device.label || `Microphone ${index + 1}`;
                    micSelect.appendChild(opt);
                });
            } catch (err) {
                console.error('Error loading devices:', err);
            }
        }
        
        function startTimer() {
            timerInterval = setInterval(() => {
                timeRemaining--;
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                const progress = ((300 - timeRemaining) / 300) * 100;
                progressBar.style.width = `${progress}%`;
                
                if (timeRemaining <= 0) {
                    stopRecording();
                    alert('Time\'s up! Your test has been automatically submitted.');
                }
            }, 1000);
        }

        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        async function startRecording() {
            try {
                if (!currentStream) {
                    await requestMicrophonePermission();
                }
                
                if (testId && !testStarted) {
                    try {
                        await startTest();
            } catch (err) {
                        console.error('Failed to start test:', err);
                        alert('Cannot start recording: ' + err.message);
                        return; // Stop recording if test start fails
                    }
                }
                
                recordRTC = RecordRTC(currentStream, {
                    type: 'audio',
                    mimeType: 'audio/webm;codecs=opus',
                    audioBitsPerSecond: 128000,
                    timeSlice: 1000,
                    ondataavailable: function(blob) {
                        console.log('Audio chunk received:', blob.size, 'bytes');
                    }
                });
                
                recordRTC.startRecording();
                isRecording = true;
                
                startBtn.disabled = true;
                stopBtn.disabled = false;
                progressText.textContent = 'Recording in progress...';
                startTimer();
                
            } catch (err) {
                console.error('Recording error:', err);
                alert('Could not start recording: ' + err.message);
            }
        }
        
        function stopRecording() {
            if (recordRTC && isRecording) {
                recordRTC.stopRecording(async () => {
                    const blob = recordRTC.getBlob();
                    const audioUrl = URL.createObjectURL(blob);
                    player.src = audioUrl;
                    document.getElementById('audio-player').classList.remove('hidden');
                    
                    await submitTest(blob);
                });
                
                isRecording = false;
                stopTimer();
                startBtn.disabled = false;
                stopBtn.disabled = true;
                progressText.textContent = 'Processing recording...';
            }
        }
        
        async function startTest() {
            try {
                console.log('Starting test with ID:', testId);
                
                // Validate test ID
                if (!testId || testId === 'null') {
                    throw new Error('No valid test ID found. Please refresh the page.');
                }
                
        // Check if test belongs to current user
        const currentUserId = @json(Auth::id());
        const testUserId = @json(isset($activeTest) ? $activeTest->user_id : (isset($test) ? $test->user_id : null));
        
        console.log('Current user ID:', currentUserId);
        console.log('Test user ID:', testUserId);
        
        if (testUserId && testUserId !== currentUserId) {
            console.error('SECURITY ISSUE: Test does not belong to current user!');
            console.error('Current user:', currentUserId, 'Test owner:', testUserId);
            alert('Security Error: Test does not belong to current user. Please logout and login again.');
            window.location.href = '/logout';
            return;
        }
        
        // Additional check: If test ID is 12 and user is 1, force reload
        if (testId === 12 && currentUserId === 1) {
            console.error('WRONG TEST ID: User 1 trying to access Test ID 12');
            alert('Wrong test detected. Please refresh the page.');
            window.location.reload();
            return;
        }
                
                // Create FormData instead of JSON to ensure proper parsing
                const formData = new FormData();
                formData.append('test_id', testId);
                formData.append('_token', csrfToken);
                
                const response = await fetch(baseUrl + '/test/start', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                console.log('Start test response status:', response.status);
                
                // Handle redirect responses (middleware redirects)
                if (response.redirected || response.status === 302) {
                    throw new Error('Access denied. Please check your payment status.');
                }
                
                // Handle payment required
                if (response.status === 402) {
                    const data = await response.json();
                    throw new Error(data.message || 'Payment required to take test.');
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response from start test:', text);
                    
                    // Check if it's an HTML error page
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        throw new Error('Server returned HTML instead of JSON. This might be a payment or authentication issue.');
                    }
                    
                    throw new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
                }
                
                const data = await response.json();
                console.log('Start test response data:', data);
                
                if (data.success) {
                    testStarted = true;
                    console.log('Test started successfully');
                } else {
                    throw new Error('Failed to start test: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Test start error:', err);
                alert('Error starting test: ' + err.message);
                throw err; // Re-throw to prevent recording if test start fails
            }
        }
        
        async function submitTest(audioBlob) {
            try {
                const formData = new FormData();
                formData.append('audio_file', audioBlob, 'test-recording.webm');
                formData.append('test_id', testId);
                formData.append('duration', 300 - timeRemaining);

                console.log('Submitting test with:', {
                    testId: testId,
                    duration: 300 - timeRemaining,
                    audioSize: audioBlob.size
                });

                const response = await fetch(baseUrl + '/test/submit', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest' // Important for Laravel to detect AJAX
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error(`Server returned non-JSON response (${response.status}): ${text.substring(0, 200)}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    displayResults(data);
                } else {
                    alert('Test submission failed: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Submit error:', err);
                alert('Error submitting test: ' + err.message);
            }
        }

        function displayResults(data) {
            const attempt = data.attempt;
            const passed = data.passed;
            
            resultsContent.innerHTML = `
                <div class="text-center mb-6">
                    <h4 class="text-2xl font-bold ${passed ? 'text-green-600' : 'text-red-600'}">
                        ${passed ? '🎉 Congratulations! You Passed!' : '❌ Test Not Passed'}
                        </h4>
                    <p class="text-gray-600 text-lg">Overall Score: ${attempt.overall_score}%</p>
                    </div>
                    
                <div class="grid grid-cols-3 gap-4 text-center mb-6">
                    <div class="p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">${attempt.accuracy_score}%</div>
                            <div class="text-sm text-gray-600">Accuracy</div>
                        </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">${attempt.fluency_score}%</div>
                            <div class="text-sm text-gray-600">Fluency</div>
                        </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">${attempt.pronunciation_score}%</div>
                            <div class="text-sm text-gray-600">Pronunciation</div>
                        </div>
                    </div>
                    
                <div class="p-4 bg-gray-50 rounded-lg mb-6">
                    <h5 class="font-semibold text-gray-900 mb-2">Feedback:</h5>
                        <p class="text-gray-700">${attempt.feedback}</p>
                    </div>
                    
                    <div class="text-center">
                    <a href="/dashboard" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">
                            Back to Dashboard
                        </a>
                </div>
            `;
            
            resultsArea.classList.remove('hidden');
            progressText.textContent = 'Test completed';
        }

        // Event Listeners
        requestPermBtn.addEventListener('click', requestMicrophonePermission);
        startBtn.addEventListener('click', startRecording);
        stopBtn.addEventListener('click', stopRecording);
        speakBtn.addEventListener('click', playParagraph);
        stopSpeakBtn.addEventListener('click', stopPlaying);
        
        resetMicBtn.addEventListener('click', () => {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            if (recordRTC) {
                recordRTC.destroy();
                recordRTC = null;
            }
            stopTimer();
            startBtn.disabled = true;
            stopBtn.disabled = true;
            requestPermBtn.disabled = false;
            requestPermBtn.textContent = "Request Microphone Permission";
            progressText.textContent = "Microphone reset. Click 'Request Microphone Permission' to begin.";
            
            // Reset status indicators
            updatePermissionStatus(false);
            updateConnectionStatus(false);
            updateWorkingStatus(false);
        });

        // Initialize
        updateDeviceList();
        initializeMicrophoneStatus(); // Initialize microphone status indicators
        autoPlayParagraph(); // Auto-play paragraph when page loads
    </script>
</body>
</html>