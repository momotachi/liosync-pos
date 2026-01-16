@echo off
REM ============================================
REM Deploy to VPS via SSH from Windows
REM Cashier POS System
REM ============================================

setlocal enabledelayedexpansion

echo ========================================
echo   Cashier POS - SSH Deploy Script
echo ========================================
echo.

REM Configuration - Edit these values
set SERVER_USER=root
set SERVER_IP=your-server-ip
set PROJECT_PATH=/var/www/cashier
set LOCAL_PATH=%~dp0

echo Current Configuration:
echo   Server User: %SERVER_USER%
echo   Server IP:   %SERVER_IP%
echo   Project Path: %PROJECT_PATH%
echo   Local Path:  %LOCAL_PATH%
echo.

echo [1] Test SSH Connection
echo [2] Deploy Files Only
echo [3] Deploy and Run Setup
echo [4] Full Deployment (Files + Setup + SSL)
echo [5] Update Production
echo [6] Backup Remote Database
echo.

set /p CHOICE="Select option [1-6]: "

if "%CHOICE%"=="1" goto TEST_CONNECTION
if "%CHOICE%"=="2" goto DEPLOY_FILES
if "%CHOICE%"=="3" goto DEPLOY_SETUP
if "%CHOICE%"=="4" goto FULL_DEPLOY
if "%CHOICE%"=="5" goto UPDATE_PROD
if "%CHOICE%"=="6" goto BACKUP_DB
echo Invalid choice!
goto :eof

:TEST_CONNECTION
echo.
echo Testing SSH connection to %SERVER_IP%...
echo.
ssh %SERVER_USER%@%SERVER_IP% "echo 'Connection successful!' && uname -a"
goto :eof

:DEPLOY_FILES
echo.
echo Deploying files to %SERVER_IP%...
echo.

REM Create remote directory
ssh %SERVER_USER%@%SERVER_IP% "mkdir -p %PROJECT_PATH%"

REM Upload files (excluding unnecessary files)
echo Uploading application files...
scp -r ^
    app/^ ^
    bootstrap/^ ^
    config/^ ^
    database/^ ^
    public/^ ^
    resources/^ ^
    routes/^ ^
    storage/^ ^
    tests/^ ^
    vendor/^ ^
    .env.example ^
    artisan ^
    composer.json ^
    composer.lock ^
    package.json ^
    vite.config.js ^
    %SERVER_USER%@%SERVER_IP%:%PROJECT_PATH%/

echo Files deployed successfully!
goto :eof

:DEPLOY_SETUP
echo.
echo Deploying files and running setup...
echo.

call :DEPLOY_FILES

echo Running setup commands on server...
ssh %SERVER_USER%@%SERVER_IP% "cd %PROJECT_PATH% && bash deploy.sh"

goto :eof

:FULL_DEPLOY
echo.
echo Running full deployment...
echo.

call :DEPLOY_SETUP

echo.
echo Setup SSL Certificate?
set /p SSL_CHOICE="Install SSL? [y/N]: "

if /i "%SSL_CHOICE%"=="y" (
    echo.
    echo Enter domain name (e.g., pos.example.com):
    set /p DOMAIN_NAME=

    ssh %SERVER_USER%@%SERVER_IP% "certbot --nginx -d %DOMAIN_NAME%"
)

echo.
echo ========================================
echo   Full Deployment Complete!
echo ========================================
goto :eof

:UPDATE_PROD
echo.
echo Updating production server...
echo.

REM Optimize locally
echo Optimizing application locally...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM Upload only changed files
echo Uploading changed files...
scp -r ^
    app/^ ^
    config/^ ^
    database/migrations/^ ^
    resources/views/^ ^
    routes/^ ^
    composer.json ^
    composer.lock ^
    %SERVER_USER%@%SERVER_IP%:%PROJECT_PATH%/

REM Run remote commands
echo Running update commands on server...
ssh %SERVER_USER%@%SERVER_IP% << EOF
    cd %PROJECT_PATH%

    # Backup database
    mysqldump -u root cashier_db > backup_$(date +%%Y%%m%%d_%%H%%M%%S).sql

    # Install/update dependencies
    composer install --no-dev --optimize-autoloader --no-interaction

    # Run migrations
    php artisan migrate --force

    # Clear and cache configs
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Clear cache
    php artisan cache:clear

    # Set permissions
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    # Restart PHP-FPM
    systemctl restart php8.2-fpm
EOF

echo Update completed successfully!
goto :eof

:BACKUP_DB
echo.
echo Backing up remote database...
echo.

set BACKUP_NAME=backup_%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_NAME=%BACKUP_NAME: =0%

ssh %SERVER_USER%@%SERVER_IP% "mysqldump -u root cashier_db > /root/%BACKUP_NAME%.sql && echo 'Backup saved: /root/%BACKUP_NAME%.sql'"

goto :eof
