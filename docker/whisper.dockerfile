FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    python3 \
    python3-pip \
    python3-venv \
    python3-dev \
    ffmpeg \
    libsndfile1 \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Create and activate Python virtual environment
RUN python3 -m venv /opt/venv

# Install Whisper and gTTS inside venv
RUN /opt/venv/bin/pip install --upgrade pip && \
    /opt/venv/bin/pip install \
        git+https://github.com/openai/whisper.git \
        gTTS

# Make venv available system-wide (optional, so you can just use 'python' or 'pip')
ENV PATH="/opt/venv/bin:$PATH"

# Install Composer (for Laravel)
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set the working directory inside the container
WORKDIR /var/www

# The actual app will be mounted from host via docker-compose
