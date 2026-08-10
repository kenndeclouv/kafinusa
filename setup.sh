#!/bin/bash

echo "=========================================="
echo "Auto Setup Script (Unix/macOS)"
echo "=========================================="

echo -e "\n[1/6] Setting up Backend Environment..."
cp .env.example .env
touch database/database.sqlite

echo -e "\n[2/6] Installing Backend Dependencies (Composer)..."
composer install

echo -e "\n[3/6] Generating App Key..."
php artisan key:generate

echo -e "\n[4/6] Running Migrations and Seeders..."
php artisan migrate:fresh --seed

echo -e "\n[5/6] Creating Storage Symlink..."
php artisan storage:link

echo -e "\n[6/6] Setting up Frontend Environment..."
npm install
npm run build

echo -e "\n=========================================="
echo "Setup Completed Successfully!"
echo "=========================================="