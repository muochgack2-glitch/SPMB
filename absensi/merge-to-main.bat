@echo off
echo ======================================
echo Merge Migration Branch to Main
echo ======================================
echo.

echo WARNING: This will merge migrate-to-spmb-stack to main branch!
echo.
echo Current branch:
git branch --show-current
echo.

set /p CONFIRM="Are you sure you want to continue? (yes/no): "
if /i not "%CONFIRM%"=="yes" (
    echo Merge cancelled.
    pause
    exit /b 1
)

echo.
echo [1/6] Checking for uncommitted changes...
git status --short
echo.

echo [2/6] Switching to main branch...
git checkout main
if errorlevel 1 (
    echo ERROR: Failed to switch to main branch!
    pause
    exit /b 1
)
echo.

echo [3/6] Merging migrate-to-spmb-stack...
git merge migrate-to-spmb-stack --no-ff -m "chore: merge SPMB stack migration to main"
if errorlevel 1 (
    echo ERROR: Merge failed! Resolve conflicts manually.
    pause
    exit /b 1
)
echo.

echo [4/6] Pushing to origin (SPMB)...
git push origin main
if errorlevel 1 (
    echo ERROR: Failed to push to origin!
    pause
    exit /b 1
)
echo.

echo [5/6] Pushing to absensi (Absensi)...
git push absensi main
if errorlevel 1 (
    echo ERROR: Failed to push to absensi!
    pause
    exit /b 1
)
echo.

echo [6/6] Showing merge log...
git log --oneline -10
echo.

echo ======================================
echo Merge Successful!
echo ======================================
echo.
echo Migration branch has been merged to main.
echo Changes pushed to both remotes (origin and absensi).
echo.
echo Next steps:
echo 1. SSH to production server
echo 2. Run: cd /www/wwwroot/absensi/Absensi/absensi
echo 3. Run: git pull origin main
echo 4. Follow DEPLOYMENT_GUIDE.md
echo.
pause
