# Deployment Summary - Liosync POS

## Server Information

| Detail | Value |
|--------|-------|
| **VPS IP** | 5.189.182.49 |
| **SSH Host** | liosync-pos |
| **Liosync POS URL** | http://5.189.182.49:8080 |
| **Frappe URL** | http://5.189.182.49 |
| **OS** | Ubuntu 24.04 LTS |
| **PHP Version** | 8.4.16 |
| **Web Server** | Nginx 1.24.0 |
| **Database** | MariaDB 10.11.13 |

---

## SSH Connection

### Via VS Code
1. Press `F1` → `Remote-SSH: Connect to Host`
2. Select `liosync-pos`

### Via Terminal
```bash
ssh liosync-pos
# or
ssh root@5.189.182.49
```

### SSH Config Location
`C:\Users\HUTOMO TRI H\.ssh\config`

---

## Database Credentials

| Detail | Value |
|--------|-------|
| **Database Name** | liosync_cashier |
| **Username** | liosync_user |
| **Password** | Liosync@2025!Secure |
| **Host** | 127.0.0.1 |
| **Port** | 3306 |

### Connect via MySQL CLI
```bash
mysql -u liosync_user -p'Liosync@2025!Secure' liosync_cashier
```

---

## Application Login

| Role | Email | Password |
|------|-------|----------|
| **Superadmin** | admin@liosync.com | Liosync@2025! |

---

## Project Directory

```bash
/var/www/liosync-pos
```

---

## Important Commands

### Application Commands
```bash
# Navigate to project
cd /var/www/liosync-pos

# Run migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

# Generate APP_KEY
php artisan key:generate

# Tinker (PHP REPL)
php artisan tinker
```

### Database Backup
```bash
# Backup database
mysqldump -u liosync_user -p'Liosync@2025!Secure' liosync_cashier > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
mysql -u liosync_user -p'Liosync@2025!Secure' liosync_cashier < backup_file.sql
```

### Composer Commands
```bash
cd /var/www/liosync-pos

# Install dependencies
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Update dependencies
COMPOSER_ALLOW_SUPERUSER=1 composer update
```

### Service Management
```bash
# Restart Nginx
systemctl restart nginx

# Restart PHP-FPM
systemctl restart php8.4-fpm

# Check Nginx status
systemctl status nginx

# Check PHP-FPM status
systemctl status php8.4-fpm

# View Nginx logs
tail -f /var/log/nginx/liosync-pos_error.log

# View Laravel logs
tail -f /var/www/liosync-pos/storage/logs/laravel.log
```

---

## Deployment Update Process

### Method 1: Manual Update
```bash
# 1. SSH to server
ssh liosync-pos

# 2. Navigate to project
cd /var/www/liosync-pos

# 3. Backup database
mysqldump -u liosync_user -p'Liosync@2025!Secure' liosync_cashier > backup_$(date +%Y%m%d_%H%M%S).sql

# 4. Pull latest code
git pull origin main

# 5. Install/update dependencies
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

# 6. Run migrations
php artisan migrate --force

# 7. Clear and cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 9. Restart services
systemctl restart php8.4-fpm
systemctl restart nginx
```

### Method 2: Using deploy-ssh.bat (from Windows)
```cmd
cd d:\Cashier
deploy-ssh.bat
# Select option 5: Update Production
```

---

## Nginx Configuration

### Config File Location
```bash
/etc/nginx/sites-available/liosync-pos
```

### Update Nginx Config
```bash
# Edit config
nano /etc/nginx/sites-available/liosync-pos

# Test configuration
nginx -t

# Restart Nginx
systemctl restart nginx
```

### Current Nginx Config
```nginx
server {
    listen 80;
    server_name 5.189.182.49;
    root /var/www/liosync-pos/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

---

## Environment Configuration

### .env File Location
```bash
/var/www/liosync-pos/.env
```

### Current Production Settings
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://5.189.182.49
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=liosync_cashier
DB_USERNAME=liosync_user
DB_PASSWORD=Liosync@2025!Secure
```

---

## SSL Setup (Let's Encrypt)

### Install SSL Certificate
```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Setup SSL
certbot --nginx -d 5.189.182.49

# Or with domain
certbot --nginx -d yourdomain.com

# Test renewal
certbot renew --dry-run
```

### Auto-renewal is configured automatically via cron.

---

## Troubleshooting

### Common Issues

#### 1. 502 Bad Gateway
```bash
# Restart PHP-FPM
systemctl restart php8.4-fpm

# Check PHP-FPM status
systemctl status php8.4-fpm
```

#### 2. Permission Denied
```bash
cd /var/www/liosync-pos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### 3. Database Connection Failed
```bash
# Check MariaDB status
systemctl status mariadb

# Restart MariaDB
systemctl restart mariadb

# Test connection
mysql -u liosync_user -p'Liosync@2025!Secure' liosync_cashier
```

#### 4. Storage Link Issue
```bash
cd /var/www/liosync-pos
php artisan storage:link
```

#### 5. Cache Issues
```bash
cd /var/www/liosync-pos
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## File Permissions

### Storage and Cache Directories
```bash
cd /var/www/liosync-pos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### All Files
```bash
chown -R www-data:www-data /var/www/liosync-pos
```

---

## Monitoring

### Check Application Status
```bash
# Laravel info
php artisan about

# Check logs
tail -f storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/liosync-pos_error.log
```

### System Resources
```bash
# Disk usage
df -h

# Memory usage
free -h

# CPU usage
top -bn1 | head -20

# Check PHP processes
ps aux | grep php
```

---

## Security Checklist

- [x] APP_DEBUG=false in production
- [x] Secure database password
- [ ] SSL/HTTPS enabled (pending domain)
- [ ] Firewall configured (UFW)
- [ ] Regular backups automated
- [ ] Fail2ban installed (recommended)
- [ ] Only necessary ports open
- [ ] Regular system updates

### Enable Firewall (Recommended)
```bash
# Install UFW
apt install -y ufw

# Configure
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable

# Check status
ufw status
```

---

## Backup Strategy

### Manual Backup
```bash
# Database backup
mysqldump -u liosync_user -p'Liosync@2025!Secure' liosync_cashier > backup_$(date +%Y%m%d_%H%M%S).sql

# Application backup
tar -czf liosync-pos-backup-$(date +%Y%m%d).tar.gz /var/www/liosync-pos
```

### Automated Backup (Cron Job)
```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * mysqldump -u liosync_user -p'Liosync@2025!Secure' liosync_cashier | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz

# Keep only last 7 days
0 3 * * * find /backups -name "db_*.sql.gz" -mtime +7 -delete
```

---

## Domain Setup

### 1. Update DNS
Point your domain A record to: `5.189.182.49`

### 2. Update Nginx Config
```bash
nano /etc/nginx/sites-available/liosync-pos
# Change server_name to your domain
server_name yourdomain.com www.yourdomain.com;

# Test and restart
nginx -t
systemctl restart nginx
```

### 3. Update .env
```bash
nano /var/www/liosync-pos/.env
# Update APP_URL
APP_URL=https://yourdomain.com

# Clear cache
php artisan config:cache
```

### 4. Setup SSL for Domain
```bash
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## Useful Links

### Liosync POS (Port 8080)
- **Application URL:** http://5.189.182.49:8080
- **Admin Login:** http://5.189.182.49:8080/admin
- **Filament Admin:** http://5.189.182.49:8080/admin
- **POS:** http://5.189.182.49:8080/pos

### Frappe (Port 80)
- **Frappe URL:** http://5.189.182.49

---

## Quick Reference

### SSH Quick Connect
```bash
ssh liosync-pos
```

### Edit .env
```bash
ssh liosync-pos "cd /var/www/liosync-pos && nano .env"
```

### View Logs
```bash
ssh liosync-pos "tail -f /var/www/liosync-pos/storage/logs/laravel.log"
```

### Restart All Services
```bash
ssh liosync-pos "systemctl restart nginx php8.4-fpm"
```

### Update Application
```bash
ssh liosync-pos "cd /var/www/liosync-pos && git pull && composer install --no-dev && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache"
```

---

## Support & Documentation

- **Laravel Documentation:** https://laravel.com/docs
- **Filament Documentation:** https://filamentphp.com/docs
- **Nginx Documentation:** https://nginx.org/en/docs/

---

## Version Information

- **Deployment Date:** 2026-01-15
- **Laravel Version:** 12.0
- **PHP Version:** 8.4.16
- **Composer Version:** 2.9.3
- **Node Version:** 22.x (available on server)

---

*Last Updated: 2026-01-15*
