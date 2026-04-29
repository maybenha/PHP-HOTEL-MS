
echo "🚀 Starting Deployment..."

# Step 1: Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Step 2: Install dependencies (if using composer)
if [ -f "composer.json" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Step 3: Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 .

# Step 4: Build Docker (optional)
if [ -f "docker-compose.yml" ]; then
    echo "🐳 Building Docker container..."
    docker-compose down
    //Building input container
    docker-compose up -d --build
fi

# Step 5: Database migration (manual or auto)
echo "🗄️ Database setup..."
echo "👉 Please import your SQL file if not already done."

# Step 6: Clear cache (optional)
echo "🧹 Clearing cache..."
rm -rf cache/* 2>/dev/null

echo "✅ Deployment Completed!"