# 🌱 Database Seeder Guide - Sistem Absensi

## 📋 Overview

Seeder ini digunakan untuk mengisi database dengan data default yang diperlukan untuk menjalankan sistem absensi.

---

## 🚀 Cara Menggunakan Seeder

### **1. Run All Seeders (Recommended)**

Jalankan semua seeder sekaligus:

```bash

```

Atau dengan flag force untuk production:

```bash
php artisan db:seed --force
```

### **2. Run Specific Seeder**

Jalankan seeder tertentu saja:

```bash
# Seed admin users only
php artisan db:seed --class=AdminUserSeeder

# Seed attendance settings only
php artisan db:seed --class=AttendanceSettingsSeeder
```

### **3. Fresh Migration + Seed**

Untuk reset database dan seed ulang:

```bash
# ⚠️ WARNING: This will DROP all tables and recreate them!
php artisan migrate:fresh --seed
```

---

## 📦 Available Seeders

### **1. AdminUserSeeder**

**File:** `database/seeders/AdminUserSeeder.php`

**Fungsi:** Membuat user admin default untuk login sistem.

**Users yang dibuat:**

| Name | Email | Password | Role |
|------|-------|----------|------|
| Administrator | admin@smkpgriblora.sch.id | admin123 | Admin |
| Operator | operator@smkpgriblora.sch.id | operator123 | Operator |
| Petugas Scanner | petugas@smkpgriblora.sch.id | petugas123 | Petugas |

**Command:**
```bash
php artisan db:seed --class=AdminUserSeeder
```

**Output:**
```
✅ Admin user created successfully!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  📧 Email    : admin@smkpgriblora.sch.id
  🔑 Password : admin123
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️  IMPORTANT: Change password after first login!

✅ Operator user created successfully!
  📧 Email    : operator@smkpgriblora.sch.id
  🔑 Password : operator123

✅ Petugas Scanner user created successfully!
  📧 Email    : petugas@smkpgriblora.sch.id
  🔑 Password : petugas123
```

---

### **2. AttendanceSettingsSeeder**

**File:** `database/seeders/AttendanceSettingsSeeder.php`

**Fungsi:** Membuat konfigurasi default untuk sistem absensi.

**Settings yang dibuat:**

| Key | Default Value | Description |
|-----|--------------|-------------|
| school_name | SMK PGRI Blora | Nama sekolah |
| check_in_start | 06:00 | Waktu mulai check-in |
| check_in_end | 08:00 | Waktu akhir check-in |
| check_out_start | 14:00 | Waktu mulai check-out |
| check_out_end | 17:00 | Waktu akhir check-out |
| tolerance_minutes | 15 | Toleransi terlambat (menit) |
| cutoff_time | 10:00 | Batas waktu untuk mark alpha |
| whatsapp_enabled | true | Enable notifikasi WhatsApp |
| include_photo_in_notification | true | Include foto di notifikasi |

**Command:**
```bash
php artisan db:seed --class=AttendanceSettingsSeeder
```

---

## 🔐 Default Credentials

Setelah run seeder, gunakan credentials berikut untuk login:

### **Admin (Full Access)**
```
Email    : admin@smkpgriblora.sch.id
Password : admin123
```

### **Operator (Data Management)**
```
Email    : operator@smkpgriblora.sch.id
Password : operator123
```

### **Petugas Scanner (Scanner Only)**
```
Email    : petugas@smkpgriblora.sch.id
Password : petugas123
```

---

## ⚠️ SECURITY WARNING

**IMPORTANT:** Credentials default ini hanya untuk development dan testing.

**Di Production:**

1. ✅ **Ganti semua password** setelah login pertama kali
2. ✅ Gunakan password yang kuat (min 12 karakter)
3. ✅ Enable 2FA jika tersedia
4. ✅ Hapus atau disable user yang tidak digunakan

**Ganti Password via Profile:**
1. Login dengan credentials default
2. Klik menu **Profile**
3. Update password
4. Save

---

## 🛠️ Troubleshooting

### Error: "Class 'AdminUserSeeder' not found"

**Solusi:**
```bash
composer dump-autoload
php artisan db:seed --class=AdminUserSeeder
```

### Error: "User already exists"

Seeder ini aman dijalankan berkali-kali. Jika user sudah ada, akan skip otomatis.

**Output jika user sudah ada:**
```
⚠️  Admin user already exists. Skipping...
```

### Error: "SQLSTATE[23000]: Integrity constraint violation"

Ada data yang conflict. Reset database:

```bash
php artisan migrate:fresh --seed
```

### Error: "Nothing to seed"

Pastikan DatabaseSeeder sudah memanggil AdminUserSeeder:

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        AttendanceSettingsSeeder::class,
        AdminUserSeeder::class,
    ]);
}
```

---

## 📚 Additional Seeders (Optional)

### Create Sample Data for Testing

Jika ingin data sample untuk testing:

```bash
php artisan tinker
```

```php
// Create sample class
$class = App\Models\AttendanceClass::create([
    'name' => 'X RPL 1',
    'grade' => '10',
    'semester' => 'Ganjil',
    'academic_year' => '2026/2027',
    'is_active' => true,
]);

// Create sample students
for ($i = 1; $i <= 5; $i++) {
    App\Models\AttendanceStudent::create([
        'nis' => str_pad($i, 3, '0', STR_PAD_LEFT),
        'name' => "Siswa Test $i",
        'attendance_class_id' => $class->id,
        'gender' => $i % 2 == 0 ? 'female' : 'male',
        'parent_phone' => '08123456789' . $i,
        'is_active' => true,
    ]);
}

// Generate QR Codes
Artisan::call('attendance:generate-qr', ['--all' => true]);

echo "Sample data created!";
exit;
```

---

## 🚀 Production Deployment

### First Time Setup:

```bash
# 1. Run migrations
php artisan migrate --force

# 2. Run seeders
php artisan db:seed --force

# 3. Verify admin user created
php artisan tinker
>>> App\Models\User::where('email', 'admin@smkpgriblora.sch.id')->first()
>>> exit

# 4. Login and change password
# https://absensi.smkpgriblora.sch.id/login
```

---

## 📞 Support

**Need help?**
- Check documentation: `USER_MANUAL.md`
- Deployment guide: `DEPLOYMENT_SUCCESS_CHECKLIST.md`
- Troubleshooting: `FIX_TEMPNAM_ERROR_HOSTING.md`

---

**Document Version:** 1.0  
**Last Updated:** 2026-07-28  
**Status:** Ready to Use
