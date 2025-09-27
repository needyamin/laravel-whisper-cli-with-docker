<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speaking Test - English Speaking Test</title>
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

                <!-- Microphone Setup Instructions -->
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <h3 class="text-lg font-medium text-yellow-900 mb-2">Microphone Setup:</h3>
                    <div class="text-yellow-800 space-y-2">
                        <p><strong>Step 1:</strong> Click "Request Microphone Permission" below</p>
                        <p><strong>Step 2:</strong> When your browser asks for permission, click "Allow"</p>
                        <p><strong>Step 3:</strong> Select your microphone from the dropdown</p>
                        <p><strong>Step 4:</strong> Click "Start Test" to begin recording</p>
                        <p class="text-sm text-yellow-700 mt-2">
                            <strong>Note:</strong> If you don't see the permission prompt, check your browser's address bar for a microphone icon and click it to allow access.
                        </p>
                    </div>
                </div>

                <!-- Paragraph to Read -->
                <div class="mb-6 p-6 bg-gray-50 border border-gray-200 rounded-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Read this paragraph:</h3>
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const testId = {{ isset($activeTest) ? $activeTest->id : (isset($test) ? $test->id : 'null') }};
        
        let mediaRecorder = null;
        let audioChunks = [];
        let currentStream = null;
        let timerInterval = null;
        let timeRemaining = 300; // 5 minutes in seconds
        let testStarted = false;
        
        console.log('=== TEST INITIALIZATION ===');
        console.log('testId:', testId);
        console.log('activeTest:', @json(isset($activeTest) ? $activeTest : null));
        console.log('test:', @json(isset($test) ? $test : null));
        console.log('testStarted:', testStarted);

        const micSelect = document.getElementById('micSelect');
        const requestPermBtn = document.getElementById('requestPermBtn');
        const resetMicBtn = document.getElementById('resetMicBtn');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const player = document.getElementById('player');
        const recordingIndicator = document.getElementById('recording-indicator');
        const audioLevel = document.getElementById('audio-level');
        const levelBar = document.getElementById('level-bar');
        const audioPlayer = document.getElementById('audio-player');
        const timer = document.getElementById('timer');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultsArea = document.getElementById('results-area');
        const resultsContent = document.getElementById('results-content');

        // Initialize microphone selection with automatic working mic detection
        async function updateDeviceList() {
            try {
                console.log('Updating device list...');
                const devices = await navigator.mediaDevices.enumerateDevices();
                const inputs = devices.filter(d => d.kind === 'audioinput');
                console.log('Found audio inputs:', inputs);
                
                micSelect.innerHTML = "";
                
                if (inputs.length === 0) {
                    micSelect.innerHTML = "<option value=''>No microphones found</option>";
                    console.log('No microphones found');
                    return;
                }
                
                // Add options for each microphone
                inputs.forEach((device, index) => {
                    const opt = document.createElement('option');
                    opt.value = device.deviceId;
                    opt.textContent = device.label || `Microphone ${index + 1}`;
                    micSelect.appendChild(opt);
                });
                
                // Auto-select the best working microphone
                await autoSelectWorkingMicrophone(inputs);
                
            } catch (err) {
                console.error('Error enumerating devices:', err);
                micSelect.innerHTML = "<option value=''>Error loading microphones</option>";
            }
        }

        // Auto-select working microphone by testing each one
        async function autoSelectWorkingMicrophone(devices) {
            console.log('Auto-selecting working microphone...');
            
            // First, try to get a default stream to test basic access
            try {
                console.log('Testing default microphone access...');
                const defaultStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('Default microphone works:', defaultStream);
                
                // Get the device ID from the default stream
                const audioTrack = defaultStream.getAudioTracks()[0];
                if (audioTrack) {
                    const deviceId = audioTrack.getSettings().deviceId;
                    console.log('Default microphone device ID:', deviceId);
                    
                    // Find matching device in our list
                    const matchingDevice = devices.find(d => d.deviceId === deviceId);
                    if (matchingDevice) {
                        console.log('Setting default microphone:', matchingDevice.label);
                        micSelect.value = deviceId;
                        localStorage.setItem('preferredMicDeviceId', deviceId);
                        
                        // Stop the test stream
                        defaultStream.getTracks().forEach(track => track.stop());
                        return;
                    }
                }
                
                // Stop the test stream
                defaultStream.getTracks().forEach(track => track.stop());
            } catch (err) {
                console.error('Default microphone test failed:', err);
            }
            
            // If default doesn't work, try each device individually
            for (let i = 0; i < devices.length; i++) {
                const device = devices[i];
                console.log(`Testing microphone ${i + 1}:`, device.label);
                
                try {
                    const testStream = await navigator.mediaDevices.getUserMedia({
                        audio: { deviceId: { exact: device.deviceId } }
                    });
                    
                    console.log(`✅ Microphone ${i + 1} works:`, device.label);
                    
                    // This microphone works, select it
                    micSelect.value = device.deviceId;
                    localStorage.setItem('preferredMicDeviceId', device.deviceId);
                    
                    // Stop the test stream
                    testStream.getTracks().forEach(track => track.stop());
                    
                    console.log('Auto-selected working microphone:', device.label);
                    return;
                    
                } catch (err) {
                    console.log(`❌ Microphone ${i + 1} failed:`, device.label, err.name);
                    // Continue to next microphone
                }
            }
            
            // If no specific device works, try without device constraint
            console.log('No specific device worked, trying default...');
            try {
                const fallbackStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('✅ Fallback microphone works');
                
                // Select the first available option (default)
                if (micSelect.options.length > 0) {
                    micSelect.selectedIndex = 0;
                    console.log('Selected first available microphone as fallback');
                }
                
                // Stop the test stream
                fallbackStream.getTracks().forEach(track => track.stop());
                
            } catch (err) {
                console.error('❌ All microphones failed:', err);
                // Leave micSelect empty - user will need to manually select
            }
        }

        // Reset microphone completely
        async function resetMicrophone() {
            console.log('Resetting microphone...');
            
            try {
                // Stop any active recording
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    console.log('Stopping active recording...');
                    mediaRecorder.stop();
                    mediaRecorder = null;
                }
                
                // Stop any active timer
                stopTimer();
                
                // Stop current stream
                if (currentStream) {
                    console.log('Stopping current stream...');
                    currentStream.getTracks().forEach(track => {
                        console.log('Stopping track:', track.label);
                        track.stop();
                    });
                    currentStream = null;
                }
                
                // Reset UI state
                startBtn.disabled = true;
                stopBtn.disabled = true;
                recordingIndicator.classList.add('hidden');
                audioLevel.classList.add('hidden');
                audioPlayer.classList.add('hidden');
                resultsArea.classList.add('hidden');
                
                // Reset progress
                progressText.textContent = 'Microphone reset. Click "Request Microphone Permission" to begin.';
                progressBar.style.width = '0%';
                timer.textContent = '05:00';
                timeRemaining = 300;
                
                // Reset permission button
                requestPermBtn.disabled = false;
                requestPermBtn.textContent = "Request Microphone Permission";
                requestPermBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                requestPermBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                
                // Clear microphone selection
                micSelect.innerHTML = "<option>Loading microphones...</option>";
                
                // Clear localStorage
                localStorage.removeItem('preferredMicDeviceId');
                
                // Wait a moment for cleanup
                await new Promise(resolve => setTimeout(resolve, 500));
                
                console.log('Microphone reset completed');
                alert('Microphone has been reset. Please click "Request Microphone Permission" to set up again.');
                
            } catch (err) {
                console.error('Error resetting microphone:', err);
                alert('Error resetting microphone: ' + err.message);
            }
        }

        // Force refresh microphone devices
        async function forceRefreshDevices() {
            console.log('Force refreshing microphone devices...');
            
            try {
                // Clear current device list
                micSelect.innerHTML = "<option>Refreshing microphones...</option>";
                
                // Wait a moment
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Re-enumerate devices
                await updateDeviceList();
                
                console.log('Device refresh completed');
                
            } catch (err) {
                console.error('Error refreshing devices:', err);
                micSelect.innerHTML = "<option>Error refreshing microphones</option>";
            }
        }

        // Show permission state (best-effort)
        async function refreshPermissionStatus() {
            if (!navigator.permissions || !navigator.permissions.query) {
                return;
            }
            try {
                const p = await navigator.permissions.query({ name: 'microphone' });
                p.onchange = () => {
                    // Permission state changed
                };
            } catch (err) {
                // Fallback — treat as unknown
            }
        }

        // Simplified microphone access function
        async function ensureStream() {
            console.log('ensureStream called');
            
            // Clean up any existing stream
            if (currentStream) {
                console.log('Cleaning up existing stream');
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            
            try {
                console.log('Requesting microphone access...');
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('✅ Microphone access granted');
                
                // Verify stream has audio tracks
                const audioTracks = stream.getAudioTracks();
                if (audioTracks.length === 0) {
                    throw new Error('No audio tracks found in stream');
                }
                
                // Verify track is live
                const audioTrack = audioTracks[0];
                if (audioTrack.readyState !== 'live') {
                    throw new Error('Audio track is not live');
                }
                
                console.log('✅ Stream verified and working');
                currentStream = stream;
                return stream;
                
            } catch (err) {
                console.error('❌ Microphone access failed:', err);
                
                let errorMessage = 'Microphone access failed: ';
                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Permission denied. Please allow microphone access and refresh the page.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'No microphone found. Please connect a microphone.';
                } else if (err.name === 'NotReadableError') {
                    errorMessage += 'Microphone is being used by another application.';
                } else {
                    errorMessage += err.message;
                }
                
                alert(errorMessage);
                throw new Error(errorMessage);
            }
        }

        // Start timer
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

        // Stop timer
        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        // Start recording
        async function startRecording() {
            console.log('startRecording called');
            audioChunks = [];
            try {
                // First, start the test if it's not already started
                if (testId && !testStarted) {
                    console.log('=== STARTING TEST ===');
                    console.log('testId:', testId);
                    console.log('testStarted:', testStarted);
                    
                    const startResponse = await fetch('/test/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ test_id: testId })
                    });
                    
                    console.log('Start test response status:', startResponse.status);
                    console.log('Start test response headers:', startResponse.headers);
                    
                    if (!startResponse.ok) {
                        const errorText = await startResponse.text();
                        console.error('Start test failed:', errorText);
                        throw new Error('Failed to start test: ' + errorText);
                    }
                    
                    const startData = await startResponse.json();
                    console.log('Start test response data:', startData);
                    
                    if (startData.success) {
                        testStarted = true;
                        console.log('Test started successfully');
                    } else {
                        console.error('Start test failed:', startData.error);
                        throw new Error('Failed to start test: ' + startData.error);
                    }
                } else {
                    console.log('Skipping test start - testId:', testId, 'testStarted:', testStarted);
                }
                
                // Check if we have microphone access first
                if (!currentStream) {
                    progressText.textContent = 'Requesting microphone access...';
                    await ensureStream();
                }
                
                const stream = await ensureStream(); // Will prompt only if permission not already granted
                console.log('Got stream for recording:', stream);
                
                // Verify stream is working before creating MediaRecorder
                const audioTracks = stream.getAudioTracks();
                if (audioTracks.length === 0) {
                    throw new Error('No audio tracks available in stream');
                }
                
                const audioTrack = audioTracks[0];
                if (audioTrack.readyState !== 'live') {
                    throw new Error('Audio track is not live');
                }
                
                console.log('Creating MediaRecorder with stream');
                
                // Try different MediaRecorder options
                const mimeTypes = [
                    'audio/webm;codecs=opus',
                    'audio/webm',
                    'audio/mp4',
                    'audio/wav'
                ];
                
                let mimeType = null;
                for (const type of mimeTypes) {
                    if (MediaRecorder.isTypeSupported(type)) {
                        mimeType = type;
                        console.log('Using MIME type:', mimeType);
                        break;
                    }
                }
                
                const options = mimeType ? { mimeType } : {};
                console.log('MediaRecorder options:', options);
                
                try {
                    mediaRecorder = new MediaRecorder(stream, options);
                    console.log('MediaRecorder created successfully');
                } catch (err) {
                    console.error('Failed to create MediaRecorder with options:', err);
                    // Try without options
                    mediaRecorder = new MediaRecorder(stream);
                    console.log('MediaRecorder created without options');
                }
                
                mediaRecorder.ondataavailable = event => {
                    console.log('Data available:', event.data.size, 'bytes');
                    if (event.data && event.data.size) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstart = () => {
                    console.log('Recording started');
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
                    stopBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    stopBtn.classList.add('hover:bg-red-700');
                    recordingIndicator.classList.remove('hidden');
                    audioLevel.classList.remove('hidden');
                    progressText.textContent = 'Recording in progress...';
                    startTimer();
                };

                mediaRecorder.onerror = (event) => {
                    console.error('MediaRecorder error:', event.error);
                    alert('Recording error: ' + event.error);
                };

                console.log('Starting MediaRecorder...');
                mediaRecorder.start(1000); // Record in 1-second chunks
            } catch (err) {
                console.error('Error starting recording:', err);
                
                // Reset button states
                startBtn.disabled = false;
                stopBtn.disabled = true;
                recordingIndicator.classList.add('hidden');
                audioLevel.classList.add('hidden');
                
                // Show specific error message
                if (err.name === 'NotAllowedError') {
                    progressText.textContent = 'Microphone permission denied. Please click "Request Microphone Permission" first.';
                    alert('Microphone permission is required to start recording. Please click "Request Microphone Permission" and allow access when prompted.');
                } else if (err.name === 'NotFoundError') {
                    progressText.textContent = 'No microphone found. Please connect a microphone.';
                    alert('No microphone detected. Please connect a microphone and try again.');
                } else {
                    progressText.textContent = 'Could not start recording. Please check microphone permissions.';
                    alert('Could not start recording. Please check microphone permissions and try again.');
                }
                
                await refreshPermissionStatus();
            }
        }

        // Stop recording
        function stopRecording() {
            console.log('stopRecording called');
            console.log('mediaRecorder:', mediaRecorder);
            console.log('mediaRecorder state:', mediaRecorder?.state);
            console.log('stopBtn disabled:', stopBtn.disabled);
            
            // Force stop regardless of state
            if (mediaRecorder) {
                console.log('MediaRecorder exists, attempting to stop...');
                
                // Stop timer immediately
                stopTimer();
                
                // Update UI immediately
                startBtn.disabled = false;
                stopBtn.disabled = true;
                stopBtn.classList.add('opacity-50', 'cursor-not-allowed');
                stopBtn.classList.remove('hover:bg-red-700');
                recordingIndicator.classList.add('hidden');
                audioLevel.classList.add('hidden');
                progressText.textContent = 'Processing recording...';
                
                // Set up the onstop handler BEFORE calling stop()
                let stopTimeout;
                mediaRecorder.onstop = async () => {
                    console.log('MediaRecorder onstop event fired');
                    clearTimeout(stopTimeout); // Clear the timeout since stop worked
                    
                    try {
                        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                        console.log('Audio blob created:', audioBlob.size, 'bytes');
                        
                        if (audioBlob.size > 0) {
                            const audioUrl = URL.createObjectURL(audioBlob);
                            player.src = audioUrl;
                            audioPlayer.classList.remove('hidden');
                            
                            // Submit test
                            await submitTest(audioBlob);
                        } else {
                            console.error('No audio data recorded');
                            alert('No audio was recorded. Please try again.');
                            progressText.textContent = 'No audio recorded. Please try again.';
                        }
                    } catch (err) {
                        console.error('Error processing audio:', err);
                        alert('Error processing recording: ' + err.message);
                        progressText.textContent = 'Error processing recording.';
                    }
                    
                    // Clean up
                    mediaRecorder = null;
                    audioChunks = [];
                };
                
                // Set a timeout to force cleanup if MediaRecorder doesn't stop
                stopTimeout = setTimeout(() => {
                    console.log('MediaRecorder stop timeout - forcing cleanup');
                    mediaRecorder = null;
                    audioChunks = [];
                    progressText.textContent = 'Recording stopped (timeout).';
                }, 5000); // 5 second timeout
                
                // Now call stop
                try {
                    if (mediaRecorder.state === 'recording') {
                        console.log('Stopping MediaRecorder (was recording)...');
                        mediaRecorder.stop();
                    } else {
                        console.log('MediaRecorder state was:', mediaRecorder.state, '- calling stop anyway');
                        mediaRecorder.stop();
                    }
                } catch (err) {
                    console.error('Error calling mediaRecorder.stop():', err);
                    // Force cleanup if stop fails
                    mediaRecorder = null;
                    audioChunks = [];
                    progressText.textContent = 'Recording stopped with error.';
                }
            } else {
                console.log('No MediaRecorder found - forcing cleanup');
                stopTimer();
                startBtn.disabled = false;
                stopBtn.disabled = true;
                recordingIndicator.classList.add('hidden');
                audioLevel.classList.add('hidden');
                progressText.textContent = 'No recording found.';
            }
        }

        // Submit test
        async function submitTest(audioBlob) {
            try {
                progressText.textContent = 'Submitting test...';
                
                const formData = new FormData();
                formData.append('audio_file', audioBlob, 'test-recording.webm');
                formData.append('test_id', testId);
                formData.append('duration', 300 - timeRemaining);

                console.log('=== SUBMITTING TEST ===');
                console.log('Submitting test with:', {
                    testId: testId,
                    duration: 300 - timeRemaining,
                    audioSize: audioBlob.size,
                    testStarted: testStarted
                });
                
                if (!testId) {
                    throw new Error('No test ID available for submission');
                }

                const response = await fetch('/test/submit', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
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
                    console.error('Test submission failed:', data);
                    alert('Test submission failed: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error submitting test:', err);
                alert('Error submitting test. Please try again. Error: ' + err.message);
            }
        }

        // Display results
        function displayResults(data) {
            const attempt = data.attempt;
            const passed = data.passed;
            
            resultsContent.innerHTML = `
                <div class="space-y-4">
                    <div class="text-center">
                        <h4 class="text-xl font-semibold ${passed ? 'text-green-600' : 'text-red-600'}">
                            ${passed ? 'Congratulations! You Passed!' : 'Test Not Passed'}
                        </h4>
                        <p class="text-gray-600">Overall Score: ${attempt.overall_score}%</p>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-blue-600">${attempt.accuracy_score}%</div>
                            <div class="text-sm text-gray-600">Accuracy</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600">${attempt.fluency_score}%</div>
                            <div class="text-sm text-gray-600">Fluency</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600">${attempt.pronunciation_score}%</div>
                            <div class="text-sm text-gray-600">Pronunciation</div>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-gray-100 rounded-md">
                        <h5 class="font-medium text-gray-900 mb-2">Feedback:</h5>
                        <p class="text-gray-700">${attempt.feedback}</p>
                    </div>
                    
                    ${data.certificate_generated ? 
                        '<div class="text-center"><a href="/certificates" class="text-indigo-600 hover:text-indigo-800 font-medium">View Your Certificate</a></div>' : 
                        ''
                    }
                    
                    <div class="text-center">
                        <a href="/dashboard" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            `;
            
            resultsArea.classList.remove('hidden');
            progressText.textContent = 'Test completed';
        }

        // Event listeners
        startBtn.addEventListener('click', startRecording);
        stopBtn.addEventListener('click', (e) => {
            console.log('Stop button clicked!');
            console.log('Event:', e);
            console.log('Button disabled:', stopBtn.disabled);
            console.log('Button element:', stopBtn);
            stopRecording();
        });
        
        // Reset microphone button
        resetMicBtn.addEventListener('click', resetMicrophone);
        
        // Request permission and list devices (useful to trigger browser prompt)
        requestPermBtn.addEventListener('click', async () => {
            console.log('=== PERMISSION BUTTON CLICKED ===');
            try {
                progressText.textContent = "Requesting microphone permission...";
                requestPermBtn.disabled = true;
                requestPermBtn.textContent = "Requesting...";
                
                // Get microphone access
                await ensureStream();
                console.log('✅ Microphone access successful');
                
                // Update device list
                await updateDeviceList();
                console.log('✅ Device list updated');
                
                // Update UI
                progressText.textContent = "Permission granted! Click 'Start Test' when ready.";
                requestPermBtn.textContent = "Permission Granted ✓";
                requestPermBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                requestPermBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                requestPermBtn.disabled = true;
                
                // Enable start button
                startBtn.disabled = false;
                console.log('✅ Permission request completed successfully');
                
            } catch (err) {
                console.error('❌ Permission request failed:', err);
                progressText.textContent = 'Permission error: ' + err.message;
                requestPermBtn.disabled = false;
                requestPermBtn.textContent = "Request Microphone Permission";
                
                // Show specific error message
                if (err.name === 'NotAllowedError') {
                    alert('Microphone permission was denied. Please:\n1. Click the microphone icon in your browser\'s address bar\n2. Select "Allow" for microphone access\n3. Refresh the page and try again');
                } else if (err.name === 'NotFoundError') {
                    alert('No microphone found. Please:\n1. Connect a microphone to your device\n2. Refresh the page\n3. Try again');
                } else {
                    alert('Error requesting microphone permission: ' + err.message);
                }
            }
        });
        
        // Save selection when user changes mic
        micSelect.addEventListener('change', () => {
            const val = micSelect.value || "";
            localStorage.setItem('preferredMicDeviceId', val);
        });

        // Debug function
        async function debugTest() {
            try {
                const response = await fetch('/debug/test-submit', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                console.log('Debug response:', data);
                return data;
            } catch (err) {
                console.error('Debug error:', err);
                return null;
            }
        }

        // Test debug POST function
        async function testDebugPost() {
            try {
                const formData = new FormData();
                formData.append('test_id', testId);
                formData.append('audio_file', new Blob(['test'], { type: 'audio/webm' }), 'test.webm');
                
                const response = await fetch('/debug/test-submit', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const data = await response.json();
                console.log('Debug POST response:', data);
                return data;
            } catch (err) {
                console.error('Debug POST error:', err);
                return null;
            }
        }

        // Manual stop recording function for debugging
        window.manualStopRecording = function() {
            console.log('=== MANUAL STOP RECORDING DEBUG ===');
            console.log('mediaRecorder:', mediaRecorder);
            console.log('mediaRecorder state:', mediaRecorder?.state);
            console.log('stopBtn:', stopBtn);
            console.log('stopBtn disabled:', stopBtn.disabled);
            console.log('audioChunks length:', audioChunks.length);
            
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                console.log('Manually stopping recording...');
                stopRecording();
            } else {
                console.log('Cannot stop - MediaRecorder not in recording state');
            }
        };

        // Force stop everything - emergency function
        window.forceStopEverything = function() {
            console.log('=== FORCE STOP EVERYTHING ===');
            
            // Stop timer
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
                console.log('Timer stopped');
            }
            
            // Stop MediaRecorder
            if (mediaRecorder) {
                try {
                    mediaRecorder.stop();
                    console.log('MediaRecorder.stop() called');
                } catch (err) {
                    console.log('Error stopping MediaRecorder:', err);
                }
                mediaRecorder = null;
            }
            
            // Reset UI
            startBtn.disabled = false;
            stopBtn.disabled = true;
            stopBtn.classList.add('opacity-50', 'cursor-not-allowed');
            stopBtn.classList.remove('hover:bg-red-700');
            recordingIndicator.classList.add('hidden');
            audioLevel.classList.add('hidden');
            progressText.textContent = 'Recording force stopped.';
            
            // Clear audio chunks
            audioChunks = [];
            
            console.log('Force stop completed');
        };

        // Debug microphone function - call this from browser console
        window.debugMicrophone = async function() {
            console.log('=== MICROPHONE DEBUG START ===');
            console.log('Current stream:', currentStream);
            console.log('MediaRecorder support:', typeof MediaRecorder !== 'undefined');
            console.log('getUserMedia support:', !!navigator.mediaDevices?.getUserMedia);
            
            try {
                console.log('Testing basic getUserMedia...');
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('✅ Basic getUserMedia successful:', stream);
                console.log('Audio tracks:', stream.getAudioTracks());
                
                // Stop the test stream
                stream.getTracks().forEach(track => track.stop());
                console.log('Test stream stopped');
                
                return { success: true, message: 'Microphone access working' };
            } catch (err) {
                console.error('❌ Basic getUserMedia failed:', err);
                console.error('Error name:', err.name);
                console.error('Error message:', err.message);
                return { success: false, error: err.name, message: err.message };
            }
        };

        // Firefox-specific microphone test
        window.debugFirefoxMic = async function() {
            console.log('=== FIREFOX MICROPHONE DEBUG ===');
            console.log('User Agent:', navigator.userAgent);
            console.log('Protocol:', location.protocol);
            console.log('HTTPS:', location.protocol === 'https:');
            
            const constraints = [
                { audio: true },
                { audio: { echoCancellation: true } },
                { audio: { echoCancellation: false } },
                { audio: { noiseSuppression: true } },
                { audio: { noiseSuppression: false } },
                { audio: { autoGainControl: true } },
                { audio: { autoGainControl: false } },
                { audio: {} }
            ];
            
            for (let i = 0; i < constraints.length; i++) {
                try {
                    console.log(`Testing constraint ${i + 1}:`, constraints[i]);
                    const stream = await navigator.mediaDevices.getUserMedia(constraints[i]);
                    console.log(`✅ Constraint ${i + 1} successful:`, stream);
                    
                    const tracks = stream.getAudioTracks();
                    console.log('Audio tracks:', tracks);
                    tracks.forEach(track => {
                        console.log('Track settings:', track.getSettings());
                        console.log('Track constraints:', track.getConstraints());
                    });
                    
                    // Stop the test stream
                    stream.getTracks().forEach(track => track.stop());
                    console.log(`Constraint ${i + 1} test completed`);
                    
                } catch (err) {
                    console.log(`❌ Constraint ${i + 1} failed:`, err.name, err.message);
                }
            }
            
            return { message: 'Firefox microphone test completed' };
        };

        // Initialize
        (async function init() {
            // Debug authentication and CSRF
            const debugInfo = await debugTest();
            console.log('Debug info:', debugInfo);
            
            // Test POST debug endpoint
            const debugPostInfo = await testDebugPost();
            console.log('Debug POST info:', debugPostInfo);
            
            // Check if test is already in progress
            if (testId) {
                testStarted = {{ isset($activeTest) && $activeTest->status === 'in_progress' ? 'true' : 'false' }};
                console.log('Test status:', testStarted ? 'in_progress' : 'pending');
            }
            
            // If page served insecurely (file://) permissions won't persist — warn user
            if (location.protocol === 'file:') {
                console.warn("Warning: file:// pages don't persist microphone permissions. Serve this page from http://127.0.0.1 or https://");
            }
            
            // Check if we already have microphone permission
            try {
                console.log('Checking microphone permission...');
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                console.log('✅ Microphone permission already granted');
                
                // Verify microphone is working
                const audioTracks = stream.getAudioTracks();
                if (audioTracks.length > 0 && audioTracks[0].readyState === 'live') {
                    console.log('✅ Microphone is working');
                    
                    // Update UI to show permission granted
                    requestPermBtn.textContent = "Permission Granted ✓";
                    requestPermBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    requestPermBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    requestPermBtn.disabled = true;
                    startBtn.disabled = false;
                    progressText.textContent = "Microphone ready! Click 'Start Test' to begin.";
                    
                    // Store the stream
                    currentStream = stream;
                } else {
                    throw new Error('Microphone not working properly');
                }
            } catch (err) {
                console.log('Microphone permission not granted yet:', err.message);
                requestPermBtn.disabled = false;
                startBtn.disabled = true;
                progressText.textContent = "Click 'Request Microphone Permission' to begin.";
            }
            
            // Update device list
            await updateDeviceList();
        })();

        // Cleanup on unload
        window.addEventListener('beforeunload', () => {
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
                currentStream = null;
            }
        });
    </script>
</body>
</html>
