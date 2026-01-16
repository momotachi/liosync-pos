@echo off
cd /d %~dp0
echo ============================================
echo   JuicePOS - Complete Installation
echo ============================================
echo.
echo Step 1: Installing Spatie Laravel Permission...
call composer update
echo.
echo Step 2: Publishing Spatie migrations...
call php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
echo.
echo Step 3: Running migrations...
call php artisan migrate --force
echo.
echo Step 4: Running RolePermission seeder...
call php artisan db:seed --class=RolePermissionSeeder
echo.
echo Step 5: Running AdminUser seeder...
call php artisan db:seed --class=AdminUserSeeder
echo.
echo ============================================
echo   Installation Complete!
echo ============================================
echo.
echo You can now login with credentials in CREDENTIALS.txt
echo.
pause
