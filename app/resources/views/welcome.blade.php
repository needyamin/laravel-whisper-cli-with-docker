<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    
    <!-- Speech to Text -->
    <h2>Speech to Text</h2>
    <form action="/speech-to-text" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="audio" accept="audio/*" required>
        <button type="submit">Transcribe</button>
    </form>

    <hr>

    <!-- Text to Speech -->
    <h2>Text to Speech</h2>
    <form id="ttsForm">
        @csrf
        <textarea name="text" id="textInput" rows="4" cols="50" placeholder="Enter text here..." required></textarea><br>
        <button type="submit">Generate Speech</button>
    </form>

    <audio id="ttsAudio" controls style="margin-top: 15px; display: none;"></audio>

    <script>
        document.getElementById('ttsForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const text = document.getElementById('textInput').value;
            const token = document.querySelector('input[name="_token"]').value;

            const response = await fetch('/text-to-speech', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ text })
            });

            const data = await response.json();

            if (data.audio_url) {
                const audio = document.getElementById('ttsAudio');
                audio.src = data.audio_url;
                audio.style.display = 'block';
                audio.play();
            } else {
                alert('TTS failed: ' + (data.error || 'Unknown error'));
            }
        });
    </script>

</body>
</html>
