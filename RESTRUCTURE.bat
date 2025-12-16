@echo off
REM ============================================
REM MASTER SCRIPT - Run All Restructuring Steps
REM ============================================

color 0A
echo.
echo ========================================
echo   BACKEND-FRONTEND RESTRUCTURING
echo   Learning1 Project
echo ========================================
echo.
echo This script will:
echo   1. Create backup of current files
echo   2. Reorganize folder structure
echo   3. Update all file paths
echo.
echo IMPORTANT:
echo - Make sure no files are currently open
echo - Close any running PHP server
echo - This will take a few minutes
echo.
echo Press any key to START or Ctrl+C to CANCEL...
pause >nul

REM Step 1: Backup
echo.
echo ========================================
echo STEP 1/3: Creating Backup
echo ========================================
call 1_backup.bat

REM Step 2: Reorganize
echo.
echo ========================================
echo STEP 2/3: Reorganizing Files
echo ========================================
call 2_reorganize.bat

REM Step 3: Update Paths
echo.
echo ========================================
echo STEP 3/3: Updating File Paths
echo ========================================
call 3_update_paths.bat

REM Done
echo.
echo ========================================
echo   RESTRUCTURING COMPLETE!
echo ========================================
echo.
echo Your project has been reorganized into:
echo.
echo   views/           - All presentation files
echo   public/          - Assets and uploads
echo   backend/         - (Ready for controllers/models)
echo   config/          - Configuration files
echo   includes/        - Helper functions
echo.
echo NEXT STEPS:
echo   1. Test the application: http://localhost/Learning1/
echo   2. Login with: siswa1 / password123
echo   3. Test all features
echo   4. If everything works, you can delete old folders:
echo      - siswa/
echo      - admin/
echo      - teknisi/
echo      - assets/
echo.
echo Backup location: ..\Learning1_Backup_*
echo.
pause
