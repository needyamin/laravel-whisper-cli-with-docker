# Offline Speech to Text API (Docker + Whisper CLI)

This project provides an offline, open-source speech-to-text (STT) API using Laravel, Python, OpenAI's Whisper, and Docker.

---

## ✅ This setup includes:

- Laravel app (PHP 8.2)
- Python + Whisper CLI installed
- FFmpeg for audio handling
- API to upload and transcribe audio using Whisper
- Docker volume mount for sharing files across app

---

## 📁 Project Structure

```
laravel-whisper/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── SpeechController.php
│   ├── routes/
│   │   └── web.php
├── docker/
│   └── whisper.dockerfile
├── docker-compose.yml
├── .env
└── README.md
```

---

## 🚀 Run it!

```bash
cd laravel-whisper
docker-compose up --build
```

Laravel will be running at: [http://localhost:8000](http://localhost:8000)

---

## 🧪 Test it with CURL

```bash
curl -X POST http://localhost:8000/speech-to-text \
  -F "audio=@/full/path/to/your/audio.mp3"
```

Or use a browser form with:

```html
<form action="/speech-to-text" method="POST" enctype="multipart/form-data">
  <input type="file" name="audio" accept="audio/*" required>
  <button type="submit">Transcribe</button>
</form>
```

---

## 📝 Notes

- ✅ Whisper runs inside Docker — no internet or API key required.
- 🔊 For best results, use audio at **16kHz or higher**.
- 🎧 Recommended formats: `.mp3`, `.wav`, `.flac`

---

## 🎛️ Convert audio with FFmpeg

```bash
ffmpeg -i input.mp3 -ar 16000 output.wav
```

---

## 🤖 Powered by

- [Laravel](https://laravel.com/)
- [OpenAI Whisper](https://github.com/openai/whisper)
- [Docker](https://www.docker.com/)
- [FFmpeg](https://ffmpeg.org/)

---

## 📄 License

MIT License

---

PRs and contributions welcome!

