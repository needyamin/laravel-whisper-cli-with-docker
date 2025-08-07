<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IELTS Speech Tools</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="max-w-4xl mx-auto py-10 px-4">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-blue-700">IELTS Speech Tools</h1>
            <p class="text-gray-600 mt-2">Convert speech to text or generate speech from your text instantly.</p>
        </div>

        <!-- Card: Speech to Text -->
        <div class="bg-white shadow-md rounded-xl p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">🎤 Speech to Text</h2>
            
            <div class="flex flex-col items-center space-y-4">
                <button id="recordBtn" 
                    class="bg-red-600 text-white py-2 px-6 rounded-lg hover:bg-red-700 transition-all duration-200">
                    🎙 Start Recording
                </button>
                <audio id="recordedAudio" controls class="hidden w-full mt-2"></audio>
                <button id="sendAudioBtn" disabled 
                    class="bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition-all duration-200">
                    ⬆ Upload & Transcribe
                </button>
            </div>

            <div id="transcriptOutput" class="mt-4 p-3 bg-gray-100 rounded hidden"></div>
        </div>

        <!-- Card: Text to Speech -->
        <div class="bg-white shadow-md rounded-xl p-6">
            <h2 class="text-xl font-semibold mb-4">📝 Text to Speech</h2>
            <form id="ttsForm" class="space-y-4">
                <textarea name="text" id="textInput" rows="4" placeholder="Enter text here..." required
                    class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                <button type="submit" 
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-all duration-200">
                    Generate Speech
                </button>
            </form>

            <audio id="ttsAudio" controls class="w-full mt-4 hidden"></audio>
        </div>
    </div>

    <!-- Script -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let mediaRecorder;
        let audioChunks = [];
        let audioBlob;

        const recordBtn = document.getElementById('recordBtn');
        const sendAudioBtn = document.getElementById('sendAudioBtn');
        const recordedAudio = document.getElementById('recordedAudio');
        const transcriptOutput = document.getElementById('transcriptOutput');

        recordBtn.addEventListener('click', async () => {
            if (mediaRecorder && mediaRecorder.state === "recording") {
                mediaRecorder.stop();
                recordBtn.textContent = "🎙 Start Recording";
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);

                audioChunks = [];
                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    recordedAudio.src = audioUrl;
                    recordedAudio.classList.remove('hidden');
                    sendAudioBtn.disabled = false;
                };

                mediaRecorder.start();
                recordBtn.textContent = "⏹ Stop Recording";

            } catch (err) {
                alert("Microphone access denied or not available.");
                console.error(err);
            }
        });

        sendAudioBtn.addEventListener('click', async () => {
            if (!audioBlob) return;

            const formData = new FormData();
            formData.append('audio', audioBlob, 'recording.webm');

            try {
                const response = await fetch(`{{ url('/speech-to-text') }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });

                const result = await response.json();
                if (result.transcript) {
                    transcriptOutput.textContent = result.transcript;
                    transcriptOutput.classList.remove('hidden');
                } else {
                    alert("Error: " + (result.error || "Unknown error"));
                }
            } catch (error) {
                alert("Upload failed. See console for details.");
                console.error(error);
            }
        });

        document.getElementById('ttsForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const text = document.getElementById('textInput').value;

            try {
                const response = await fetch(`{{ url('/text-to-speech') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ text })
                });

                const data = await response.json();

                if (data.audio_url) {
                    const audio = document.getElementById('ttsAudio');
                    audio.src = data.audio_url;
                    audio.classList.remove('hidden');
                    audio.play();
                } else {
                    alert('TTS failed: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                alert("TTS request failed. See console for details.");
                console.error(error);
            }
        });
    </script>
</body>
</html>
