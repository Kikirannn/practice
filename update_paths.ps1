# PowerShell Script to Update All File Paths
Write-Host "Updating file paths..." -ForegroundColor Green

# Function to update paths in a file
function Update-Paths {
    param (
        [string]$FilePath
    )
    
    if (Test-Path $FilePath) {
        $content = Get-Content $FilePath -Raw
        
        # Update require_once paths
        $content = $content -replace "require_once '../config/", "require_once '../../config/"
        $content = $content -replace "require_once '../includes/", "require_once '../../includes/"
        $content = $content -replace "require_once 'config/", "require_once '../config/"
        $content = $content -replace "require_once 'includes/", "require_once '../includes/"
        
        # Update header/footer includes
        $content = $content -replace "include '../includes/header.php'", "include '../partials/header.php'"
        $content = $content -replace "include '../includes/footer.php'", "include '../partials/footer.php'"
        $content = $content -replace "include 'includes/header.php'", "include 'views/partials/header.php'"
        $content = $content -replace "include 'includes/footer.php'", "include 'views/partials/footer.php'"
        
        # Update asset paths in HTML
        $content = $content -replace "/Learning1/assets/css/", "/Learning1/public/css/"
        $content = $content -replace "/Learning1/assets/js/", "/Learning1/public/js/"
        $content = $content -replace "/Learning1/uploads/", "/Learning1/public/uploads/"
        
        # Update redirect paths for views
        $content = $content -replace "redirect\('/Learning1/siswa/", "redirect('/Learning1/views/siswa/"
        $content = $content -replace "redirect\('/Learning1/admin/", "redirect('/Learning1/views/admin/"
        $content = $content -replace "redirect\('/Learning1/teknisi/", "redirect('/Learning1/views/teknisi/"
        $content = $content -replace "redirect\('/Learning1/login.php'\)", "redirect('/Learning1/views/auth/login.php')"
        
        # Update form actions
        $content = $content -replace 'action="../', 'action="../../'
        
        # Save updated content
        Set-Content -Path $FilePath -Value $content -NoNewline
        Write-Host "Updated: $FilePath" -ForegroundColor Cyan
    }
}

# Update all view files
Write-Host "`nUpdating Siswa views..." -ForegroundColor Yellow
Get-ChildItem "views\siswa\*.php" -ErrorAction SilentlyContinue | ForEach-Object { Update-Paths $_.FullName }

Write-Host "`nUpdating Admin views..." -ForegroundColor Yellow
Get-ChildItem "views\admin\*.php" -ErrorAction SilentlyContinue | ForEach-Object { Update-Paths $_.FullName }

Write-Host "`nUpdating Teknisi views..." -ForegroundColor Yellow
Get-ChildItem "views\teknisi\*.php" -ErrorAction SilentlyContinue | ForEach-Object { Update-Paths $_.FullName }

Write-Host "`nUpdating Auth views..." -ForegroundColor Yellow
Update-Paths "views\auth\login.php"

Write-Host "`nUpdating Partials..." -ForegroundColor Yellow
Update-Paths "views\partials\header.php"
Update-Paths "views\partials\footer.php"

Write-Host "`nUpdating Root files..." -ForegroundColor Yellow
Update-Paths "index.php"
Update-Paths "logout.php"

Write-Host "`nUpdating includes/functions.php for upload path..." -ForegroundColor Yellow
if (Test-Path "includes\functions.php") {
    $content = Get-Content "includes\functions.php" -Raw
    $content = $content -replace "uploadDir = 'uploads/'", "uploadDir = 'public/uploads/'"
    Set-Content -Path "includes\functions.php" -Value $content -NoNewline
}

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Path update complete!" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Green
