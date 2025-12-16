@echo off
REM ============================================
REM Reorganize Files into Backend-Frontend Structure
REM ============================================

echo ========================================
echo STEP 2: Creating New Folder Structure
echo ========================================

REM Create main folders
if not exist "views" mkdir views
if not exist "views\auth" mkdir views\auth
if not exist "views\siswa" mkdir views\siswa
if not exist "views\admin" mkdir views\admin
if not exist "views\teknisi" mkdir views\teknisi
if not exist "views\partials" mkdir views\partials

if not exist "public" mkdir public
if not exist "public\css" mkdir public\css
if not exist "public\js" mkdir public\js
if not exist "public\uploads" mkdir public\uploads

if not exist "backend" mkdir backend

echo Folder structure created!
echo.

echo ========================================
echo STEP 3: Moving Files
echo ========================================

REM Move views
echo Moving view files...
move /Y login.php views\auth\ >nul 2>&1

if exist "siswa" (
    move /Y siswa\*.php views\siswa\ >nul 2>&1
)

if exist "admin" (
    move /Y admin\*.php views\admin\ >nul 2>&1
)

if exist "teknisi" (
    move /Y teknisi\*.php views\teknisi\ >nul 2>&1
)

REM Move partials
if exist "includes\header.php" (
    copy /Y includes\header.php views\partials\header.php >nul 2>&1
)

if exist "includes\footer.php" (
    copy /Y includes\footer.php views\partials\footer.php >nul 2>&1
)

REM Move public assets
echo Moving assets...
if exist "assets\css" (
    xcopy /E /I /Y assets\css\*.* public\css\ >nul 2>&1
)

if exist "assets\js" (
    xcopy /E /I /Y assets\js\*.* public\js\ >nul 2>&1
)

REM Copy uploads (don't delete originals yet)
if exist "uploads" (
    xcopy /E /I /Y uploads\*.* public\uploads\ >nul 2>&1
)

echo.
echo Files moved successfully!
echo.

echo ========================================
echo STEP 4: Cleaning Up Old Folders
echo ========================================

REM Remove old empty folders (optional - commented for safety)
REM rmdir /S /Q siswa
REM rmdir /S /Q admin
REM rmdir /S /Q teknisi
REM rmdir /S /Q assets

echo.
echo Old folders preserved (not deleted for safety)
echo You can manually delete siswa/, admin/, teknisi/, assets/ folders after verification
echo.

echo ========================================
echo Reorganization Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Run "3_update_paths.bat" to update file paths
echo 2. Test the application
echo 3. If everything works, delete old folders manually
echo.
pause
