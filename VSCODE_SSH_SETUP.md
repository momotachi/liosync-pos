# Setup SSH di VS Code untuk Deployment

## Langkah 1: Install Ekstensi

1. Buka VS Code
2. Tekan `Ctrl+Shift+X` untuk buka Extensions
3. Cari dan install: **Remote - SSH** (oleh Microsoft)

## Langkah 2: Generate SSH Key (Jika belum ada)

**Di Windows (PowerShell/CMD):**

```bash
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

# Tekan Enter untuk default location
# Tekan Enter untuk no passphrase (atau masukkan passphrase untuk security)
```

**Copy public key ke VPS:**

```bash
# Method 1: Via ssh-copy-id (jika available)
ssh-copy-id root@your-server-ip

# Method 2: Manual copy
type C:\Users\YourUser\.ssh\id_rsa.pub

# Lalu SSH ke server dan paste ke ~/.ssh/authorized_keys
ssh root@your-server-ip
mkdir -p ~/.ssh
nano ~/.ssh/authorized_keys
# Paste content dari id_rsa.pub
```

## Langkah 3: Setup SSH Config

**Di Windows:**

Buka/create file: `C:\Users\YourUser\.ssh\config`

Paste konfigurasi berikut:

```
# Cashier POS VPS
Host cashier-vps
    HostName your-server-ip
    User root
    IdentityFile ~/.ssh/id_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3

# Atau jika pakai user biasa (lebih secure)
Host cashier-prod
    HostName your-server-ip
    User cashier
    IdentityFile ~/.ssh/id_rsa
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

## Langkah 4: Connect via VS Code

### Method 1: Command Palette

1. Tekan `F1` atau `Ctrl+Shift+P`
2. Ketik: `Remote-SSH: Connect to Host`
3. Pilih: `cashier-vps` (nama host dari config)
4. Pilih: Continue
5. Pilih: Linux
6. Tunggu koneksi terbuka

### Method 2: Status Bar

1. Klik icon remote di kiri bawah status bar
2. Pilih: `Remote-SSH: Connect to Host`
3. Pilih host yang sudah dikonfigurasi

## Langkah 5: Buka Folder di Remote

Setelah terkoneksi:

1. Tekan `Ctrl+Shift+P`
2. Ketik: `File: Open Folder`
3. Pilih: `/var/www/cashier` (path aplikasi di server)

## VS Code Settings untuk Remote

Buka Settings (`Ctrl+,`) dan tambahkan:

```json
{
    "files.autoSave": "afterDelay",
    "files.autoSaveDelay": 1000,
    "php.suggest.basic": false,
    "php.executablePath": "/usr/bin/php8.2",
    "terminal.integrated.cwd": "/var/www/cashier"
}
```

## Extensions untuk Remote Development

Install extensions ini secara otomatis saat connect ke remote:

- **PHP Intelephense** - PHP IntelliSense
- **Laravel Extra Intellisense** - Laravel autocomplete
- **Blade Formatter** - Format Blade files
- **GitLens** - Git supercharged
- **Material Icon Theme** - Icons
- **One Dark Pro** - Theme (optional)

## Tips & Tricks

### 1. Quick Terminal Commands

Di VS Code terminal remote:

```bash
# Cd ke project
cd /var/www/cashier

# Check Laravel status
php artisan about

# Run migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check logs
tail -f storage/logs/laravel.log

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### 2. Split Terminal untuk Monitoring

`Ctrl+Shift+5` - Split terminal

Terminal 1: Watch logs
```bash
tail -f storage/logs/laravel.log
```

Terminal 2: Run commands
```bash
php artisan migrate:fresh --seed
```

### 3. Edit Files langsung di Server

- Edit files di VS Code seperti biasa
- Auto-save terenable (files.autoSave)
- Changes langsung apply di server

### 4. Deploy dengan VS Code Tasks

Buka `.vscode/tasks.json` di project:

```json
{
    "version": "2.0.0",
    "tasks": [
        {
            "label": "Deploy: Pull Latest",
            "type": "shell",
            "command": "git pull origin main",
            "options": {
                "cwd": "/var/www/cashier"
            },
            "problemMatcher": []
        },
        {
            "label": "Laravel: Migrate",
            "type": "shell",
            "command": "php artisan migrate --force",
            "options": {
                "cwd": "/var/www/cashier"
            },
            "problemMatcher": []
        },
        {
            "label": "Laravel: Clear Cache",
            "type": "shell",
            "command": "php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear",
            "options": {
                "cwd": "/var/www/cashier"
            },
            "problemMatcher": []
        },
        {
            "label": "Laravel: Optimize",
            "type": "shell",
            "command": "php artisan config:cache && php artisan route:cache && php artisan view:cache",
            "options": {
                "cwd": "/var/www/cashier"
            },
            "problemMatcher": []
        },
        {
            "label": "Nginx: Restart",
            "type": "shell",
            "command": "sudo systemctl restart nginx",
            "problemMatcher": []
        },
        {
            "label": "Deploy: Full Update",
            "dependsOrder": "sequence",
            "dependsOn": [
                "Deploy: Pull Latest",
                "Laravel: Migrate",
                "Laravel: Clear Cache",
                "Laravel: Optimize",
                "Nginx: Restart"
            ]
        }
    ]
}
```

Run tasks: `Ctrl+Shift+P` → `Tasks: Run Task` → Pilih task

### 5. SFTP Extension (Alternative)

Jika tidak mau SSH, bisa pakai ekstensi **SFTP**:

1. Install ekstensi: `SFTP` oleh liximomo
2. Buat file `.vscode/sftp.json`:
```json
{
    "host": "your-server-ip",
    "port": 22,
    "username": "root",
    "privateKeyPath": "C:\\Users\\YourUser\\.ssh\\id_rsa",
    "remotePath": "/var/www/cashier",
    "uploadOnSave": true,
    "ignore": [
        ".vscode",
        ".git",
        "node_modules",
        "vendor"
    ]
}
```

3. Klik kanan file/folder → `SFTP: Upload`

## Troubleshooting

### Connection Timeout

```
Connection reset by x.x.x.x port 22
```

**Solution:**
1. Cek firewall di server: `sudo ufw status`
2. Pastikan port 22 open: `sudo ufw allow OpenSSH`
3. Restart SSH: `sudo systemctl restart sshd`

### Permission Denied

```
Permission denied (publickey)
```

**Solution:**
1. Pastikan public key sudah ada di `~/.ssh/authorized_keys` di server
2. Cek permission: `chmod 600 ~/.ssh/authorized_keys`
3. Cek SSH config: `sudo cat /etc/ssh/sshd_config`
   - Pastikan `PubkeyAuthentication yes`
   - Restart: `sudo systemctl restart sshd`

### VS Code Slow saat Remote

**Solution:**
1. Disable extensions lokal yang tidak perlu
2. Install extensions hanya saat di remote
3. Increase RAM limit di Settings

---

## Quick Reference

**Keyboard Shortcuts:**
- `Ctrl+Shift+P` - Command Palette
- `Ctrl+` - Toggle Terminal
- `Ctrl+Shift+` - New Terminal
- `Ctrl+Shift+5` - Split Terminal
- `Ctrl+B` - Toggle Sidebar
- `Ctrl+Shift+E` - Explorer

**Laravel Commands:**
```bash
php artisan about              # App info
php artisan migrate --force    # Run migrations
php artisan db:seed            # Seed database
php artisan cache:clear        # Clear cache
php artisan config:cache       # Cache config
php artisan route:cache        # Cache routes
php artisan view:cache         # Cache views
php artisan storage:link       # Link storage
```

**System Commands:**
```bash
systemctl status nginx         # Nginx status
systemctl restart nginx        # Restart Nginx
systemctl status php8.2-fpm    # PHP-FPM status
systemctl restart php8.2-fpm   # Restart PHP-FPM
tail -f storage/logs/laravel.log  # View logs
```
