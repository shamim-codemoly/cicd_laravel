#!/bin/bash

# =============================================
# Zero-Dependency GTM Server Setup Script
# Version: 2.3
# Assumes: Docker, NGINX, Certbot are pre-installed
# =============================================

set -euo pipefail

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
    echo -e "${RED}Invalid domain format${NC}"; return 1
  }
}

validate_port() {
  [[ "$1" =~ ^[0-9]+$ ]] && [ "$1" -gt 0 -a "$1" -lt 65536 ] || {
    echo -e "${RED}Invalid port (1-65535)${NC}"; return 1
  }
}

validate_gtm_config() {
  # Accepts both GTM-XXXXXX and base64
  [[ "$1" =~ ^GTM-[A-Z0-9]{6,}$ ]] || [[ "$1" =~ ^[A-Za-z0-9+/]+={0,2}$ ]] || {
    echo -e "${RED}Invalid format (GTM-XXXXXX or base64)${NC}"; return 1
  }
}

validate_email() {
  [[ "$1" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]] || {
    echo -e "${RED}Invalid email${NC}"; return 1
  }
}

check_port_available() {
  if ss -tulnp | grep -q ":${1} "; then
    echo -e "${RED}Port $1 already in use${NC}"
    return 1
  fi
}

# =============================================
# User Input
# =============================================

echo -e "${GREEN}GTM Server Setup (No Installation)${NC}"
echo "===================================="

# Domain
while :; do
  read -p "Enter domain/subdomain (e.g., gtm.example.com): " DOMAIN
  validate_domain "$DOMAIN" && break
done

# Port
while :; do
  read -p "Enter port [default: 5201]: " PORT
  PORT=${PORT:-5201}
  validate_port "$PORT" && check_port_available "$PORT" && break
done

# GTM Config
echo -e "${BLUE}Enter either:"
echo "1. Plain GTM-ID (GTM-XXXXXX)"
echo "2. Base64 encoded config${NC}"
while :; do
  read -p "GTM configuration: " CONFIG
  validate_gtm_config "$CONFIG" && break
done

# Email
while :; do
  read -p "Email for SSL certificates: " EMAIL
  validate_email "$EMAIL" && break
done

# =============================================
# Deployment
# =============================================

echo -e "${GREEN}Starting deployment...${NC}"

# Docker Container
echo -e "${YELLOW}Launching GTM container...${NC}"
docker run -d \
  --restart unless-stopped \
  -p "${PORT}:8080" \
  -e CONTAINER_CONFIG="${CONFIG}" \
  gcr.io/cloud-tagging-10302018/gtm-cloud-image:stable

# NGINX Config
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

# SSL Certificate
echo -e "${YELLOW}Obtaining SSL certificate...${NC}"
sudo certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${EMAIL}"

# =============================================
# Completion
# =============================================

echo -e "${GREEN}"
cat << "EOF"
  ____  _   _  ____ _____   _____ ___ _   _  ____
 / ___|| | | |/ ___|_ _\ \ / /_ _|_ _| \ | |/ ___|
 \___ \| | | | |    | | \ V / | | | ||  \| | |
  ___) | |_| | |___ | |  | |  | | | || |\  | |___
 |____/ \___/ \____|___| |_| |___|___|_| \_|\____|
EOF
echo -e "${NC}"

echo -e "Successfully deployed GTM Server!"
echo -e "Access URL: ${BLUE}https://${DOMAIN}${NC}"
echo -e "Container port: ${BLUE}${PORT}${NC}"
echo -e "Config type: ${BLUE}$(
  [[ "$CONFIG" =~ ^GTM- ]] && echo "GTM-ID" || echo "Base64 encoded"
)${NC}"
