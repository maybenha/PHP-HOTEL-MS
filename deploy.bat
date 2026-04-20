@echo off
echo 🚀 Starting Deployment...

echo 📥 Pulling latest code...
git pull origin main

IF EXIST composer.json (
    echo 📦 Installing dependencies...
    composer install --no-dev --optimize-autoloader
)

echo 🔐 Setting permissions...
echo Skipped (Windows)

IF EXIST docker-compose.yml (
    echo 🐳 Running Docker...
    docker-compose down
    docker-compose up -d --build
)

echo 🧹 Clearing cache...
del /Q cache\* 2>nul

echo ✅ Deployment Completed!
pause