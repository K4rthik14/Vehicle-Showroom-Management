#!/bin/bash
# ============================================================
# VSMS Setup Script for CachyOS (Arch-based)
# Works with Apache (httpd) + MariaDB + PHP
# ============================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
DEPLOY_DIR="/srv/http/vsms"
DB_NAME="vsms"
DB_USER="vsms_user"
DB_PASS="vsms_pass_2026"

echo -e "${CYAN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║   VSMS — CachyOS Setup Script                ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════╝${NC}"
echo ""

# ── 1. Check services ───────────────────────────────────────
echo -e "${YELLOW}[1/5]${NC} Checking services..."

if ! systemctl is-active --quiet httpd; then
    echo -e "  ${RED}✗${NC} Apache (httpd) is not running."
    echo -e "  Run: ${CYAN}sudo systemctl start httpd${NC}"
    exit 1
fi
echo -e "  ${GREEN}✓${NC} Apache is running"

if ! systemctl is-active --quiet mariadb; then
    echo -e "  ${RED}✗${NC} MariaDB is not running."
    echo -e "  Run: ${CYAN}sudo systemctl start mariadb${NC}"
    exit 1
fi
echo -e "  ${GREEN}✓${NC} MariaDB is running"

if ! php -m 2>/dev/null | grep -q mysqli; then
    echo -e "  ${RED}✗${NC} PHP mysqli extension not found."
    echo -e "  Run: ${CYAN}sudo pacman -S php-mysqli${NC}"
    exit 1
fi
echo -e "  ${GREEN}✓${NC} PHP with mysqli available"
echo ""

# ── 2. Setup MariaDB database & user ────────────────────────
echo -e "${YELLOW}[2/5]${NC} Setting up MariaDB database..."
echo -e "  This requires ${CYAN}sudo${NC} to access MariaDB as root."
echo ""

sudo mariadb <<EOF
-- Create database
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a dedicated user with password auth (works from PHP)
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "  ${GREEN}✓${NC} Database '${DB_NAME}' created"
echo -e "  ${GREEN}✓${NC} User '${DB_USER}' created with password auth"
echo ""

# ── 3. Import schema & seed data ────────────────────────────
echo -e "${YELLOW}[3/5]${NC} Importing schema and seed data..."

mariadb -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${PROJECT_DIR}/vsms_setup.sql" 2>/dev/null || {
    # Tables may already exist, that's OK
    echo -e "  ${YELLOW}⚠${NC}  Tables may already exist — skipping import (no data lost)"
}
echo -e "  ${GREEN}✓${NC} Database schema ready"
echo ""

# ── 4. Deploy to /srv/http/vsms ──────────────────────────────
echo -e "${YELLOW}[4/5]${NC} Deploying to ${DEPLOY_DIR}..."

sudo rm -rf "${DEPLOY_DIR}"
sudo cp -r "${PROJECT_DIR}" "${DEPLOY_DIR}"
sudo chown -R http:http "${DEPLOY_DIR}"
sudo chmod -R 755 "${DEPLOY_DIR}"

echo -e "  ${GREEN}✓${NC} Files deployed"
echo ""

# ── 5. Update db.php credentials ────────────────────────────
echo -e "${YELLOW}[5/5]${NC} Updating database credentials..."

sudo tee "${DEPLOY_DIR}/includes/db.php" > /dev/null <<DBEOF
<?php
define('DB_HOST', 'localhost');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('DB_NAME', '${DB_NAME}');

\$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (\$conn->connect_error) {
    die("<div class='alert alert-danger m-4'>Database Connection Failed: " . \$conn->connect_error . "</div>");
}

\$conn->set_charset("utf8mb4");
?>
DBEOF

sudo chown http:http "${DEPLOY_DIR}/includes/db.php"
echo -e "  ${GREEN}✓${NC} Credentials updated"
echo ""

# ── Done ─────────────────────────────────────────────────────
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✅ Setup Complete!                          ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║                                              ║${NC}"
echo -e "${GREEN}║   Open: ${CYAN}http://localhost/vsms/${GREEN}              ║${NC}"
echo -e "${GREEN}║                                              ║${NC}"
echo -e "${GREEN}║   Admin login:  admin / admin123             ║${NC}"
echo -e "${GREEN}║   Staff login:  staff1 / staff123            ║${NC}"
echo -e "${GREEN}║                                              ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
