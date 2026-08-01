@echo off
echo ======================================
echo Testing Absensi Migration to SPMB Stack
echo ======================================
echo.

echo [1/8] Checking PHP version...
php -v | findstr "PHP"
echo.

echo [2/8] Clearing all caches...
php artisan optimize:clear
echo.

echo [3/8] Checking routes...
php artisan route:list | findstr "dashboard"
echo.

echo [4/8] Checking if views compile...
php artisan view:clear
echo View cache cleared successfully!
echo.

echo [5/8] Testing database connection...
php artisan migrate:status
echo.

echo [6/8] Checking for console errors...
php artisan list | findstr "attendance"
echo.

echo [7/8] Dumping autoload...
composer dump-autoload --no-interaction
echo.

echo [8/8] Running basic test...
php artisan tinker --execute="echo 'Laravel working: ' . app()->version();"
echo.

echo ======================================
echo Test Complete!
echo ======================================
echo.
echo Next steps:
echo 1. Run: php artisan serve
echo 2. Open: http://localhost:8000/attendance/dashboard
echo 3. Login and test features
echo.
pause
