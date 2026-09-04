@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

set "SRC=%cd%"
set "STAGING=%TEMP%\aviation_export_%RANDOM%"
set "OUTNAME=aviation_information_system_export.zip"
set "OUTPUT=%SRC%\%OUTNAME%"

echo ============================================
echo  Aviation Information System - Extract
echo ============================================
echo.
echo  This will copy all project files into a zip,
echo  excluding: .git, .claude, arduinocode.txt,
echo  backend\vendor (composer install) and
echo  frontend\node_modules (npm install)
echo.

echo [1/3] Copying files...
robocopy "%SRC%" "%STAGING%" /E /XD .git .claude vendor node_modules /XF arduinocode.txt "%OUTNAME%" /NFL /NDL /NJH /NJS /R:2 /W:1 >nul
if errorlevel 8 goto :error

echo [2/3] Compressing to %OUTNAME%...
if exist "%OUTPUT%" del /f /q "%OUTPUT%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "Compress-Archive -Path '%STAGING%\*' -DestinationPath '%OUTPUT%' -Force"
if errorlevel 1 goto :error

echo [3/3] Cleaning up temporary files...
rmdir /s /q "%STAGING%" 2>nul

echo.
echo ============================================
echo  Done! Created:
echo  %OUTPUT%
echo ============================================
pause
exit /b 0

:error
echo.
echo ============================================
echo  Extraction FAILED. See the error above.
echo ============================================
if exist "%STAGING%" rmdir /s /q "%STAGING%" 2>nul
pause
exit /b 1
