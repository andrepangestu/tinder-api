#!/bin/bash

# Script to upgrade Docker Compose to V2
# Run this on your DigitalOcean server

set -e

echo "🔄 Upgrading Docker Compose to V2..."

# Check current version
echo "Current docker-compose version:"
docker-compose version || echo "docker-compose not found"

# Install Docker Compose V2 plugin
echo "Installing Docker Compose V2 plugin..."
apt-get update
apt-get install -y docker-compose-plugin

# Verify installation
echo ""
echo "✅ Docker Compose V2 installed successfully!"
echo ""
echo "New version:"
docker compose version

echo ""
echo "Note: You can now use 'docker compose' (with space) instead of 'docker-compose'"
echo "The old 'docker-compose' command will still work for backward compatibility"
