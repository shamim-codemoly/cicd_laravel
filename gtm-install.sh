#!/bin/bash

# 1. Update package list
sudo apt update

# 2. Install necessary packages for Docker
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# 3. Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# 4. Add the Docker repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# 5. Install Docker Engine
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io

# 6. Add your user to the 'docker' group (so you don't need sudo for docker commands)
sudo usermod -aG docker ${USER}

# 7. Log out and log back in (or run 'newgrp docker') for group changes to take effect
echo "Docker installed. Please log out and log back into your SSH session (or run 'newgrp docker') for the changes to take effect."

# 1. Install Nginx ===============================================
sudo apt install -y nginx

# 2. Allow Nginx Full in UFW firewall (if enabled)
sudo ufw allow 'Nginx Full'
# If you don't use UFW, ensure your VPS provider's firewall allows ports 80 and 443

# 3. Install Certbot (for Let's Encrypt SSL certificates)
sudo snap install core
sudo snap refresh core
sudo snap install --classic certbot
sudo ln -s /snap/bin/certbot /usr/bin/certbot

