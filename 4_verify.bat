@echo off
REM Quick verification script to check if restructure was successful

echo.
echo ========================================
echo   RESTRUCTURE VERIFICATION
echo ========================================
echo.

echo Checking new folder structure...
echo.

if exist "views" (
    echo [OK] views/ folder exists
) else (
    echo [ERROR] views/ folder missing!
)

if exist "views\siswa" (
    echo [OK] views/siswa/ folder exists
) else (
    echo [ERROR] views/siswa/ folder missing!
)

if exist "views\admin" (
    echo [OK] views/admin/ folder exists
) else (
    echo [ERROR] views/admin/ folder missing!
)

if exist "views\teknisi" (
    echo [OK] views/teknisi/ folder exists
) else (
    echo [ERROR] views/teknisi/ folder missing!
)

if exist "public" (
    echo [OK] public/ folder exists
) else (
    echo [ERROR] public/ folder missing!
)

if exist "public\css" (
    echo [OK] public/css/ folder exists
) else (
    echo [ERROR] public/css/ folder missing!
)

echo.
echo Checking key files...
echo.

if exist "views\auth\login.php" (
    echo [OK] login.php moved to views/auth/
) else (
    echo [ERROR] views/auth/login.php missing!
)

if exist "views\partials\header.php" (
    echo [OK] header.php moved to views/partials/
) else (
    echo [ERROR] views/partials/header.php missing!
)

if exist "public\css\style.css" (
    echo [OK] CSS moved to public/css/
) else (
    echo [ERROR] public/css/style.css missing!
)

echo.
echo ========================================
echo Verification complete!
echo ========================================
echo.
echo If all checks show [OK], restructure is successful!
echo If any show [ERROR], review the reorganization process.
echo.
pause
