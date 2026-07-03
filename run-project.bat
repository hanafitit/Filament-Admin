@echo off
setlocal EnableExtensions

set "PROJECT_DIR=%~dp0"
set "PHP_EXE=C:\tools\php-8.3.32\php.exe"
set "PHP_INI=C:\tools\php-8.3.32\php.ini"
set "PS_HELPER=%PROJECT_DIR%scripts\start-background.ps1"
set "PID_DIR=%PROJECT_DIR%storage\app\runtime"
set "LOG_DIR=%PROJECT_DIR%storage\logs"

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

echo Freelance CRM launcher v2
echo Project dir: %PROJECT_DIR%
echo.

call "%PROJECT_DIR%stop-project.bat" >nul 2>&1

call :start_service "queue" """%PHP_EXE%"" -c ""%PHP_INI%"" artisan queue:work"
call :start_service "scheduler" """%PHP_EXE%"" -c ""%PHP_INI%"" artisan schedule:work"

where npm.cmd >nul 2>&1
if %ERRORLEVEL%==0 (
    call :start_service "vite" "npm run dev"
)

start "" http://127.0.0.1:8000/admin

echo Freelance CRM started.
echo Visible terminal: Laravel server
echo Background services: queue, scheduler, vite
echo Logs: %LOG_DIR%
echo Stop: Ctrl+C in this window or run stop-project.bat
echo.

C:\tools\php-8.3.32\php.exe -c C:\tools\php-8.3.32\php.ini artisan serve --host=0.0.0.0 --port=8000

call "%PROJECT_DIR%stop-project.bat" >nul 2>&1
exit /b %ERRORLEVEL%

:start_service
set "SERVICE_NAME=%~1"
set "SERVICE_CMD=%~2"
set "SERVICE_PID_FILE=%PID_DIR%\%SERVICE_NAME%.pid"
set "SERVICE_STDOUT_FILE=%LOG_DIR%\%SERVICE_NAME%.out.log"
set "SERVICE_STDERR_FILE=%LOG_DIR%\%SERVICE_NAME%.err.log"
set "SERVICE_WORKING_DIR=%PROJECT_DIR%"
set "SERVICE_COMMAND_LINE=%SERVICE_CMD%"

powershell -NoLogo -NoProfile -ExecutionPolicy Bypass -File "%PS_HELPER%"

if exist "%PID_DIR%\%SERVICE_NAME%.pid" exit /b 0

echo Failed to start %SERVICE_NAME%.
exit /b 1
