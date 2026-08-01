# Fix Nginx Configuration & CSRF Token Issues

## Issue 1: Nginx Configuration Error

**Error:** `"location" directive is not allowed here in /www/server/panel/vhost/nginx/enable-php-84.conf:2`

**Root Cause:** The file `/www/server/panel/vhost/nginx/enable-php-84.conf` contains location directives outside of a server block, which is invalid nginx syntax.

### Solution:

```bash
# 1. Find where the file actually is
find /www/server -name "enable-php-84.conf" 2>/dev/null

# 2. Check the main vhost config for includes
grep -r "enable-php-84" /www/server/panel/vhost/nginx/

# 3. If found in the vhost config for absensi.smkpgriblora.sch.id, remove that include line
nano /www/server/panel/vhost/nginx/absensi.smkpgriblora.sch.id.conf

# Look for a line like:
# include /www/server/panel/vhost/nginx/enable-php-84.conf;
# Delete or comment it out (add # at the beginning)

# 4. Test nginx config
nginx -t

# 5. If test passes, restart nginx
systemctl restart nginx

# 6. Verify nginx is running
systemctl status nginx
ps aux | grep nginx | grep -v grep
```

**Alternative Quick Fix:**
```bash
# If you can't find/fix the include, just create an empty valid file:
echo "# PHP 8.4 enabled" > /tmp/enable-php-84.conf
# Then find and replace the malformed file with this one
```

---

## Issue 2: CSRF Token 419 Errors

**Error:** POST requests to `/whatsapp/gateway/start` and `/whatsapp/gateway/stop` return "419 Page Expired"

**Root Cause:** Browser session has expired CSRF token, or browser cache holding old tokens.

### Solution A: Simple Browser Fix (Try This First)

1. **Clear browser cache and cookies** for `absensi.smkpgriblora.sch.id`
2. **Close all browser tabs** for the site
3. **Reopen in incognito/private mode**
4. **Login again** (fresh session = fresh CSRF token)
5. **Try Start button again**

### Solution B: Add CSRF Exception (If Solution A Doesn't Work)

Since Laravel 11 doesn't use `VerifyCsrfToken.php` in app folder, we need to add the exception in `bootstrap/app.php`:

```bash
# On production server:
cd /www/wwwroot/absensi/Absensi/absensi

# Edit bootstrap/app.php
nano bootstrap/app.php
```

Add this code **before** the `return $app;` line:

```php
// CSRF exceptions for WhatsApp Gateway control
$app->middleware([
    'web' => [
        'except' => [
            'whatsapp/gateway/*'
        ]
    ]
]);
```

**Full example of what `bootstrap/app.php` should look like:**

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add CSRF exceptions here if needed
        $middleware->validateCsrfTokens(except: [
            'whatsapp/gateway/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Then clear cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## Testing After Fixes

### 1. Test Nginx:
```bash
curl -I http://absensi.smkpgriblora.sch.id/whatsapp
# Should return HTTP 302 (redirect to login) - means nginx works
```

### 2. Test Gateway Start (via curl to test CSRF):
```bash
# Login to get session cookie first (in browser)
# Then get CSRF token from page source

# Test start endpoint
curl -X POST http://localhost/whatsapp/gateway/start \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN_HERE" \
  -b "laravel_session=YOUR_SESSION_COOKIE"
```

### 3. Test in Browser:
- Clear cache and cookies
- Login fresh
- Navigate to WhatsApp Gateway page
- Click "Start Gateway Server (PM2)"
- Should show success message instead of 419 error

---

## Quick Status Check

```bash
# Check nginx
systemctl status nginx | head -5
ps aux | grep nginx | grep -v grep | head -3

# Check PHP-FPM
systemctl status php-fpm-84 | head -5

# Check PM2 processes
pm2 list

# Check gateway connectivity
curl http://localhost:3001/health

# Check Laravel logs for errors
tail -30 /www/wwwroot/absensi/Absensi/absensi/storage/logs/laravel.log
```

---

## Most Likely Solution Path

Based on the error logs, here's what probably happened:

1. You copied `enable-php-84.conf` to wrong location with wrong content
2. Nginx includes it and fails to parse
3. Gateway controls work via CLI (PM2) but browser has 419 CSRF errors

**Recommended fix order:**
1. ✅ Fix nginx first (remove bad include or fix conf file)
2. ✅ Restart nginx
3. ✅ Clear browser cache/cookies, login fresh
4. ✅ Test Start/Stop buttons
5. ✅ If still 419, add CSRF exception in bootstrap/app.php

---

## Success Indicators

- ✅ Nginx starts without errors: `nginx -t` passes
- ✅ Site loads: `curl -I http://absensi.smkpgriblora.sch.id/whatsapp` returns 302
- ✅ Gateway start button works: Shows success message, process appears in `pm2 list`
- ✅ No 419 errors in browser console
- ✅ PM2 process "whatsapp-gateway-absensi" shows as "online"
