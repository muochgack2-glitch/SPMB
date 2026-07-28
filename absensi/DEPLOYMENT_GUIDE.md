# 🚀 Production Deployment Guide

**Sistem Absensi QR Code Scanner**  
**Version:** 1.0.0  
**Date:** 14 Juni 2026

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Server Setup](#server-setup)
3. [Application Deployment](#application-deployment)
4. [WhatsApp Gateway Setup](#whatsapp-gateway-setup)
5. [Cron Job Configuration](#cron-job-configuration)
6. [Security Hardening](#security-hardening)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)
9. [Rollback Plan](#rollback-plan)

---

## Prerequisites

### Server Requirements

**Minimum Specifications:**
- **CPU:** 2 cores @ 2.0 GHz
- **RAM:** 4 GB
- **Storage:** 20 GB SSD (+ ~100MB/month for photos)
- **Internet:** 10 Mbps with static IP (recommended)
- **OS:** Ubuntu 20.04+ / CentOS 8+ / Debian 11+ / Windows Server 2019+

**Recommended Specifications:**
- **CPU:** 4 cores @ 2.5 GHz
- **RAM:** 8 GB
- **Storage:** 50 GB SSD
- **Internet:** 50 Mbps with static IP
- **OS:** Ubuntu 22.04 LTS

### Software Requirements

**Must Have:**
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.3+
- Node.js 18+ and npm 8+
- Web Server (Nginx or Apache)
- Git

**Optional:**
- PM2 (for Node.js process management)
- Redis (for queue/cache - future use)
- Supervisor (for Laravel queue worker - future use)

---

## Server Setup

### 1. Update System

**Ubuntu/Debian:**
```bash
sudo apt update && sudo apt upgrade -y
```

**CentOS:**
```bash
sudo yum update -y
```

**Windows Server:**
- Use Windows Update

### 2. Install PHP 8.2+

**Ubuntu/Debian:**
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
  php8.2-curl php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath
```

**Verify:**
```bash
php -v
# Should show PHP 8.2.x
```

### 3. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

**Verify:**
```bash
composer --version
```

### 4. Install MySQL

**Ubuntu/Debian:**
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

**Create Database:**
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE absensi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON absensi_db.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Install Node.js & npm

**Ubuntu/Debian:**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

**Verify:**
```bash
node -v  # Should show v18.x
npm -v   # Should show 8.x+
```

### 6. Install Web Server

#### Option A: Nginx (Recommended)

```bash
sudo apt install -y nginx
```

**Create Nginx Config:**
```bash
sudo nano /etc/nginx/sites-available/absensi
```

**Paste this config:**
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/absensi/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Increase upload size for Excel import
    client_max_body_size 10M;
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/absensi /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Option B: Apache

```bash
sudo apt install -y apache2 libapache2-mod-php8.2
sudo a2enmod rewrite
```

**Create Apache Config:**
```bash
sudo nano /etc/apache2/sites-available/absensi.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    ServerAdmin webmaster@your-domain.com
    DocumentRoot /var/www/absensi/public

    <Directory /var/www/absensi/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/absensi-error.log
    CustomLog ${APACHE_LOG_DIR}/absensi-access.log combined

    # Increase upload size
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</VirtualHost>
```

**Enable site:**
```bash
sudo a2ensite absensi.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

---

## Application Deployment

### 1. Clone Repository

**Create directory:**
```bash
sudo mkdir -p /var/www
cd /var/www
```

**Clone from Git:**
```bash
sudo git clone <repository-url> absensi
cd absensi
```

**Or upload via FTP/SCP:**
```bash
scp -r /local/path/absensi user@server:/var/www/
```

### 2. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/absensi
sudo chmod -R 775 /var/www/absensi/storage
sudo chmod -R 775 /var/www/absensi/bootstrap/cache
```

**Windows:**
- Give IIS_IUSRS full control on storage/ and bootstrap/cache/

### 3. Install Dependencies

```bash
cd /var/www/absensi
composer install --optimize-autoloader --no-dev
```

### 4. Configure Environment

**Copy .env file:**
```bash
cp .env.example .env
nano .env
```

**Update these values:**
```env
# Application
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_KEY=  # Will generate in next step
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_db
DB_USERNAME=absensi_user
DB_PASSWORD=strong_password_here

# WhatsApp Gateway
WHATSAPP_GATEWAY_URL=http://localhost:3001

# Session & Cache
SESSION_DRIVER=file
CACHE_DRIVER=file

# Queue (future use)
QUEUE_CONNECTION=sync
```

**Generate Application Key:**
```bash
php artisan key:generate
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --force
php artisan db:seed --class=AttendanceSettingsSeeder
```

**Optional: Seed sample data (for testing):**
```bash
php artisan db:seed --class=AttendanceClassSeeder
php artisan db:seed --class=AttendanceStudentSeeder
```

### 6. Setup Storage

```bash
php artisan storage:link
```

**Create attendance directories:**
```bash
mkdir -p storage/app/attendance/qrcodes
mkdir -p storage/app/attendance/photos
mkdir -p storage/app/exports
mkdir -p storage/app/templates
chmod -R 775 storage/app/attendance
```

### 7. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Note:** After any config change, clear cache:
```bash
php artisan config:clear
```

### 8. Generate QR Codes

**If you imported students:**
```bash
php artisan attendance:generate-qr --all
```

**Check progress:**
```bash
php artisan attendance:generate-qr --missing
```

---

## WhatsApp Gateway Setup

### 1. Install Dependencies

```bash
cd /var/www/absensi/whatsapp-server-absensi
npm install --production
```

### 2. Configure Environment

```bash
cp .env.example .env
nano .env
```

**Update:**
```env
PORT=3001
SESSION_NAME=absensi-wa-session
NODE_ENV=production
```

### 3. Install PM2 (Process Manager)

```bash
sudo npm install -g pm2
```

### 4. Start Gateway with PM2

**Create PM2 ecosystem file:**
```bash
nano ecosystem.config.js
```

```javascript
module.exports = {
  apps: [{
    name: 'wa-absensi',
    script: 'server.js',
    cwd: '/var/www/absensi/whatsapp-server-absensi',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '500M',
    env: {
      NODE_ENV: 'production',
      PORT: 3001
    },
    error_file: './logs/error.log',
    out_file: './logs/output.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z'
  }]
};
```

**Start with PM2:**
```bash
pm2 start ecosystem.config.js
pm2 save
pm2 startup  # Follow instructions to enable on boot
```

**Check status:**
```bash
pm2 status
pm2 logs wa-absensi
```

### 5. Authenticate WhatsApp

**Option 1: Terminal QR (if you have GUI terminal)**
```bash
pm2 logs wa-absensi
# QR Code will appear in logs
```

**Option 2: Browser QR (recommended)**
1. Open browser: `http://your-server-ip:3001/qr`
2. QR Code will display
3. Scan with WhatsApp:
   - Open WhatsApp
   - Menu (⋮) > "Linked Devices"
   - "Link a Device"
   - Scan QR Code

**Important:** Use a dedicated WhatsApp number for the system, not personal!

### 6. Test Gateway

**Check status:**
```bash
curl http://localhost:3001/status
```

**Send test message:**
```bash
curl -X POST http://localhost:3001/send \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "628123456789",
    "message": "Test dari Sistem Absensi"
  }'
```

---

## Cron Job Configuration

### Ubuntu/Debian (Crontab)

**Edit crontab:**
```bash
sudo crontab -e
```

**Add this line:**
```cron
* * * * * cd /var/www/absensi && php artisan schedule:run >> /dev/null 2>&1
```

**Verify cron is running:**
```bash
sudo systemctl status cron
```

### CentOS (Cronie)

**Same as above, but verify cronie service:**
```bash
sudo systemctl status crond
```

### Windows Server (Task Scheduler)

See detailed guide: [`CRON_SETUP.md`](CRON_SETUP.md)

**Quick Steps:**
1. Open Task Scheduler
2. Create Basic Task: "Laravel Scheduler - Absensi"
3. Trigger: Daily, repeat every 1 minute
4. Action: Start a program
   - Program: `C:\path\to\php.exe`
   - Arguments: `artisan schedule:run`
   - Start in: `C:\path\to\absensi`
5. Save and enable

**Test manually:**
```bash
php artisan schedule:list
php artisan attendance:mark-absent
```

---

## Security Hardening

### 1. Setup SSL Certificate

**Using Let's Encrypt (Free):**

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

**Nginx config will auto-update to use HTTPS.**

**Test auto-renewal:**
```bash
sudo certbot renew --dry-run
```

### 2. Firewall Configuration

**Ubuntu (UFW):**
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

**CentOS (firewalld):**
```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

**Block direct access to WhatsApp Gateway port (3001):**
- Only allow localhost connections
- Use reverse proxy if external access needed

### 3. Secure MySQL

**Bind to localhost only:**
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
bind-address = 127.0.0.1
```

```bash
sudo systemctl restart mysql
```

### 4. Disable Directory Listing

**Nginx:** Already disabled in config  
**Apache:** Add to .htaccess:
```apache
Options -Indexes
```

### 5. Hide PHP Version

**Edit php.ini:**
```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
expose_php = Off
```

```bash
sudo systemctl restart php8.2-fpm
```

### 6. Rate Limiting (Recommended)

**Add to Laravel routes/api.php:**
```php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/attendance/scan', [AttendanceScanController::class, 'scan']);
    Route::post('/attendance/reject', [AttendanceScanController::class, 'reject']);
});
```

**Nginx rate limiting:**
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;

location /api/ {
    limit_req zone=api burst=5 nodelay;
    # ... rest of config
}
```

### 7. Backup Encryption

**Encrypt backups with GPG:**
```bash
gpg --symmetric --cipher-algo AES256 backup.sql
```

---

## Monitoring & Maintenance

### 1. Log Monitoring

**Laravel Logs:**
```bash
tail -f /var/www/absensi/storage/logs/laravel.log
```

**WhatsApp Gateway Logs:**
```bash
pm2 logs wa-absensi
```

**Nginx Access Logs:**
```bash
sudo tail -f /var/log/nginx/access.log
```

**Nginx Error Logs:**
```bash
sudo tail -f /var/log/nginx/error.log
```

### 2. Log Rotation

**Create logrotate config:**
```bash
sudo nano /etc/logrotate.d/absensi
```

```
/var/www/absensi/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

### 3. Database Backup

**Create backup script:**
```bash
sudo nano /usr/local/bin/backup-absensi.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/absensi"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u absensi_user -p'strong_password_here' absensi_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Photos backup
tar -czf $BACKUP_DIR/photos_$DATE.tar.gz /var/www/absensi/storage/app/attendance/photos

# WhatsApp session backup
tar -czf $BACKUP_DIR/wa_session_$DATE.tar.gz /var/www/absensi/whatsapp-server-absensi/absensi-wa-session

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

**Make executable:**
```bash
sudo chmod +x /usr/local/bin/backup-absensi.sh
```

**Schedule daily backup:**
```bash
sudo crontab -e
```

```cron
0 2 * * * /usr/local/bin/backup-absensi.sh >> /var/log/absensi-backup.log 2>&1
```

### 4. Disk Space Monitoring

**Check disk usage:**
```bash
df -h
du -sh /var/www/absensi/storage/app/attendance/photos
```

**Alert on low disk space:**
```bash
THRESHOLD=80
CURRENT=$(df / | grep / | awk '{ print $5 }' | sed 's/%//g')
if [ "$CURRENT" -gt "$THRESHOLD" ]; then
    echo "Disk usage is above $THRESHOLD%"
    # Send alert (email, etc.)
fi
```

### 5. Health Checks

**Create health check script:**
```bash
nano /usr/local/bin/check-absensi-health.sh
```

```bash
#!/bin/bash

# Check web server
curl -f http://localhost > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ERROR: Web server down"
    sudo systemctl restart nginx
fi

# Check WhatsApp Gateway
curl -f http://localhost:3001/status > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ERROR: WhatsApp Gateway down"
    pm2 restart wa-absensi
fi

# Check database
mysql -u absensi_user -p'password' -e "USE absensi_db; SELECT 1;" > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ERROR: Database connection failed"
fi
```

**Schedule every 5 minutes:**
```cron
*/5 * * * * /usr/local/bin/check-absensi-health.sh
```

---

## Troubleshooting

### Application Issues

**500 Internal Server Error:**
```bash
# Check Laravel logs
tail -100 /var/www/absensi/storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data /var/www/absensi/storage
sudo chmod -R 775 /var/www/absensi/storage

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**Database Connection Error:**
```bash
# Test connection
mysql -u absensi_user -p'password' absensi_db

# Check .env credentials
cat /var/www/absensi/.env | grep DB_

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

**QR Code Not Generating:**
```bash
# Check storage permissions
ls -la /var/www/absensi/storage/app/attendance/qrcodes

# Test manual generation
php artisan attendance:generate-qr --nis=24001

# Check logs
tail -100 /var/www/absensi/storage/logs/laravel.log
```

### WhatsApp Gateway Issues

**Gateway Not Starting:**
```bash
# Check PM2 logs
pm2 logs wa-absensi --lines 100

# Check Node.js version
node -v  # Must be 18+

# Reinstall dependencies
cd /var/www/absensi/whatsapp-server-absensi
rm -rf node_modules package-lock.json
npm install

# Restart
pm2 restart wa-absensi
```

**Messages Not Sending:**
```bash
# Check gateway status
curl http://localhost:3001/status

# Check if authenticated
# Visit http://localhost:3001/qr and rescan if needed

# Test send
curl -X POST http://localhost:3001/send \
  -H "Content-Type: application/json" \
  -d '{"phone":"628123456789","message":"Test"}'

# Check logs
pm2 logs wa-absensi
```

### Performance Issues

**Slow Response Time:**
```bash
# Enable OPcache
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

```bash
sudo systemctl restart php8.2-fpm
```

**High CPU Usage:**
```bash
# Check running processes
top
pm2 monit

# Increase PHP-FPM workers
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
```

---

## Rollback Plan

### Quick Rollback Steps

1. **Stop services:**
   ```bash
   sudo systemctl stop nginx
   pm2 stop wa-absensi
   ```

2. **Restore database backup:**
   ```bash
   gunzip < /var/backups/absensi/db_20260614_020000.sql.gz | \
     mysql -u absensi_user -p'password' absensi_db
   ```

3. **Restore application files:**
   ```bash
   cd /var/www
   sudo mv absensi absensi_broken
   sudo cp -r absensi_backup absensi
   ```

4. **Restore photos:**
   ```bash
   sudo tar -xzf /var/backups/absensi/photos_20260614_020000.tar.gz -C /
   ```

5. **Restart services:**
   ```bash
   sudo systemctl start nginx
   pm2 restart wa-absensi
   ```

---

## Post-Deployment Checklist

### Day 1

- [ ] All services running (web server, PHP-FPM, MySQL, PM2)
- [ ] SSL certificate active and auto-renewal configured
- [ ] WhatsApp Gateway authenticated and connected
- [ ] Cron job running (check logs)
- [ ] Test complete check-in flow
- [ ] Test WhatsApp notification
- [ ] Test Excel export
- [ ] Monitor logs for errors

### Week 1

- [ ] Monitor disk space daily
- [ ] Review error logs
- [ ] Verify backups are running
- [ ] Test auto alpha marking
- [ ] Collect user feedback
- [ ] Document any issues

### Month 1

- [ ] Review all backups
- [ ] Performance optimization (if needed)
- [ ] Security audit
- [ ] Update documentation based on feedback
- [ ] Plan Phase 2 features

---

## Support Contacts

**Technical Support:**
- Email: support@your-organization.com
- Phone: +62 xxx-xxxx-xxxx
- Hours: Mon-Fri 08:00-17:00 WIB

**Emergency Contacts:**
- On-call DevOps: +62 xxx-xxxx-xxxx (24/7)

---

**Document Version:** 1.0  
**Last Updated:** 2026-06-14  
**Maintained By:** [Your Organization] IT Team
