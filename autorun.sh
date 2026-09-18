#!/usr/bin/env bash
set -e
cd "$(dirname "$0")"

echo "ClickFix autorun"

echo "[1/4] Checking project files..."
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
  else
    echo "ERROR: .env.example not found."
    exit 1
  fi
fi

echo "[2/4] Checking PHP runtime..."
if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: PHP is not installed or not in PATH."
  exit 1
fi

echo "[3/4] Preparing Laravel app..."
php artisan key:generate --force
php artisan migrate --force

echo "[4/4] Starting Laravel server..."
echo "Serving on http://127.0.0.1:8000"
php artisan serve --host=127.0.0.1 --port=8000