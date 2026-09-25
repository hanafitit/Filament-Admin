@echo off
setlocal EnableExtensions

set "PROJECT_DIR=%~dp0"
set "PHP_EXE=C:\tools\php-8.3.32\php.exe"
set "PHP_INI=C:\tools\php-8.3.32\php.ini"
set "PS_HELPER=%PROJECT_DIR%scripts\start-background.ps1"
set "PID_DIR=%PROJECT_DIR%storage\app\runtime"
set "LOG_DIR=%PROJECT_DIR%storage\logs"
set "VITE_MANIFEST=%PROJECT_DIR%public\build\manifest.json"

if not exist "%PHP_EXE%" (
    echo PHP not found: %PHP_EXE%
    pause
    exit /b 1
)

if not exist "%PS_HELPER%" (
    echo Helper not found: %PS_HELPER%
    pause
    exit /b 1
)

if not exist "%PID_DIR%" mkdir "%PID_DIR%"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

cd /d "%PROJECT_DIR%"

echo ==========================================
echo Freelance CRM launcher
echo ==========================================
echo Project dir: %PROJECT_DIR%
echo.

call "%PROJECT_DIR%stop-project.bat" >nul 2>&1

REM ============================================================
REM Frontend / Vite
REM ============================================================

where npm.cmd >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: npm not found.
    echo Install Node.js or add npm to PATH.
    pause
    exit /b 1
)

if not exist "node_modules\.bin\vite.cmd" (
    echo.
    echo Vite is not installed.
    echo Installing npm dependencies...
    echo.

    call npm install

    if errorlevel 1 (
        echo.
        echo ERROR: npm install failed.
        pause
        exit /b 1
    )
)

if not exist "%VITE_MANIFEST%" (
    echo.
    echo Vite manifest not found.
    echo Building frontend...
    echo.

    call npm run build

    if errorlevel 1 (
        echo.
        echo ERROR: npm run build failed.
        pause
        exit /b 1
    )

    if not exist "%VITE_MANIFEST%" (
        echo.
        echo ERROR: Vite build completed but manifest.json was not created.
        echo Expected: %VITE_MANIFEST%
        pause
        exit /b 1
    )

    echo.
    echo Vite build completed successfully.
) else (
    echo Vite manifest found. Skipping frontend build.
)

echo.

REM ============================================================
REM Background services
REM ============================================================

call :start_service "queue" """%PHP_EXE%"" -c ""%PHP_INI%"" artisan queue:work"
call :start_service "scheduler" """%PHP_EXE%"" -c ""%PHP_INI%"" artisan schedule:work"

echo.

REM ============================================================
REM Laravel
REM ============================================================

start "" http://127.0.0.1:8000/admin

echo Freelance CRM started.
echo Visible terminal: Laravel server
echo Background services: queue, scheduler
echo Logs: %LOG_DIR%
echo Stop: Ctrl+C in this window or run stop-project.bat
echo.

"%PHP_EXE%" -c "%PHP_INI%" artisan serve --host=0.0.0.0 --port=8000

call "%PROJECT_DIR%stop-project.bat" >nul 2>&1
exit /b %ERRORLEVEL%

REM ============================================================
REM Background service helper
REM ============================================================

:start_service
set "SERVICE_NAME=%~1"
set "SERVICE_CMD=%~2"
set "SERVICE_PID_FILE=%PID_DIR%\%SERVICE_NAME%.pid"
set "SERVICE_STDOUT_FILE=%LOG_DIR%\%SERVICE_NAME%.out.log"
set "SERVICE_STDERR_FILE=%LOG_DIR%\%SERVICE_NAME%.err.log"
set "SERVICE_WORKING_DIR=%PROJECT_DIR%"
set "SERVICE_COMMAND_LINE=%SERVICE_CMD%"

powershell -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%PS_HELPER%"

if exist "%SERVICE_PID_FILE%" exit /b 0

echo Failed to start %SERVICE_NAME%.
exit /b 1