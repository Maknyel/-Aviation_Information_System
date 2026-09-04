@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

REM Look for mysql.exe within this project's folder structure
set "MYSQL_BIN="

if exist "%~dp0xampp\mysql\bin\mysql.exe" (
    set "MYSQL_BIN=%~dp0xampp\mysql\bin\mysql.exe"
) else if exist "%~dp0mysql\bin\mysql.exe" (
    set "MYSQL_BIN=%~dp0mysql\bin\mysql.exe"
) else (
    for /r "%~dp0" %%F in (mysql.exe) do (
        if not defined MYSQL_BIN (
            set "MYSQL_BIN=%%F"
        )
    )
)

if not defined MYSQL_BIN (
    echo.
    echo ERROR: Could not find mysql.exe inside this project folder.
    echo Please place your XAMPP folder inside this project directory
    echo ^(e.g. .\xampp\mysql\bin\mysql.exe^) or edit install.bat manually.
    pause
    exit /b 1
)

set DB_NAME=aviation09032026

echo ============================================
echo  Aviation Information System - Install
echo ============================================
echo  Using MySQL: %MYSQL_BIN%
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