@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

if exist "C:\xampp\mysql\bin\mysql.exe" (
    set "MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe"
) else if exist "D:\xampp\mysql\bin\mysql.exe" (
    set "MYSQL_BIN=D:\xampp\mysql\bin\mysql.exe"
) else (
    echo.
    echo ERROR: Could not find XAMPP's mysql.exe on C:\xampp or D:\xampp.
    echo Please edit install.bat and set MYSQL_BIN to your XAMPP mysql.exe path.
    pause
    exit /b 1
)
set DB_NAME=aviation09032026

echo ============================================
echo  Aviation Information System - Install
echo ============================================

echo.
echo [1/8] Checking MySQL connection...
"%MYSQL_BIN%" -u root -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: Could not connect to MySQL at %MYSQL_BIN%.
    echo Make sure XAMPP's MySQL module is started, then run this script again.
    pause
    exit /b 1
)

echo.
echo [2/8] Creating database "%DB_NAME%" if it does not exist...
"%MYSQL_BIN%" -u root -e "CREATE DATABASE IF NOT EXISTS %DB_NAME%;"
if errorlevel 1 goto :error

echo.
echo [3/8] Setting backend\.env to use "%DB_NAME%"...
powershell -NoProfile -Command "(Get-Content 'backend\.env') -replace '^DB_DATABASE=.*', 'DB_DATABASE=%DB_NAME%' -replace '^DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content 'backend\.env'"
if errorlevel 1 goto :error

echo.
echo [4/8] Installing backend dependencies (composer install)...
cd backend
call composer install
if errorlevel 1 goto :error

echo.
echo [5/8] Running database migrations...
call php artisan migrate --force
if errorlevel 1 goto :error

echo.
echo [6/8] Seeding the database...
call php artisan db:seed --force
if errorlevel 1 goto :error

echo.
echo [6b/8] Linking public storage folder...
call php artisan storage:link

cd ..

echo.
echo [7/8] Installing frontend dependencies (npm install)...
cd frontend
call npm install
if errorlevel 1 goto :error

echo.
echo [8/8] Building frontend (npm run build)...
call npm run build
if errorlevel 1 goto :error

cd ..

echo.
echo ============================================
echo  Installation complete!
echo  Use setup.bat to start the dev servers.
echo ============================================
pause
exit /b 0

:error
echo.
echo ============================================
echo  Installation FAILED. See the error above.
echo ============================================
cd /d "%~dp0"
pause
exit /b 1
