@echo off
REM ============================================
REM Deploy to VPS via Git - Best Practice
REM Liosync POS System
REM ============================================

setlocal enabledelayedexpansion

echo ========================================
echo   Liosync POS - Git Deploy Script
echo ========================================
echo.

REM Configuration
set SERVER_USER=root
set SERVER_IP=5.189.182.49
set PROJECT_PATH=/var/www/liosync-pos
set GITHUB_REPO=https://github.com/momotachi/liosync-pos.git

echo Current Configuration:
echo   Server:      %SERVER_USER%@%SERVER_IP%
echo   Project:     %PROJECT_PATH%
echo   Repository:  %GITHUB_REPO%
echo.

echo [1] Test SSH Connection
echo [2] Deploy to Production (Git Pull)
echo [3] Check Server Status
echo [4] View Recent Commits
echo [5] Backup Database
echo [6] Rollback to Previous Version
echo.

set /p CHOICE="Select option [1-6]: "

if "%CHOICE%"=="1" goto TEST_CONNECTION
if "%CHOICE%"=="2" goto DEPLOY
if "%CHOICE%"=="3" goto STATUS
if "%CHOICE%"=="4" goto COMMITS
if "%CHOICE%"=="5" goto BACKUP
if "%CHOICE%"=="6" goto ROLLBACK
echo Invalid choice!
goto :eof

:TEST_CONNECTION
echo.
echo Testing SSH connection...
ssh %SERVER_USER%@%SERVER_IP% "echo 'Connection successful!' && uname -a && git --version"
goto :eof

:DEPLOY
echo.
echo ========================================
echo   Deploying to Production
echo ========================================
echo.

REM Step 1: Commit local changes
echo [1/4] Checking local changes...
cd /d %~dp0
git status --short
echo.
set /p COMMIT_MSG="Enter commit message (or press Enter to skip commit): "
if not "%COMMIT_MSG%"=="" (
    git add .
    git commit -m "%COMMIT_MSG%"
    echo Local changes committed.
)

REM Step 2: Push to GitHub
echo.
echo [2/4] Pushing to GitHub...
git push origin main
if errorlevel 1 (
    echo ERROR: Git push failed!
    pause
    goto :eof
)
echo Pushed to GitHub successfully.

REM Step 3: Pull on server
echo.
echo [3/4] Pulling on server...
ssh %SERVER_USER%@%SERVER_IP% << 'ENDSSH'
    cd %PROJECT_PATH%

    # Backup database before update
    echo "Backing up database..."
    mysqldump -u liosync_user -p'Liosync@2025!' liosync_cashier > /root/liosync_backup_$(date +%%Y%%m%%d_%%H%%M%%S).sql

    # Pull latest changes
    echo "Pulling from GitHub..."
    git pull origin main

    # Run migrations if any
    echo "Running migrations..."
    php artisan migrate --force

    # Clear caches
    echo "Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear

    # Optimize for production
    echo "Optimizing..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Set permissions
    echo "Setting permissions..."
    chown -R www-data:www-data .
    chmod -R 755 .
    chmod -R 775 storage bootstrap/cache

    echo "Deployment successful!"
ENDSSH

if errorlevel 1 (
    echo ERROR: Deployment failed!
    pause
    goto :eof
)

REM Step 4: Verify
echo.
echo [4/4] Verifying deployment...
curl -s -o /dev/null -w "HTTP Status: %%{http_code}\n" http://liosync.hasgroup.id

echo.
echo ========================================
echo   Deployment Complete!
echo ========================================
goto :eof

:STATUS
echo.
echo Checking server status...
ssh %SERVER_USER%@%SERVER_IP% << 'ENDSSH'
    cd %PROJECT_PATH%
    echo "=== Git Status ==="
    git status
    echo.
    echo "=== Last Commit ==="
    git log -1 --oneline
    echo.
    echo "=== Application Status ==="
    curl -s -o /dev/null -w "HTTP Status: %%{http_code}\n" http://liosync.hasgroup.id
ENDSSH
goto :eof

:COMMITS
echo.
echo === Recent Commits ===
cd /d %~dp0
git log -5 --oneline --decorate
echo.
echo === Server Commits ===
ssh %SERVER_USER%@%SERVER_IP% "cd %PROJECT_PATH% && git log -5 --oneline"
goto :eof

:BACKUP
echo.
echo Backing up database...
ssh %SERVER_USER%@%SERVER_IP% "cd /PROJECT_PATH% && mysqldump -u liosync_user -p'Liosync@2025!' liosync_cashier > /root/liosync_backup_$(date +%%Y%%m%%d_%%H%%M%%S).sql && ls -lh /root/liosync_backup_*.sql | tail -1"
echo Database backed up.
goto :eof

:ROLLBACK
echo.
echo === Available Commits ===
ssh %SERVER_USER%@%SERVER_IP% "cd %PROJECT_PATH% && git log -10 --oneline"
echo.
set /p COMMIT_HASH="Enter commit hash to rollback to: "
ssh %SERVER_USER%@%SERVER_IP% "cd %PROJECT_PATH% && git reset --hard %COMMIT_HASH% && php artisan config:clear && php artisan cache:clear && systemctl restart php8.4-fpm"
echo Rolled back to commit %COMMIT_HASH%
goto :eof
