@echo off
REM ============================================================================
REM User Seeder Runner Script for Windows
REM Wrapper script untuk menjalankan user seeder dengan berbagai opsi
REM
REM Usage:
REM   run_seeder.bat [options]
REM
REM Options:
REM   test      Run unit tests only
REM   seed      Run seeder only
REM   all       Run tests then seeder (default)
REM   help      Show this help message
REM
REM Author: Kiro AI Assistant
REM Version: 1.0.0
REM ============================================================================

setlocal enabledelayedexpansion

REM Get script directory
set SCRIPT_DIR=%~dp0
set ROOT_DIR=%SCRIPT_DIR%..

REM Default mode
set MODE=all

REM Parse arguments
if "%1"=="" goto :check_prerequisites
if /i "%1"=="test" set MODE=test
if /i "%1"=="seed" set MODE=seed
if /i "%1"=="all" set MODE=all
if /i "%1"=="help" goto :show_help
if /i "%1"=="-h" goto :show_help
if /i "%1"=="/?" goto :show_help

:check_prerequisites
echo.
echo ===========================================
echo   JAGAPADI User Seeder
echo ===========================================
echo.

REM Check PHP
echo [INFO] Checking prerequisites...
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not in PATH
    exit /b 1
)

for /f "tokens=*" %%i in ('php -r "echo PHP_VERSION;"') do set PHP_VERSION=%%i
echo [OK] PHP version: %PHP_VERSION%

REM Check database connection
echo [INFO] Checking database connection...
php -r "require '%ROOT_DIR%\config\database.php'; try { $db = Database::getInstance()->getConnection(); echo 'OK'; } catch (Exception $e) { echo 'FAIL'; exit(1); }"
if errorlevel 1 (
    echo [ERROR] Database connection failed
    exit /b 1
)
echo [OK] Database connection successful
echo.

REM Execute based on mode
if "%MODE%"=="test" goto :run_tests
if "%MODE%"=="seed" goto :run_seeder
if "%MODE%"=="all" goto :run_all

:run_tests
echo ===========================================
echo   Running Unit Tests
echo ===========================================
echo.
php "%SCRIPT_DIR%test_user_seeder.php"
if errorlevel 1 (
    echo.
    echo [ERROR] Some tests failed
    exit /b 1
)
echo.
echo [OK] All tests passed
if "%MODE%"=="test" exit /b 0
goto :eof

:run_seeder
echo ===========================================
echo   Running User Seeder
echo ===========================================
echo.
php "%SCRIPT_DIR%seed_users.php"
if errorlevel 1 (
    echo.
    echo [ERROR] Seeder failed
    exit /b 1
)
echo.
echo [OK] Seeder completed successfully
exit /b 0

:run_all
call :run_tests
if errorlevel 1 (
    echo.
    echo [WARNING] Skipping seeder due to test failures
    exit /b 1
)
echo.
call :run_seeder
exit /b %errorlevel%

:show_help
echo User Seeder Runner Script for Windows
echo.
echo Usage:
echo     %~nx0 [options]
echo.
echo Options:
echo     test      Run unit tests only
echo     seed      Run seeder only
echo     all       Run tests then seeder (default)
echo     help      Show this help message
echo.
echo Examples:
echo     %~nx0                  # Run tests and seeder
echo     %~nx0 test            # Run tests only
echo     %~nx0 seed            # Run seeder only
echo.
echo For more information, see scripts\README.md
exit /b 0

:eof
endlocal
