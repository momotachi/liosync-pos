#!/bin/bash

# ==============================================================================
# AUTOMATED SERVER SETUP SCRIPT FOR LARAVEL (LEMP STACK)
# ==============================================================================
# This script will install:
# 1. Nginx (Web Server)
# 2. PHP 8.2 + Required Extensions (for Laravel 12)
# 3. Composer (PHP Dependency Manager)
# 4. MariaDB (Database)
# 5. Node.js & NPM (Frontend Assets)
# ==============================================================================

# Exit immediately if a command exits with a non-zero status
set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Server Setup...${NC}"

# 1. Update System
echo -e "${YELLOW}[1/6] Updating System Repositories...${NC}"
sudo apt-get update && sudo apt-get upgrade -y
sudo apt-get install -y software-properties-common curl git unzip zip

# 2. Install PHP 8.2
echo -e "${YELLOW}[2/6] Installing PHP 8.2 and Extensions...${NC}"
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip php8.2-intl php8.2-gd php8.2-cli

# 3. Install Composer
echo -e "${YELLOW}[3/6] Installing Composer...${NC}"
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 4. Install Node.js (LTS Version)
echo -e "${YELLOW}[4/6] Installing Node.js & NPM...${NC}"
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# 5. Install MariaDB
echo -e "${YELLOW}[5/6] Installing MariaDB Database...${NC}"
sudo apt-get install -y mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb

# 6. Install Nginx
echo -e "${YELLOW}[6/6] Installing Nginx...${NC}"
sudo apt-get install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# ==============================================================================
# VERIFICATION
# ==============================================================================
echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}SETUP COMPLETED SUCCESSFULLY!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo -e "PHP Version: $(php -v | head -n 1)"
echo -e "Node Version: $(node -v)"
echo -e "NPM Version: $(npm -v)"
echo -e "Composer Version: $(composer --version | head -n 1)"
echo -e "Nginx Status: $(systemctl is-active nginx)"
echo -e "MariaDB Status: $(systemctl is-active mariadb)"
echo -e "${GREEN}=========================================${NC}"

# ==============================================================================
# OPTIONAL: PROJECT DIRECTORY SETUP
# ==============================================================================
# Uncomment the lines below if you want to create the directory structure now
# PROJECT_DIR="/var/www/juicepos"
# echo -e "${YELLOW}Creating project directory at ${PROJECT_DIR}...${NC}"
# sudo mkdir -p ${PROJECT_DIR}
# sudo chown -R $USER:www-data ${PROJECT_DIR}
# sudo chmod -R 775 ${PROJECT_DIR}
# echo -e "Directory created. You can now upload your files to ${PROJECT_DIR}"
