<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Speak ? Worker (POST) — Improved</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <!-- CSRF token for Laravel -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; padding: 24px; max-width: 820px; margin: auto; }
    h1 { font-size: 20px; margin-bottom: 8px; }
    button { padding: 8px 12px; font-size: 14px; margin-right: 8px; }
    select { padding: 6px; font-size: 14px; }
    #response { white-space: pre-wrap; margin-top: 18px; padding: 12px; border-radius: 6px; border: 1px solid #ddd; background:#fafafa; min-height: 80px; }
    #player { display:block; margin-top:10px; width:100%; }
    .muted { color:#666; font-size:13px; margin-top:6px; }
    .row { margin-top: 12px; display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
  </style>
</head>
<body>
<h1>?? Speak ? Worker (POST) — Improved mic handling</h1>

<div class="row">
  <label for="micSelect">Microphone:</label>
  <select id="micSelect"><option>Loading…</option></select>

  <button id="requestPermBtn">Request Permission</button>
  <button id="releaseBtn" disabled>Release Mic</button>
</div>

<div class="row">
  <button id="startBtn">Start Recording</button>
  <button id="stopBtn" disabled>Stop & Send</button>
  <button id="playLastBtn" disabled>Play Last</button>
</div>

<audio id="player" controls></audio>

<div id="response"><strong>Transcription / Server response:</strong>
  <div id="resText">—</div>
</div>

<div class="muted" id="permStatus">Permission: unknown</div>

<script>
  const workerUrl = "/speech-to-texts"; // Laravel route
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  const micSelect = document.getElementById('micSelect');
  const requestPermBtn = document.getElementById('requestPermBtn');
  const releaseBtn = document.getElementById('releaseBtn');
  const startBtn = document.getElementById('startBtn');
  const stopBtn = document.getElementById('stopBtn');
  const playLastBtn = document.getElementById('playLastBtn');
  const player = document.getElementById('player');
  const resText = document.getElementById('resText');
  const permStatus = document.getElementById('permStatus');

  let currentStream = null;
  let mediaRecorder = null;
  let audioChunks = [];
  let lastBlobUrl = null;

  const LS_KEY = "preferredMicDeviceId";

  async function updateDeviceList() {
    try {
      const devices = await navigator.mediaDevices.enumerateDevices();
      const inputs = devices.filter(d => d.kind === 'audioinput');
      micSelect.innerHTML = "";
      if (inputs.length === 0) {
        micSelect.innerHTML = "<option value=''>No microphones found</option>";
        return;
      }
      const saved = localStorage.getItem(LS_KEY) || "";
      inputs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.deviceId;
        opt.textContent = d.label || `Microphone (${d.deviceId.slice(0,6)})`;
        micSelect.appendChild(opt);
      });
      if (saved) {
        const found = Array.from(micSelect.options).find(o => o.value === saved);
        if (found) micSelect.value = saved;
      }
    } catch (err) {
      console.error('enumerateDevices failed', err);
      micSelect.innerHTML = "<option value=''>Error enumerating devices</option>";
    }
  }

  async function refreshPermissionStatus() {
    if (!navigator.permissions || !navigator.permissions.query) return;
    try {
      const p = await navigator.permissions.query({ name: 'microphone' });
      permStatus.textContent = 'Permission: ' + p.state;
      p.onchange = () => permStatus.textContent = 'Permission: ' + p.state;
    } catch { permStatus.textContent = 'Permission: unknown'; }
  }

  async function ensureStream() {
    const selectedDeviceId = micSelect.value || null;
    if (currentStream) {
      const track = currentStream.getAudioTracks()[0];
      if (track && (!selectedDeviceId || track.deviceId === selectedDeviceId)) return currentStream;
      currentStream.getTracks().forEach(t => t.stop());
      currentStream = null;
    }
    const constraints = selectedDeviceId ? { audio: { deviceId: { exact: selectedDeviceId } } } : { audio: true };
    const stream = await navigator.mediaDevices.getUserMedia(constraints);
    currentStream = stream;
    releaseBtn.disabled = false;
    return stream;
  }

  requestPermBtn.addEventListener('click', async () => {
    try {
      resText.textContent = "Requesting mic permission...";
      await ensureStream();
      await updateDeviceList();
      await refreshPermissionStatus();
      resText.textContent = "Permission granted. Choose a mic and Start Recording.";
    } catch (err) {
      console.error(err);
      resText.textContent = 'Permission error: ' + err.message;
    }
  });

  releaseBtn.addEventListener('click', () => {
    if (currentStream) currentStream.getTracks().forEach(t => t.stop());
    currentStream = null;
    releaseBtn.disabled = true;
    resText.textContent = 'Mic released.';
  });

  micSelect.addEventListener('change', () => {
    localStorage.setItem(LS_KEY, micSelect.value || "");
  });

  startBtn.addEventListener('click', async () => {
    audioChunks = [];
    try {
      const stream = await ensureStream();
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.ondataavailable = e => { if (e.data && e.data.size) audioChunks.push(e.data); };
      mediaRecorder.onstart = () => {
        startBtn.disabled = true;
        stopBtn.disabled = false;
        resText.textContent = "Recording…";
      };
      mediaRecorder.start();
    } catch (err) {
      console.error(err);
      resText.textContent = "Could not start recording: " + err.message;
    }
  });

  stopBtn.addEventListener('click', async () => {
    if (!mediaRecorder) return;
    stopBtn.disabled = true;
    startBtn.disabled = false;

    mediaRecorder.onstop = async () => {
      const blob = new Blob(audioChunks, { type: 'audio/webm' });
      if (lastBlobUrl) URL.revokeObjectURL(lastBlobUrl);
      lastBlobUrl = URL.createObjectURL(blob);
      player.src = lastBlobUrl;
      playLastBtn.disabled = false;

      resText.textContent = "Uploading audio...";

      try {
        const form = new FormData();
        form.append('file', blob, 'voice.webm');

        const resp = await fetch(workerUrl, {
          method: 'POST',
          body: form,
          headers: {
            "X-CSRF-TOKEN": csrfToken
          }
        });

        if (!resp.ok) {
          const txt = await resp.text().catch(() => resp.statusText || resp.status);
          resText.textContent = `Server returned HTTP ${resp.status}: ${txt}`;
          return;
        }

      const data = await resp.json();
resText.textContent = data.text || "(empty response)";
      } catch (err) {
        resText.textContent = "Upload error: " + err.message;
      } finally {
        mediaRecorder = null;
      }
    };

    mediaRecorder.stop();
  });

  playLastBtn.addEventListener('click', () => { if (player.src) player.play(); });

  (async function init() {
    if (location.protocol === 'file:') resText.textContent = "Serve from http://127.0.0.1 or https://";
    await updateDeviceList();
    await refreshPermissionStatus();
  })();

  window.addEventListener('beforeunload', () => {
    if (lastBlobUrl) URL.revokeObjectURL(lastBlobUrl);
    if (currentStream) currentStream.getTracks().forEach(t => t.stop());
  });
</script>
</body>
</html>















