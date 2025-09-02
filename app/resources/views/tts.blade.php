<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Manual TTS Test</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; padding: 24px; max-width: 600px; margin: auto; }
input, button { padding: 8px 12px; font-size: 14px; margin-right: 8px; width: calc(100% - 24px); margin-bottom: 12px; }
audio { width: 100%; margin-top: 12px; }
#response { white-space: pre-wrap; border: 1px solid #ddd; padding: 12px; border-radius: 6px; background:#fafafa; min-height:50px; }
</style>
</head>
<body>
<h1>Manual Text-to-Speech Test</h1>

<input type="text" id="textInput" placeholder="Enter text to speak..." />
<button id="ttsBtn">Generate & Play</button>

<audio id="player" controls></audio>

<div id="response">Status...</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const textInput = document.getElementById('textInput');
const ttsBtn = document.getElementById('ttsBtn');
const player = document.getElementById('player');
const responseDiv = document.getElementById('response');

ttsBtn.addEventListener('click', async () => {
    const text = textInput.value.trim();
    if (!text) {
        responseDiv.textContent = "Please enter some text.";
        return;
    }

    responseDiv.textContent = "Generating TTS...";

    try {
        const resp = await fetch("/text-to-speech", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ text })
        });

        if (!resp.ok) {
            const txt = await resp.text().catch(() => resp.statusText || resp.status);
            responseDiv.textContent = `TTS error ${resp.status}: ${txt}`;
            return;
        }

        const data = await resp.json();
        if (data.audio_url) {
            // Stop and reset previous audio
            player.pause();
            player.currentTime = 0;
            player.src = data.audio_url;

            // Attempt to play
            const playPromise = player.play();
            if (playPromise !== undefined) {
                playPromise
                    .then(() => {
                        responseDiv.textContent = "TTS audio generated and playing.";
                    })
                    .catch(err => {
                        console.warn("Audio play blocked:", err);
                        responseDiv.textContent = "TTS audio generated. Click play button to listen.";
                    });
            } else {
                responseDiv.textContent = "TTS audio generated and ready to play.";
            }
        } else {
            responseDiv.textContent = "TTS returned no audio URL.";
        }

    } catch (err) {
        responseDiv.textContent = "Error: " + err.message;
        console.error(err);
    }
});
</script>
</body>
</html>
