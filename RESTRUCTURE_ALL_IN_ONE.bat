@echo off
REM ============================================================
REM BACKEND-FRONTEND RESTRUCTURING - ALL IN ONE
REM Sistem Pelaporan Kerusakan Fasilitas Sekolah
REM ============================================================

color 0A
title Backend-Frontend Restructuring

echo.
echo ============================================================
echo   BACKEND-FRONTEND RESTRUCTURING
echo   Learning1 Project - All-in-One Script
echo ============================================================
echo.
echo This script will:
echo   1. Create backup of all current files
echo   2. Create new folder structure (views/, public/, backend/)
echo   3. Move files to new locations
echo   4. Update all file paths automatically
echo   5. Verify the restructure
echo.
echo IMPORTANT:
echo   - Close all open files in editor
echo   - Stop PHP development server if running
echo   - Process takes about 2-3 minutes
echo.
echo Press any key to START or Ctrl+C to CANCEL...
pause >nul

REM ====================================
REM STEP 1: CREATE BACKUP
REM ====================================
echo.
echo ============================================================
echo STEP 1/4: Creating Backup
echo ============================================================

set timestamp=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%
set backupdir=..\Learning1_Backup_%timestamp%

echo Creating backup folder: %backupdir%
xcopy /E /I /H /Y . %backupdir% >nul 2>&1

if exist %backupdir% (
    echo [OK] Backup created successfully!
    echo Location: %backupdir%
) else (
    echo [ERROR] Backup failed!
    echo Aborting...
    pause
    exit /b 1
)

echo.
pause

REM ====================================
REM STEP 2: CREATE FOLDER STRUCTURE
REM ====================================
echo.
echo ============================================================
echo STEP 2/4: Creating New Folder Structure
echo ============================================================

echo Creating folders...

mkdir views 2>nul
mkdir views\auth 2>nul
mkdir views\siswa 2>nul
mkdir views\admin 2>nul
mkdir views\teknisi 2>nul
mkdir views\partials 2>nul

mkdir public 2>nul
mkdir public\css 2>nul
mkdir public\js 2>nul
mkdir public\uploads 2>nul

mkdir backend 2>nul

echo [OK] Folder structure created!
echo.
pause

REM ====================================
REM STEP 3: MOVE FILES
REM ====================================
echo.
echo ============================================================
echo STEP 3/4: Moving Files to New Structure
echo ============================================================

echo Moving view files...
if exist login.php move /Y login.php views\auth\ >nul 2>&1

if exist siswa\*.php (
    for %%F in (siswa\*.php) do (
        move /Y "%%F" views\siswa\ >nul 2>&1
        echo   Moved: %%~nxF
    )
)

if exist admin\*.php (
    for %%F in (admin\*.php) do (
        move /Y "%%F" views\admin\ >nul 2>&1
        echo   Moved: %%~nxF
    )
)

if exist teknisi\*.php (
    for %%F in (teknisi\*.php) do (
        move /Y "%%F" views\teknisi\ >nul 2>&1
        echo   Moved: %%~nxF
    )
)

echo Moving partials...
if exist includes\header.php copy /Y includes\header.php views\partials\header.php >nul 2>&1
if exist includes\footer.php copy /Y includes\footer.php views\partials\footer.php >nul 2>&1

echo Moving assets...
if exist assets\css\*.* xcopy /E /I /Y assets\css\*.* public\css\ >nul 2>&1
if exist assets\js\*.* xcopy /E /I /Y assets\js\*.* public\js\ >nul 2>&1

echo Copying uploads...
if exist uploads\*.* xcopy /E /I /Y uploads\*.* public\uploads\ >nul 2>&1

echo [OK] Files moved successfully!
echo.
pause

REM ====================================
REM STEP 4: UPDATE PATHS
REM ====================================
echo.
echo ============================================================
echo STEP 4/4: Updating File Paths
echo ============================================================

echo Updating paths in view files...

REM Update siswa views
for %%F in (views\siswa\*.php) do (
    powershell -Command "(Get-Content '%%F') -replace \"require_once '../config/\", \"require_once '../../config/\" -replace \"require_once '../includes/\", \"require_once '../../includes/\" -replace \"include '../includes/header.php'\", \"include '../partials/header.php'\" -replace \"include '../includes/footer.php'\", \"include '../partials/footer.php'\" -replace '/Learning1/assets/css/', '/Learning1/public/css/' -replace '/Learning1/assets/js/', '/Learning1/public/js/' -replace '/Learning1/uploads/', '/Learning1/public/uploads/' -replace \"redirect\('/Learning1/siswa/\", \"redirect('/Learning1/views/siswa/\" | Set-Content '%%F'" >nul 2>&1
    echo   Updated: %%~nxF
)

REM Update admin views
for %%F in (views\admin\*.php) do (
    powershell -Command "(Get-Content '%%F') -replace \"require_once '../config/\", \"require_once '../../config/\" -replace \"require_once '../includes/\", \"require_once '../../includes/\" -replace \"include '../includes/header.php'\", \"include '../partials/header.php'\" -replace \"include '../includes/footer.php'\", \"include '../partials/footer.php'\" -replace '/Learning1/assets/css/', '/Learning1/public/css/' -replace '/Learning1/assets/js/', '/Learning1/public/js/' -replace '/Learning1/uploads/', '/Learning1/public/uploads/' -replace \"redirect\('/Learning1/admin/\", \"redirect('/Learning1/views/admin/\" | Set-Content '%%F'" >nul 2>&1
    echo   Updated: %%~nxF
)

REM Update teknisi views
for %%F in (views\teknisi\*.php) do (
    powershell -Command "(Get-Content '%%F') -replace \"require_once '../config/\", \"require_once '../../config/\" -replace \"require_once '../includes/\", \"require_once '../../includes/\" -replace \"include '../includes/header.php'\", \"include '../partials/header.php'\" -replace \"include '../includes/footer.php'\", \"include '../partials/footer.php'\" -replace '/Learning1/assets/css/', '/Learning1/public/css/' -replace '/Learning1/assets/js/', '/Learning1/public/js/' -replace '/Learning1/uploads/', '/Learning1/public/uploads/' -replace \"redirect\('/Learning1/teknisi/\", \"redirect('/Learning1/views/teknisi/\" | Set-Content '%%F'" >nul 2>&1
    echo   Updated: %%~nxF
)

REM Update auth view
if exist views\auth\login.php (
    powershell -Command "(Get-Content 'views\auth\login.php') -replace \"require_once 'config/\", \"require_once '../../config/\" -replace \"require_once 'includes/\", \"require_once '../../includes/\" -replace '/Learning1/assets/css/', '/Learning1/public/css/' -replace '/Learning1/assets/js/', '/Learning1/public/js/' -replace \"redirect\('/Learning1/siswa/\", \"redirect('/Learning1/views/siswa/\" -replace \"redirect\('/Learning1/admin/\", \"redirect('/Learning1/views/admin/\" -replace \"redirect\('/Learning1/teknisi/\", \"redirect('/Learning1/views/teknisi/\" | Set-Content 'views\auth\login.php'" >nul 2>&1
    echo   Updated: login.php
)

REM Update partials
if exist views\partials\header.php (
    powershell -Command "(Get-Content 'views\partials\header.php') -replace '/Learning1/assets/css/', '/Learning1/public/css/' -replace '/Learning1/assets/js/', '/Learning1/public/js/' -replace '/Learning1/siswa/', '/Learning1/views/siswa/' -replace '/Learning1/admin/', '/Learning1/views/admin/' -replace '/Learning1/teknisi/', '/Learning1/views/teknisi/' -replace '/Learning1/login.php', '/Learning1/views/auth/login.php' | Set-Content 'views\partials\header.php'" >nul 2>&1
    echo   Updated: header.php
)

REM Update index.php
if exist index.php (
    powershell -Command "(Get-Content 'index.php') -replace \"redirect\('/Learning1/siswa/\", \"redirect('/Learning1/views/siswa/\" -replace \"redirect\('/Learning1/admin/\", \"redirect('/Learning1/views/admin/\" -replace \"redirect\('/Learning1/teknisi/\", \"redirect('/Learning1/views/teknisi/\" -replace \"redirect\('/Learning1/login.php'\)", \"redirect('/Learning1/views/auth/login.php')\" | Set-Content 'index.php'" >nul 2>&1
    echo   Updated: index.php
)

REM Update logout.php
if exist logout.php (
    powershell -Command "(Get-Content 'logout.php') -replace \"redirect\('/Learning1/login.php'\)", \"redirect('/Learning1/views/auth/login.php')\" | Set-Content 'logout.php'" >nul 2>&1
    echo   Updated: logout.php
)

REM Update functions.php for upload path
if exist includes\functions.php (
    powershell -Command "(Get-Content 'includes\functions.php') -replace \"uploadDir = 'uploads/'\", \"uploadDir = 'public/uploads/'\" | Set-Content 'includes\functions.php'" >nul 2>&1
    echo   Updated: functions.php
)

echo [OK] All paths updated!
echo.
pause

REM ====================================
REM VERIFICATION
REM ====================================
echo.
echo ============================================================
echo VERIFICATION
echo ============================================================

echo Checking new structure...
echo.

set error_count=0

if exist "views\siswa" (echo [OK] views/siswa/) else (echo [ERROR] views/siswa/ missing! && set /a error_count+=1)
if exist "views\admin" (echo [OK] views/admin/) else (echo [ERROR] views/admin/ missing! && set /a error_count+=1)
if exist "views\teknisi" (echo [OK] views/teknisi/) else (echo [ERROR] views/teknisi/ missing! && set /a error_count+=1)
if exist "views\auth" (echo [OK] views/auth/) else (echo [ERROR] views/auth/ missing! && set /a error_count+=1)
if exist "views\partials" (echo [OK] views/partials/) else (echo [ERROR] views/partials/ missing! && set /a error_count+=1)
if exist "public\css" (echo [OK] public/css/) else (echo [ERROR] public/css/ missing! && set /a error_count+=1)
if exist "public\js" (echo [OK] public/js/) else (echo [ERROR] public/js/ missing! && set /a error_count+=1)
if exist "views\auth\login.php" (echo [OK] login.php moved) else (echo [ERROR] login.php not moved! && set /a error_count+=1)

echo.

if %error_count% == 0 (
    echo ============================================================
    echo   RESTRUCTURING COMPLETE! SUCCESS!
    echo ============================================================
    echo.
    echo New Structure:
    echo   views/           - All presentation files
    echo   public/          - Assets and uploads
    echo   backend/         - Ready for controllers/models
    echo   config/          - Configuration
    echo   includes/        - Helpers
    echo.
    echo NEXT STEPS:
    echo   1. Test aplikasi: http://localhost/Learning1/
    echo   2. Login: siswa1 / password123
    echo   3. Test all features
    echo   4. If OK, delete old empty folders:
    echo      - siswa/ (now empty)
    echo      - admin/ (now empty)
    echo      - teknisi/ (now empty)
    echo      - assets/ (now empty)
    echo.
    echo Backup location: %backupdir%
) else (
    echo ============================================================
    echo   WARNING: %error_count% ERRORS DETECTED!
    echo ============================================================
    echo.
    echo Some files may be missing. Please check manually.
    echo You can restore from backup: %backupdir%
)

echo.
echo ============================================================
pause
