#!/bin/bash

# ============================================================

# VSMS Setup Script for Ubuntu

# Apache2 + MySQL + PHP

# ============================================================

set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
DEPLOY_DIR="/var/www/html/vsms"
DB_NAME="vsms"
DB_USER="vsms_user"
DB_PASS="vsms_pass_2026"

echo "===== VSMS Ubuntu Setup ====="

# ── 1. Install dependencies ───────────────────────────────

echo "[1/6] Installing dependencies..."

sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql libapache2-mod-php

# ── 2. Start services ─────────────────────────────────────

echo "[2/6] Starting services..."

sudo systemctl start apache2
sudo systemctl enable apache2

sudo systemctl start mysql
sudo systemctl enable mysql

# ── 3. Setup database ─────────────────────────────────────

echo "[3/6] Setting up database..."

sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS ${DB_NAME};

CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';

FLUSH PRIVILEGES;
EOF

# ── 4. Import SQL ─────────────────────────────────────────

echo "[4/6] Importing database..."

mysql -u ${DB_USER} -p${DB_PASS} ${DB_NAME} < "${PROJECT_DIR}/vsms_setup.sql" || true

# ── 5. Deploy project ─────────────────────────────────────

echo "[5/6] Deploying project..."

sudo rm -rf ${DEPLOY_DIR}
sudo cp -r "${PROJECT_DIR}" ${DEPLOY_DIR}

sudo chown -R www-data:www-data ${DEPLOY_DIR}
sudo chmod -R 755 ${DEPLOY_DIR}

# ── 6. Configure DB connection ────────────────────────────

echo "[6/6] Configuring DB connection..."

sudo tee ${DEPLOY_DIR}/includes/db.php > /dev/null <<DBEOF

<?php
define('DB_HOST', 'localhost');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('DB_NAME', '${DB_NAME}');

\$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (\$conn->connect_error) {
    die("Database Connection Failed: " . \$conn->connect_error);
}
?>

DBEOF

echo "✅ Setup complete!"
echo "👉 Open: http://localhost/vsms/"
echo "Admin: admin / admin123"
echo "Staff: staff1 / staff123"
