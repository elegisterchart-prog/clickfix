@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0"

echo ClickFix autorun

echo [1/4] Checking project files...
if not exist ".env" (
    if exist ".env.example" (
        copy /Y .env.example .env >nul
        echo Created .env from .env.example
    ) else (
        echo ERROR: .env.example not found.
        pause
        exit /b 1
    )
)

echo [2/4] Checking PHP runtime...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH.
    pause
    exit /b 1
)

echo [3/4] Preparing Laravel app...
php artisan key:generate --force >nul 2>&1
php artisan migrate --force

echo [4/4] Starting Laravel server...
echo Serving on http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
exit /b