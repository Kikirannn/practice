@echo off
echo ========================================
echo STEP 5: Creating Updated Files
echo ========================================
echo.
echo This script will create updated PHP files with corrected paths.
echo The updated files will be placed in their new locations.
echo.
echo Files to be updated:
echo - All view files (siswa, admin, teknisi)
echo - Header and footer partials
echo - Index and logout files
echo.
echo Press any key to start creating updated files...
pause >nul

echo.
echo Creating updated files...
echo This may take a moment...
echo.

REM The actual file updates will be done by PowerShell script for easier string replacement
powershell -ExecutionPolicy Bypass -File update_paths.ps1

echo.
echo ========================================
echo Path Updates Complete!
echo ========================================
echo.
echo Updated files have been created in their new locations.
echo.
echo Next step: Test your application!
echo Open: http://localhost/Learning1/
echo.
pause
