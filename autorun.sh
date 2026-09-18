#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"

echo "Preparing ClickFix environment..."

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if command -v docker >/dev/null 2>&1; then
  echo "Docker detected. Starting application with Docker Compose..."
  docker compose up --build
  exit 0
fi

echo "Docker not found. Falling back to PHP built-in server."
if command -v php >/dev/null 2>&1; then
  echo "Generating APP_KEY and running migrations..."
  php artisan key:generate --force
  php artisan migrate --force
  echo "Serving application on http://localhost:8080"
  php artisan serve --host=0.0.0.0 --port=8080
  exit 0
fi

echo "ERROR: Neither Docker nor PHP CLI was found on this machine."
echo "Install Docker or PHP and try again."
exit 1