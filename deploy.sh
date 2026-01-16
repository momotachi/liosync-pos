#!/bin/bash

# ============================================
# Laravel Deployment Script for Linux VPS
# Cashier POS System
# ============================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="cashier"
APP_DIR="/var/www/$PROJECT_NAME"
NGINX_CONF="/etc/nginx/sites-available/$PROJECT_NAME"
SERVICE_NAME="cashier-pos"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Cashier POS Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run this script as root or with sudo${NC}"
    exit 1
fi

# Step 1: Update System
echo -e "\n${YELLOW}[1/9] Updating system packages...${NC}"
apt update && apt upgrade -y

# Step 2: Install Required Packages
echo -e "\n${YELLOW}[2/9] Installing required packages...${NC}"
apt install -y \
    nginx \
    mariadb-server \
    php8.2 \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-xml \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath \
    composer \
    git \
    unzip \
    curl \
    certbot \
    python3-certbot-nginx

# Step 3: Configure MySQL
echo -e "\n${YELLOW}[3/9] Configuring MySQL...${NC}"
systemctl start mariadb
systemctl enable mariadb

echo -e "${GREEN}Please enter MySQL root password (or press Enter for no password):${NC}"
read -s MYSQL_ROOT_PASSWORD

if [ -n "$MYSQL_ROOT_PASSWORD" ]; then
    mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$MYSQL_ROOT_PASSWORD';"
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"
fi

echo -e "${GREEN}Creating database...${NC}"
echo -e "Enter database name [cashier_db]:"
read DB_NAME
DB_NAME=${DB_NAME:-cashier_db}

echo -e "Enter database user [cashier_user]:"
read DB_USER
DB_USER=${DB_USER:-cashier_user}

echo -e "Enter database password:"
read -s DB_PASSWORD

mysql -u root ${MYSQL_ROOT_PASSWORD:+-p"$MYSQL_ROOT_PASSWORD"} <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# Step 4: Setup Application Directory
echo -e "\n${YELLOW}[4/9] Setting up application directory...${NC}"
mkdir -p $APP_DIR
chown -R $SUDO_USER:$SUDO_USER $APP_DIR

# Step 5: Clone or Copy Application
echo -e "\n${YELLOW}[5/9] Deploying application files...${NC}"
echo -e "Choose deployment method:"
echo -e "  1) Clone from Git repository"
echo -e "  2) Copy from local directory"
read -p "Enter choice [1-2]: " DEPLOY_METHOD

if [ "$DEPLOY_METHOD" = "1" ]; then
    echo -e "Enter Git repository URL:"
    read GIT_URL
    git clone $GIT_URL $APP_DIR
else
    echo -e "Enter local directory path:"
    read LOCAL_DIR
    cp -r $LOCAL_DIR/* $APP_DIR/
fi

cd $APP_DIR

# Step 6: Install Dependencies
echo -e "\n${YELLOW}[6/9] Installing dependencies...${NC}"
sudo -u $SUDO_USER composer install --no-dev --optimize-autoloader --no-interaction

# Step 7: Setup Environment
echo -e "\n${YELLOW}[7/9] Setting up environment...${NC}"
if [ ! -f .env ]; then
    sudo -u $SUDO_USER cp .env.example .env
fi

# Generate APP_KEY
sudo -u $SUDO_USER php artisan key:generate

# Update .env with production settings
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

echo -e "${GREEN}Enter your application URL (e.g., https://pos.example.com):${NC}"
read APP_URL
sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env

# Step 8: Run Migrations and Setup
echo -e "\n${YELLOW}[8/9] Running migrations and optimizations...${NC}"
sudo -u $SUDO_USER php artisan migrate --force
sudo -u $SUDO_USER php artisan storage:link
sudo -u $SUDO_USER php artisan config:cache
sudo -u $SUDO_USER php artisan route:cache
sudo -u $SUDO_USER php artisan view:cache

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Step 9: Configure Nginx
echo -e "\n${YELLOW}[9/9] Configuring Nginx...${NC}"
cat > $NGINX_CONF <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $APP_URL;

    root $APP_DIR/public;
    index index.php index.html;

    # Add security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Logging
    access_log /var/log/nginx/${PROJECT_NAME}_access.log;
    error_log /var/log/nginx/${PROJECT_NAME}_error.log;

    # Redirect server error pages to static page
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /usr/share/nginx/html;
    }

    # Pass PHP scripts to FastCGI server
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;

        # Add PHP security headers
        fastcgi_param PHP_VALUE "open_basedir=\$document_root/:/tmp/";
        fastcgi_param PHP_VALUE "max_execution_time=300";
        fastcgi_param PHP_VALUE "memory_limit=256M";
        fastcgi_param PHP_VALUE "post_max_size=20M";
        fastcgi_param PHP_VALUE "upload_max_filesize=20M";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Enable gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Browser caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}
EOF

ln -sf $NGINX_CONF /etc/nginx/sites-enabled/$PROJECT_NAME
nginx -t
systemctl restart nginx

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "  1. Configure SSL with: ${GREEN}sudo certbot --nginx -d yourdomain.com${NC}"
echo -e "  2. Setup firewall: ${GREEN}sudo ufw allow 'Nginx Full'${NC}"
echo -e "  3. Create admin user: ${GREEN}cd $APP_DIR && php artisan db:seed --class=AdminUserSeeder${NC}"
echo -e "  4. Monitor logs: ${GREEN}tail -f $APP_DIR/storage/logs/laravel.log${NC}"
echo -e "\n${YELLOW}Application Details:${NC}"
echo -e "  Directory: $APP_DIR"
echo -e "  URL: $APP_URL"
echo -e "  Database: $DB_NAME"
echo -e "\n"
