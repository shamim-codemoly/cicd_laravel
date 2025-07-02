#!/bin/bash

# =============================================
# Zero-Dependency GTM Server Setup Script
# Version: 2.3 (Modified for Laravel)
# =============================================
# Usage: ./deploy.sh domain port email config
# =============================================

set -euo pipefail

# Check number of arguments
if [ "$#" -ne 4 ]; then
  echo "Usage: $0 domain port email config"
  exit 1
fi

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# =============================================
# Validation Functions
# =============================================

validate_domain() {
  [[ "$1" =~ ^[a-zA-Z0-9][a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]] || {
    echo -e "${RED}Invalid domain format${NC}"; exit 1
  }
}

validate_port() {
  [[ "$1" =~ ^[0-9]+$ ]] && [ "$1" -gt 0 -a "$1" -lt 65536 ] || {
    echo -e "${RED}Invalid port (1-65535)${NC}"; exit 1
  }
}

validate_gtm_config() {
  [[ "$1" =~ ^GTM-[A-Z0-9]{6,}$ ]] || [[ "$1" =~ ^[A-Za-z0-9+/]+={0,2}$ ]] || {
    echo -e "${RED}Invalid format (GTM-XXXXXX or base64)${NC}"; exit 1
  }
}

validate_email() {
  [[ "$1" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]] || {
    echo -e "${RED}Invalid email${NC}"; exit 1
  }
}

check_port_available() {
  if ss -tulnp | grep -q ":${1} "; then
    echo -e "${RED}Port $1 already in use${NC}"
    exit 1
  fi
}

# =============================================
# Read Inputs from Laravel
# =============================================

DOMAIN=$1
PORT=$2
EMAIL=$3
CONFIG=$4

# =============================================
# Validate Inputs
# =============================================

validate_domain "$DOMAIN"
validate_port "$PORT"
check_port_available "$PORT"
validate_gtm_config "$CONFIG"
validate_email "$EMAIL"

# =============================================
# Begin Deployment
# =============================================

echo -e "${GREEN}Starting GTM Server Deployment...${NC}"

# Launch Docker GTM Container
echo -e "${YELLOW}Launching Docker container...${NC}"
docker run -d \
  --restart unless-stopped \
  -p "${PORT}:8080" \
  -e CONTAINER_CONFIG="${CONFIG}" \
  gcr.io/cloud-tagging-10302018/gtm-cloud-image:stable

# Setup NGINX Configuration
echo -e "${YELLOW}Creating NGINX config...${NC}"
cat > "/tmp/${DOMAIN}.conf" <<EOF
server {
    listen 80;
    server_name ${DOMAIN};

    location / {
        proxy_pass http://localhost:${PORT};
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    }
}
EOF

sudo mv "/tmp/${DOMAIN}.conf" "/etc/nginx/sites-available/"
sudo ln -sf "/etc/nginx/sites-available/${DOMAIN}.conf" "/etc/nginx/sites-enabled/"

# Reload NGINX to apply new config
echo -e "${YELLOW}Reloading NGINX...${NC}"
sudo systemctl reload nginx

# Get SSL Certificate
echo -e "${YELLOW}Issuing SSL Certificate with Certbot...${NC}"
sudo certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${EMAIL}"

# =============================================
# Completion Banner
# =============================================

echo -e "✅ Successfully deployed GTM Server!"
echo -e "🌐 Access URL: ${BLUE}https://${DOMAIN}${NC}"
echo -e "📦 Container port: ${BLUE}${PORT}${NC}"
echo -e "⚙️  Config type: ${BLUE}$([[ "$CONFIG" =~ ^GTM- ]] && echo "GTM-ID" || echo "Base64")${NC}"
