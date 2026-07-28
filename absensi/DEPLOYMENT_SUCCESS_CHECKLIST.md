# ✅ Deployment Success Checklist - Sistem Absensi

**Status:** 🎉 Laravel berhasil running di hosting!  
**URL:** https://absensi.smkpgriblora.sch.id  
**Date:** 2026-07-28

---

## ✅ LANGKAH YANG SUDAH SELESAI

- [x] 🚀 Deploy ke hosting
- [x] 🔧 Fix permission error (tempnam)
- [x] ✅ Laravel welcome page tampil
- [x] 📝 Route updated (redirect ke login/dashboard)

---

## 📋 LANGKAH SELANJUTNYA (WAJIB)

### **1. Setup Database** ⚠️ PENTING

#### Via aaPanel:

1. Login **aaPanel** → **Database**
2. **Create Database:**
   - Database Name: `absensi_db`
   - Username: `absensi_user`
   - Password: (generate strong password)
   - Access: `localhost` atau `%` (all hosts)
3. **Save credentials** untuk langkah berikutnya

#### Via SSH:

```bash
mysql -u root -p

CREATE DATABASE absensi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON absensi_db.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### **2. Update File .env** ⚠️ PENTING

Edit file `.env` di hosting:

```env
# Application
APP_NAME="Sistem Absensi"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://absensi.smkpgriblora.sch.id

# Database (GANTI DENGAN CREDENTIALS ANDA)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_db
DB_USERNAME=absensi_user
DB_PASSWORD=your_strong_password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Filesystem
FILESYSTEM_DISK=local

# Mail (optional, untuk email notification)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@smkpgriblora.sch.id"
MAIL_FROM_NAME="${APP_NAME}"

# WhatsApp Gateway (sesuaikan dengan setup Anda)
WHATSAPP_GATEWAY_URL=http://localhost:3001
WHATSAPP_GATEWAY_ENABLED=true
```

**⚠️ PENTING:**
- Set `APP_DEBUG=false` untuk production
- Generate `APP_KEY` jika belum ada

---

### **3. Generate APP_KEY** ⚠️ WAJIB

Via SSH:

```bash
cd /www/wwwroot/absensi/Absensi
php artisan key:generate --force
```

Via aaPanel Terminal atau SSH.

---

### **4. Run Database Migrations** ⚠️ WAJIB

```bash
# Masuk ke directory project
cd /www/wwwroot/absensi/Absensi

# Run migrations
php artisan migrate --force

# Seed default settings
php artisan db:seed --class=AttendanceSettingsSeeder --force
```

**Output yang diharapkan:**
```
Migration table created successfully.
Migrating: 2026_06_14_110238_create_attendance_classes_table
Migrated:  2026_06_14_110238_create_attendance_classes_table
Migrating: 2026_06_14_110239_create_attendance_students_table
Migrated:  2026_06_14_110239_create_attendance_students_table
...
```

---

### **5. Create Admin User** ⚠️ WAJIB

Via Tinker:

```bash
php artisan tinker
```

Lalu jalankan:

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@smkpgriblora.sch.id';
$user->password = bcrypt('password123');
$user->save();

exit;
```

**Credentials Login:**
- Email: `admin@smkpgriblora.sch.id`
- Password: `password123`

⚠️ **GANTI PASSWORD SETELAH LOGIN PERTAMA KALI!**

---

### **6. Setup Storage Link** ⚠️ WAJIB

```bash
php artisan storage:link
```

Ini membuat symbolic link dari `storage/app/public` ke `public/storage` untuk akses foto.

---

### **7. Clear & Optimize Cache**

```bash
# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### **8. Setup WhatsApp Gateway** (Optional tapi recommended)

#### Via aaPanel Terminal:

```bash
# Masuk ke folder whatsapp server
cd /www/wwwroot/absensi/Absensi/whatsapp-server-absensi

# Install dependencies
npm install

# Start with PM2
pm2 start server.js --name "absensi-wa-gateway"
pm2 save
pm2 startup
```

#### Authenticate WhatsApp:

1. Akses: `http://your-server-ip:3001/qr`
2. Scan QR Code dengan WhatsApp
3. Tunggu hingga status "Connected"

#### Update .env dengan gateway URL:

```env
WHATSAPP_GATEWAY_URL=http://localhost:3001
WHATSAPP_GATEWAY_ENABLED=true
```

---

### **9. Setup Cron Job untuk Auto Alpha** (Optional tapi recommended)

#### Via aaPanel:

1. Login **aaPanel** → **Cron**
2. **Add Task:**
   - Type: `Shell Script`
   - Name: `Auto Mark Alpha - Absensi`
   - Period: `Daily` at `16:00` (sesuaikan cutoff time)
   - Script:
     ```bash
     cd /www/wwwroot/absensi/Absensi && php artisan attendance:mark-absent
     ```
3. **Save**

#### Via Crontab:

```bash
crontab -e
```

Tambahkan:

```
# Auto mark absent students (every day at 4 PM)
0 16 * * * cd /www/wwwroot/absensi/Absensi && php artisan attendance:mark-absent >> /dev/null 2>&1

# Laravel Scheduler (setiap menit)
* * * * * cd /www/wwwroot/absensi/Absensi && php artisan schedule:run >> /dev/null 2>&1
```

---

### **10. Test Sistem**

#### Test 1: Login
1. Akses: `https://absensi.smkpgriblora.sch.id`
2. Klik **Login**
3. Email: `admin@smkpgriblora.sch.id`
4. Password: `password123`
5. ✅ Berhasil masuk ke dashboard

#### Test 2: Create Class
1. Menu **Classes** → **Add New Class**
2. Isi form:
   - Class Name: `X RPL 1`
   - Grade: `10`
   - Semester: `Ganjil`
   - Academic Year: `2026/2027`
3. **Save**
4. ✅ Class berhasil dibuat

#### Test 3: Create Student
1. Menu **Students** → **Add New Student**
2. Isi form:
   - NIS: `001`
   - Name: `Test Student`
   - Class: `X RPL 1`
   - Gender: `Male`
   - Parent Phone: `08123456789`
3. **Save**
4. ✅ Student & QR Code otomatis dibuat

#### Test 4: QR Scanner
1. Menu **Scanner**
2. Allow camera access
3. Scan QR Code student
4. ✅ Check-in berhasil dengan foto

#### Test 5: Dashboard
1. Menu **Dashboard**
2. ✅ Statistics tampil (Hadir: 1)
3. ✅ Photo preview tampil

#### Test 6: WhatsApp Notification (jika gateway sudah setup)
1. Menu **Settings**
2. Enable WhatsApp Notification
3. Test Notification dengan nomor HP Anda
4. ✅ Menerima pesan WhatsApp

---

## 🔒 SECURITY CHECKLIST

- [ ] **APP_DEBUG=false** di .env
- [ ] **APP_KEY generated** dan tidak default
- [ ] **Strong database password** (min 16 karakter)
- [ ] **Ganti admin password** dari default
- [ ] **HTTPS enabled** (sudah ada SSL)
- [ ] **Firewall rules** (block port 3001 dari outside jika gateway internal)
- [ ] **Backup database** regular
- [ ] **Update Laravel & dependencies** regular

---

## 📊 POST-DEPLOYMENT MONITORING

### **1. Check Logs**

```bash
# Laravel application log
tail -f /www/wwwroot/absensi/Absensi/storage/logs/laravel.log

# Nginx access log
tail -f /www/wwwroot/absensi/Absensi/logs/access.log

# Nginx error log
tail -f /www/wwwroot/absensi/Absensi/logs/error.log
```

### **2. Monitor Disk Space**

```bash
df -h
```

Pastikan storage cukup untuk foto absensi (estimasi 500KB per foto).

### **3. Monitor WhatsApp Gateway**

```bash
pm2 status
pm2 logs absensi-wa-gateway
```

---

## 🆘 TROUBLESHOOTING

### Error: "SQLSTATE[HY000] [1045] Access denied"
**Solusi:** Cek credentials database di `.env`

### Error: "419 Page Expired" saat login
**Solusi:** Clear cache dan regenerate key
```bash
php artisan cache:clear
php artisan key:generate --force
```

### Error: Photo tidak tampil di dashboard
**Solusi:** Pastikan storage link sudah dibuat
```bash
php artisan storage:link
```

### Error: WhatsApp notification tidak terkirim
**Solusi:** 
1. Cek gateway status: `http://localhost:3001/status`
2. Cek PM2 logs: `pm2 logs absensi-wa-gateway`
3. Re-authenticate WhatsApp: `http://localhost:3001/qr`

---

## 📞 SUPPORT

**Email:** support@smkpgriblora.sch.id  
**Phone:** +62 xxx-xxxx-xxxx  

**Documentation:**
- User Manual: `USER_MANUAL.md`
- Deployment Guide: `DEPLOYMENT_GUIDE.md`
- Troubleshooting: `FIX_TEMPNAM_ERROR_HOSTING.md`

---

## ✅ DEPLOYMENT COMPLETION STATUS

**Current Status:** 🟡 Laravel Running, Database Setup Needed

**Checklist:**
- [x] Application deployed
- [x] Permission fixed
- [x] Routes configured
- [ ] Database created
- [ ] Migrations run
- [ ] Admin user created
- [ ] Storage link created
- [ ] Cache optimized
- [ ] WhatsApp gateway setup
- [ ] Cron job configured
- [ ] System tested
- [ ] Production ready

---

**Next Action:** Setup Database & Run Migrations (Steps 1-4)

**Document Version:** 1.0  
**Last Updated:** 2026-07-28  
**Status:** In Progress
