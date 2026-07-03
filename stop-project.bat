@echo off
setlocal EnableExtensions

set "PROJECT_DIR=%~dp0"
set "PID_DIR=%PROJECT_DIR%storage\app\runtime"

call :stop_one "%PID_DIR%\queue.pid"
call :stop_one "%PID_DIR%\scheduler.pid"
call :stop_one "%PID_DIR%\vite.pid"
call :stop_one "%PID_DIR%\server.pid"

exit /b 0

:stop_one
set "PID_FILE=%~1"

if not exist "%PID_FILE%" exit /b 0

set /p PID=<"%PID_FILE%"
if not defined PID (
    del "%PID_FILE%" >nul 2>&1
    exit /b 0
)

powershell -NoLogo -NoProfile -ExecutionPolicy Bypass -Command ^
    "Stop-Process -Id %PID% -Force -ErrorAction SilentlyContinue"

del "%PID_FILE%" >nul 2>&1
exit /b 0
