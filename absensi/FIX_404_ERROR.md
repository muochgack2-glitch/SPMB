# Fix Error 404 pada Gateway Start/Stop

## Problem
Error muncul: **"Error: HTTP 404"** saat klik tombol "Start Gateway Server (PM2)"

## Root Cause
Route `/whatsapp/gateway/start` tidak ditemukan, kemungkinan:
1. Route cache belum ter-clear dengan benar
2. File routes/web.php belum terupdate di production
3. Permissions issue pada storage/cache folders

---

## Quick Fix

Jalankan command ini **satu per satu** di server:

```bash
cd /www/wwwroot/absensi/Absensi/absensi

# 1. Pastikan code terbaru sudah ter-pull
git log --oneline -1
# Harus menunjukkan: f7f847b atau lebih baru

# 2. Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize (rebuild cache)
php artisan optimize:clear
php artisan optimize

# 4. Cek routes terdaftar
php artisan route:list | grep gateway

# Output seharusnya:
# POST   whatsapp/gateway/start           whatsapp.gateway.start
# POST   whatsapp/gateway/stop            whatsapp.gateway.stop
# GET    whatsapp/gateway/process-status  whatsapp.gateway.process-status

# 5. Cek permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www:www storage
chown -R www:www bootstrap/cache

# 6. Restart PHP-FPM
systemctl restart php-fpm-84
```

---

## Verification

### 1. Test Route via curl (dari server)

```bash
# Test dengan curl (untuk bypass browser cache)
curl -X POST http://localhost/whatsapp/gateway/start \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -v

# Jika route ada, akan return JSON response (bukan HTML 404)
# Expected: {"success":false,"message":"Gateway sudah running!"} atau similar
# Not expected: <!DOCTYPE html> ... 404 page
```

### 2. Test di Browser

1. **Hard refresh browser:**
   - Chrome/Edge: `Ctrl+Shift+R`
   - Firefox: `Ctrl+F5`

2. **Atau gunakan curl untuk test:**
   ```bash
   # Dari komputer lokal (ganti dengan IP server)
   curl -X POST https://absensi.smkpgriblora.sch.id/whatsapp/gateway/start \
     -H "Accept: application/json" \
     -v
   ```

3. **Buka browser developer console (F12):**
   - Tab "Network"
   - Klik tombol "Start Gateway Server"
   - Lihat request ke `/whatsapp/gateway/start`
   - Check response status: harus 200/500, bukan 404

---

## Alternative: Test Langsung Controller

```bash
cd /www/wwwroot/absensi/Absensi/absensi

# Buat test script
cat > test_gateway_route.php << 'EOF'
<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// List all routes
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'gateway')) {
        echo $route->methods()[0] . " /" . $route->uri() . " -> " . $route->getActionName() . "\n";
    }
}
EOF

# Run test
php test_gateway_route.php

# Expected output:
# POST /whatsapp/gateway/start -> App\Http\Controllers\WhatsAppController@startGateway
# POST /whatsapp/gateway/stop -> App\Http\Controllers\WhatsAppController@stopGateway
# GET /whatsapp/gateway/process-status -> App\Http\Controllers\WhatsAppController@getGatewayProcessStatus
```

---

## If Still 404

### Check nginx vhost config

```bash
cat /www/server/panel/vhost/nginx/absensi.smkpgriblora.sch.id.conf | grep -A 10 "location ~ \.php"

# Ensure it has:
# try_files $uri =404;
# fastcgi_pass unix:/tmp/php-cgi-84.sock;
```

### Check Laravel .env

```bash
cd /www/wwwroot/absensi/Absensi/absensi
cat .env | grep APP_

# Ensure:
# APP_ENV=production
# APP_DEBUG=false (or true for debugging)
# APP_URL=https://absensi.smkpgriblora.sch.id
```

### Check Laravel logs

```bash
tail -50 /www/wwwroot/absensi/Absensi/absensi/storage/logs/laravel.log

# Look for:
# - Route not found errors
# - Controller not found errors
# - Namespace issues
```

---

## Workaround: Use CLI to Start Gateway

Jika UI masih error, gunakan PM2 langsung:

```bash
# Start gateway
cd /www/wwwroot/absensi/Absensi/whatsapp-server-absensi
pm2 start server.js --name whatsapp-gateway-absensi

# Check status
pm2 list

# Stop gateway
pm2 stop whatsapp-gateway-absensi

# Restart
pm2 restart whatsapp-gateway-absensi

# View logs
pm2 logs whatsapp-gateway-absensi
```

---

## Most Likely Solution

Berdasarkan screenshot, kemungkinan besar masalahnya adalah **route cache**. 

**Solusi tercepat:**

```bash
cd /www/wwwroot/absensi/Absensi/absensi
php artisan optimize:clear
php artisan route:cache
systemctl restart php-fpm-84
```

Lalu di browser:
1. Hard refresh: `Ctrl+Shift+R`
2. Atau buka incognito mode
3. Login lagi
4. Coba tombol Start Gateway

---

## Success Indicators

- ✅ `php artisan route:list | grep gateway` shows 3 routes
- ✅ curl test returns JSON, not HTML
- ✅ Browser network tab shows 200 or 500 (not 404)
- ✅ Tombol Start Gateway shows success/error message (not 404)

---

## Contact for Support

Jika masih 404, kirim output dari:

```bash
cd /www/wwwroot/absensi/Absensi/absensi
echo "=== Git Status ==="
git log --oneline -3

echo "=== Routes ==="
php artisan route:list | grep gateway

echo "=== Permissions ==="
ls -la storage/ | head -5
ls -la bootstrap/cache/ | head -5

echo "=== Laravel Log ==="
tail -30 storage/logs/laravel.log

echo "=== Nginx Error Log ==="
tail -30 /www/wwwlogs/absensi.smkpgriblora.sch.id.error.log
```
