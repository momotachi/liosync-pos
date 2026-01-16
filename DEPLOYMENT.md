# Panduan Deployment Cashier POS ke VPS Linux

## Persiapan Sebelum Deployment

### 1. Persyaratan Sistem

**VPS Minimum Specifications:**
- CPU: 2 Core
- RAM: 2 GB
- Storage: 20 GB SSD
- OS: Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12

**Software yang akan diinstall:**
- PHP 8.2+
- Nginx
- MySQL/MariaDB
- Composer
- Git
- Certbot (untuk SSL)

### 2. Persiapan Lokal

Sebelum deploy, pastikan:

1. **Semua perubahan code sudah dicommit:**
```bash
git add .
git commit -m "Pre-production commit: security fixes and optimizations"
git push origin main
```

2. **Install dependencies di local:**
```bash
composer install --no-dev
npm run build
```

---

## Metode Deployment

### Metode 1: Deploy via SSH dari Windows (VS Code)

#### Langkah 1: Setup SSH di VS Code

1. Install ekstensi **Remote - SSH** di VS Code

2. Setup SSH config di Windows (`C:\Users\YourUser\.ssh\config`):
```
Host cashier-vps
    HostName your-server-ip
    User root
    IdentityFile ~/.ssh/id_rsa
```

3. Connect via VS Code:
   - Tekan `F1` → `Remote-SSH: Connect to Host`
   - Pilih `cashier-vps`

#### Langkah 2: Jalankan Script Deployment

**Option A: Automated Deployment (Batch Script)**

Di lokal Windows, jalankan:
```cmd
cd d:\Cashier
deploy-ssh.bat
```

Pilih opsi:
- Opsi 4: Full Deployment (recommended untuk pertama kali)

**Option B: Manual Deployment via SSH**

1. Upload files:
```cmd
scp -r d:\Cashier/* root@your-server-ip:/var/www/cashier/
```

2. SSH ke server:
```bash
ssh root@your-server-ip
```

3. Jalankan setup script:
```bash
cd /var/www/cashier
bash deploy.sh
```

---

### Metode 2: Direct Deployment di VPS

SSH ke VPS dan jalankan perintah berikut:

#### 1. Update System
```bash
apt update && apt upgrade -y
```

#### 2. Install Stack LEMP
```bash
# Install Nginx
apt install -y nginx

# Install PHP 8.2 + Extensions
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath

# Install MariaDB
apt install -y mariadb-server
mysql_secure_installation

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

#### 3. Clone Application
```bash
mkdir -p /var/www/cashier
cd /var/www/cashier

# Jika menggunakan Git
git clone https://your-repo-url.git .

# Atau upload manual via SCP/SFTP
```

#### 4. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

#### 5. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
nano .env  # Edit configuration
```

**Edit `.env` untuk production:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pos.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cashier_db
DB_USERNAME=cashier_user
DB_PASSWORD=your_secure_password

LOG_CHANNEL=stack
LOG_LEVEL=warning

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

#### 6. Setup Database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE cashier_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cashier_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON cashier_db.* TO 'cashier_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 7. Run Migrations
```bash
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder
```

#### 8. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

#### 9. Set Permissions
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## Konfigurasi Nginx

Buat virtual host:

```bash
nano /etc/nginx/sites-available/cashier
```

Paste konfigurasi berikut:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name pos.yourdomain.com;

    root /var/www/cashier/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript;
}
```

Aktifkan site:
```bash
ln -s /etc/nginx/sites-available/cashier /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

---

## Setup SSL dengan Let's Encrypt

```bash
# Install Certbot
apt install -y certbot python3-certbot-nginx

# Setup SSL
certbot --nginx -d pos.yourdomain.com

# Auto-renewal sudah otomatis dikonfigurasi
# Test renewal:
certbot renew --dry-run
```

---

## Firewall Configuration

```bash
# Install UFW
apt install -y ufw

# Configure firewall
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw enable

# Check status
ufw status
```

---

## Update Production

Untuk update aplikasi di production:

```bash
# SSH ke server
ssh root@your-server-ip

# CD ke project
cd /var/www/cashier

# Backup database
mysqldump -u root cashier_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Pull latest code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Restart services
systemctl restart php8.2-fpm
systemctl restart nginx
```

---

## Monitoring & Maintenance

### Check Application Status

```bash
# Check Laravel logs
tail -f /var/www/cashier/storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/cashier_error.log

# Check PHP-FPM status
systemctl status php8.2-fpm

# Check Nginx status
systemctl status nginx
```

### Database Backup Automation

Buat cron job untuk daily backup:

```bash
crontab -e
```

Tambahkan:
```cron
# Daily database backup at 2 AM
0 2 * * * mysqldump -u root cashier_db | gzip > /backups/cashier_$(date +\%Y\%m\%d).sql.gz

# Keep only last 7 days
0 3 * * * find /backups -name "cashier_*.sql.gz" -mtime +7 -delete
```

---

## Troubleshooting

### 1. Permission Errors
```bash
chown -R www-data:www-data /var/www/cashier
chmod -R 775 /var/www/cashier/storage
chmod -R 775 /var/www/cashier/bootstrap/cache
```

### 2. 502 Bad Gateway
```bash
# Check PHP-FPM
systemctl restart php8.2-fpm
systemctl status php8.2-fpm
```

### 3. Database Connection Failed
```bash
# Check MySQL status
systemctl status mariadb

# Test connection
mysql -u cashier_user -p cashier_db
```

### 4. Storage Link Issues
```bash
cd /var/www/cashier
php artisan storage:link
```

---

## Security Checklist

- [ ] APP_DEBUG=false di production
- [ ] Strong database password
- [ ] SSL/HTTPS enabled
- [ ] Firewall configured
- [ ] Regular backups
- [ ] Fail2ban installed (optional)
- [ ] Only necessary ports open
- [ ] Regular system updates

---

## Commands Reference

**Essential Commands:**

```bash
# Laravel
php artisan migrate:fresh --seed    # Reset database with seeders
php artisan config:clear            # Clear config cache
php artisan route:clear             # Clear route cache
php artisan cache:clear             # Clear application cache

# System
systemctl restart nginx             # Restart Nginx
systemctl restart php8.2-fpm        # Restart PHP-FPM
systemctl restart mariadb           # Restart MySQL

# Logs
tail -f storage/logs/laravel.log    # Laravel logs
tail -f /var/log/nginx/error.log    # Nginx error log
```

---

## Support

Jika ada masalah:
1. Cek logs: `tail -f storage/logs/laravel.log`
2. Cek Nginx error: `tail -f /var/log/nginx/error.log`
3. Restart services: `systemctl restart nginx php8.2-fpm`
