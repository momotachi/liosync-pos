# DevOps Workflow - Liosync POS

## Alur yang Benar (Best Practice)

```
┌─────────────────┐      ┌──────────────┐      ┌─────────────────┐
│  Local Edit     │ ──>  │ Git Commit   │ ──>  │ GitHub Push     │
│  (VSCode)       │      │              │      │                 │
└─────────────────┘      └──────────────┘      └─────────────────┘
                                                        │
                                                        v
┌─────────────────┐      ┌──────────────┐      ┌─────────────────┐
│  Server Pull    │ <─── │  Auto Deploy │ <─── │  GitHub Repo    │
│  (Production)   │      │  (Optional)  │      │                 │
└─────────────────┘      └──────────────┘      └─────────────────┘
```

## Langkah-langkah

### 1. Local Development (Di Laptop/PC)
Edit file di VSCode lokal:
- Path: `d:\Cashier`
- Branch: `main`

### 2. Commit Changes (Git Local)
```bash
cd d:\Cashier
git add .
git commit -m "Description of changes"
```

### 3. Push to GitHub (Git Remote)
```bash
git push origin main
```

### 4. Deploy ke Server (Production)

#### Opsi A: Manual Pull (Simple)
```bash
ssh root@5.189.182.49
cd /var/www/liosync-pos
git pull origin main
php artisan config:clear
php artisan cache:clear
php artisan migrate
```

#### Opsi B: Deploy Script (Recommended)
```bash
# Dari local
deploy-ssh.bat
# Pilih opsi 2: Deploy files to production
```

#### Opsi C: CI/CD Auto Deploy (Advanced)
Gunakan GitHub Actions untuk auto-deploy setiap push.

## Setup GitHub Repository

### 1. Buat Repository Baru di GitHub
1. Buka https://github.com/new
2. Repository name: `liosync-pos` atau lainnya
3. Jangan centang "Add a README file"
4. Klik "Create repository"

### 2. Hubungkan Local ke GitHub
```bash
cd d:\Cashier
git remote add origin https://github.com/USERNAME/liosync-pos.git
git branch -M main
git push -u origin main
```

### 3. Setup Production Server
```bash
# SSH ke server
ssh root@5.189.182.49

# Clone repository
cd /var/www
mv liosync-pos liosync-pos.backup
git clone https://github.com/USERNAME/liosync-pos.git liosync-pos
cd liosync-pos

# Copy environment file
cp .env.production.example .env
# Edit .env sesuai production

# Install dependencies
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/liosync-pos
chmod -R 755 /var/www/liosync-pos
```

## Contoh Workflow Sehari-hari

### Scenario: Fix bug di CompanyController

```bash
# 1. Edit file lokal
# d:\Cashier\app\Http\Controllers\Company\CompanyController.php

# 2. Commit
git add app/Http/Controllers/Company/CompanyController.php
git commit -m "Fix branch creation code field"

# 3. Push ke GitHub
git push origin main

# 4. Deploy ke server
ssh root@5.189.182.49 "cd /var/www/liosync-pos && git pull && php artisan config:clear"
```

## Best Practices

### 1. Jangan Edit Langsung di Server
- Edit di local, commit, push, deploy
- Jangan SSH dan edit file di production

### 2. Selalu Tulis Commit Message yang Jelas
```bash
# ❌ Bad
git commit -m "fix"

# ✅ Good
git commit -m "Fix branch creation 500 error - add code field validation"
```

### 3. .env File Tidak Di-track di Git
- Pastikan `.env` ada di `.gitignore`
- Gunakan `.env.production.example` sebagai template
- Buat `.env` secara manual di server

### 4. Database Migration
- Selalu buat migration untuk perubahan database
- Jangan edit database langsung di production
- Jalankan `php artisan migrate` setelah deploy

### 5. Testing Sebelum Deploy
- Test di local environment dulu
- Pastikan tidak ada error di development

## File Konfigurasi Penting

### .gitignore
Pastikan file-file berikut TIDAK di-push ke GitHub:
- `.env`
- `storage/`
- `vendor/`
- `node_modules/`
- `*.log`

### .env.production
Di server, gunakan file `.env` dengan konfigurasi production:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=http://liosync.hasgroup.id`

## Troubleshooting

### Error: "failed to push some refs"
```bash
git pull --rebase origin main
git push origin main
```

### Error: "Permission denied"
```bash
ssh root@5.189.182.49
chown -R www-data:www-data /var/www/liosync-pos
```

### Server tidak up-to-date
```bash
ssh root@5.189.182.49 "cd /var/www/liosync-pos && git status"
```

## Current Status

✅ Git local sudah di-setup
✅ Initial commit sudah dibuat
⏳ GitHub repository belum dibuat
⏳ Server belum menggunakan Git (masih manual upload)

## Next Steps

1. Buat GitHub repository
2. Push local commits ke GitHub
3. Setup Git di production server
4. Deploy pertama kali dari GitHub
5. Untuk kedepannya: Edit local → Commit → Push → Server Pull
