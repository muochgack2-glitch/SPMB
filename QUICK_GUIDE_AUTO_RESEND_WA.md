# 🚀 QUICK GUIDE: Auto Re-send WhatsApp

## 🎯 **KASUS:**
Petugas input nomor HP **NGASAL** → Edit ke nomor **BENAR** → WA harus terkirim ke nomor **BENAR**

---

## ✅ **SOLUSI:**

### **Before:**
```
Kirim WA HANYA jika: NULL → 08111111111 (nomor baru ditambahkan)
```

### **After:**
```
Kirim WA jika:
1. NULL → 08111111111 (nomor baru ditambahkan) ✅
2. 08111111111 → 08222222222 (nomor berubah) ✅
3. 08111111111 → 08111111111 (nomor sama) ❌ TIDAK KIRIM
```

---

## 📊 **LOGIC TABLE:**

| Old Number | New Number | `wa_welcome_sent` | Kirim WA? | Keterangan |
|-----------|-----------|-------------------|-----------|-----------|
| NULL | 08111111111 | false | ✅ | Nomor baru ditambahkan |
| 08111111111 | 08222222222 | false | ✅ | WA belum pernah terkirim, nomor berubah |
| 08111111111 | 08222222222 | true | ✅ | **KASUS INI!** Nomor berubah (ngasal → benar) |
| 08111111111 | 08111111111 | true | ❌ | Nomor tidak berubah |

---

## 🔧 **IMPLEMENTASI:**

### **1. Migration (Tambah Kolom Tracking)**
```php
$table->boolean('wa_welcome_sent')->default(false);
$table->timestamp('wa_welcome_sent_at')->nullable();
$table->string('wa_welcome_sent_to', 20)->nullable();
$table->enum('wa_welcome_recipient_type', ['siswa', 'ortu', 'wali'])->nullable();
```

### **2. Logic di Controller**
```php
// LOGIC: Kirim jika nomor berubah ATAU belum pernah kirim
if (!empty($newNoTelepon) && 
    (!$pendaftar->wa_welcome_sent || $oldNoTelepon !== $newNoTelepon)) {
    
    // Kirim WA
    kirimWA($newNoTelepon);
    
    // Update tracking
    $pendaftar->update([
        'wa_welcome_sent' => true,
        'wa_welcome_sent_at' => now(),
        'wa_welcome_sent_to' => $newNoTelepon,
        'wa_welcome_recipient_type' => 'siswa',
    ]);
}
```

---

## 🧪 **TESTING:**

### **Test Case 1: Nomor Ngasal → Nomor Benar**
```
1. CREATE siswa dengan HP: 08123456789 (NGASAL)
   → WA terkirim ke 08123456789
   → DB: wa_welcome_sent = true

2. EDIT siswa, ganti HP: 08123456789 → 08987654321 (BENAR)
   → Cek: 08123456789 !== 08987654321
   → Result: ✅ WA terkirim ke 08987654321
```

### **Test Case 2: Nomor Tidak Berubah**
```
1. CREATE siswa dengan HP: 08111111111
   → WA terkirim ke 08111111111
   → DB: wa_welcome_sent = true

2. EDIT siswa (update alamat), HP tetap: 08111111111
   → Cek: 08111111111 === 08111111111
   → Result: ❌ WA TIDAK terkirim
```

---

## 🎯 **KEUNTUNGAN:**

✅ **Auto Re-send** jika nomor berubah (ngasal → benar)  
✅ **No Spam** jika nomor tidak berubah  
✅ **Tracking** kapan & ke mana WA terkirim  
✅ **Cascade Priority** siswa → ortu → wali  
✅ **Clear Logic** mudah dipahami & maintain  

---

## 🚀 **INSTALL:**

```bash
# 1. Run migration
php artisan migrate

# 2. Test
- Buat siswa baru dengan nomor ngasal
- Edit siswa, ganti nomor ke nomor benar
- Cek WhatsApp terkirim ke nomor benar
```

---

## 📝 **SUMMARY:**

| Before | After |
|--------|-------|
| Kirim hanya jika: **NULL → isi** | Kirim jika: **NULL → isi** ATAU **nomor berubah** |
| ❌ Tidak bisa auto re-send | ✅ **Bisa auto re-send** jika nomor berubah |
| ❌ Tidak ada tracking | ✅ **Ada tracking** di database |

**✅ Problem Solved: Petugas ngasal input nomor → Edit ke nomor benar → WA otomatis terkirim ke nomor benar!**
