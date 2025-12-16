@echo off
REM ============================================
REM Backup Original Files
REM ============================================
echo ========================================
echo STEP 1: Creating Backup
echo ========================================

REM Create backup folder with timestamp
set timestamp=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%
set backupdir=Learning1_Backup_%timestamp%

echo Creating backup folder: %backupdir%
xcopy /E /I /H /Y . ..\%backupdir%

echo.
echo Backup created successfully!
echo Location: ..\%backupdir%
echo.
pause

echo.
echo Starting reorganization...
echo.

REM Continue to reorganize script
call reorganize.bat
