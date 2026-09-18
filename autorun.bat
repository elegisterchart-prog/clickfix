@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0"

echo Preparing ClickFix environment...

if not exist ".env" (
    copy /Y .env.example .env >nul
    echo Created .env from .env.example
)

docker compose version >nul 2>&1
if %errorlevel%==0 (
    echo Docker Compose detected.
    echo Starting application with Docker...
    docker compose up --build
    exit /b
)

echo Docker not found. Falling back to PHP built-in server.
php --version >nul 2>&1
if %errorlevel%==0 (
    echo Generating APP_KEY and running migrations...
    php artisan key:generate --force >nul 2>&1
    php artisan migrate --force
    echo Serving application on http://localhost:8080
    php artisan serve --host=0.0.0.0 --port=8080
    exit /b
)

echo ERROR: Neither Docker nor PHP CLI was found on this machine.
echo Install Docker Desktop or PHP and try again.
pause