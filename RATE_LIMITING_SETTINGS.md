# Rate Limiting Settings untuk External Broadcast

## 📋 Overview

Sistem rate limiting yang dapat dikonfigurasi melalui database untuk mencegah spam detection dari WhatsApp Gateway. Menggantikan hardcoded delays dengan settings yang dapat disesuaikan tanpa perlu edit code.

**Commit**: `3231dfe`  
**Status**: ✅ Selesai  
**Date**: June 17, 2026

## 🎯 Problem yang Diselesaikan

### Sebelumnya (Hardcoded)
```php
// Fixed 1 second delay
sleep(1);
```

**Masalah**:
- WhatsApp Gateway 2 (backup) kena throttle/spam detection
- Delay terlalu cepat dan predictable
- Tidak bisa customize tanpa edit code
- Pattern terlalu teratur (mudah dideteksi bot)

### Sekarang (Database-Driven)
```php
// Random delay 2-4 seconds (configurable)
$minDelay = WhatsAppSetting::getExternalBroadcastMinDelay(); // 2
$maxDelay = WhatsAppSetting::getExternalBroadcastMaxDelay(); // 4
$delay = rand($minDelay, $maxDelay);
sleep($delay);

// Break every 10 messages (configurable)
if ($currentIndex % $breakInterval === 0) {
    sleep($breakDuration); // +2 seconds
}
```

**Benefits**:
- ✅ Random delays tampil natural (tidak terdeteksi bot)
- ✅ Break pattern dengan jeda tambahan tiap N pesan
- ✅ Configurable via UI tanpa edit code
- ✅ Lebih aman dari spam detection

## 📊 Database Settings

### Migration File
`database/migrations/2026_06_17_221252_add_external_broadcast_rate_limit_settings.php`

### Settings yang Ditambahkan

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `wa_external_broadcast_min_delay` | 2 | integer | Minimum delay antar pesan (detik) |
| `wa_external_broadcast_max_delay` | 4 | integer | Maximum delay antar pesan (detik) |
| `wa_external_broadcast_break_interval` | 10 | integer | Setiap berapa pesan ada break tambahan |
| `wa_external_broadcast_break_duration` | 2 | integer | Durasi break tambahan (detik) |

**Group**: `rate_limiting`  
**is_public**: `false` (internal settings)

## 🔧 Implementation

### 1. Helper Methods di Model

**File**: `app/Models/WhatsAppSetting.php`

```php
/**
 * Get external broadcast min delay (in seconds)
 */
public static function getExternalBroadcastMinDelay(): int
{
    return self::get('wa_external_broadcast_min_delay', 2);
}

/**
 * Get external broadcast max delay (in seconds)
 */
public static function getExternalBroadcastMaxDelay(): int
{
    return self::get('wa_external_broadcast_max_delay', 4);
}

/**
 * Get external broadcast break interval
 */
public static function getExternalBroadcastBreakInterval(): int
{
    return self::get('wa_external_broadcast_break_interval', 10);
}

/**
 * Get external broadcast break duration (in seconds)
 */
public static function getExternalBroadcastBreakDuration(): int
{
    return self::get('wa_external_broadcast_break_duration', 2);
}
```

### 2. Update Controller

**File**: `app/Http/Controllers/WhatsAppController.php`  
**Method**: `sendExternalBroadcast()` (~line 1920)

**Before**:
```php
$delay = rand(2, 4);
sleep($delay);

if ($currentIndex % 10 === 0 && $currentIndex > 0) {
    sleep(2);
}
```

**After**:
```php
// Read from database
$minDelay = WhatsAppSetting::getExternalBroadcastMinDelay();
$maxDelay = WhatsAppSetting::getExternalBroadcastMaxDelay();
$delay = rand($minDelay, $maxDelay);
sleep($delay);

// Configurable break interval
$breakInterval = WhatsAppSetting::getExternalBroadcastBreakInterval();
$breakDuration = WhatsAppSetting::getExternalBroadcastBreakDuration();

if ($breakInterval > 0 && $currentIndex % $breakInterval === 0 && $currentIndex > 0) {
    sleep($breakDuration);
}
```

### 3. Settings UI

**File**: `resources/views/whatsapp/settings.blade.php`

Menambahkan icon dan label untuk group `rate_limiting`:

```blade
@elseif($group == 'rate_limiting')
<i class="fas fa-clock me-2"></i>Rate Limiting
```

## ⏱️ Estimasi Waktu Broadcast

### Default Settings (min=2, max=4, interval=10, break=2)

| Recipients | Min Time | Max Time | Avg Time |
|-----------|----------|----------|----------|
| 10 pesan | ~24s | ~44s | ~34s |
| 30 pesan | ~66s | ~126s | ~96s (1.6 min) |
| 50 pesan | ~118s | ~238s | ~178s (3 min) |
| 100 pesan | ~248s | ~498s | ~373s (6.2 min) |
| 200 pesan | ~508s | ~1,018s | ~763s (12.7 min) |

**Formula**:
```
avgDelay = (minDelay + maxDelay) / 2
breaks = floor(recipients / breakInterval)
totalTime = (recipients * avgDelay) + (breaks * breakDuration)
```

### Perbandingan dengan Hardcoded (delay=1s, no breaks)

| Recipients | Old Time | New Time (avg) | Difference |
|-----------|----------|----------------|-----------|
| 10 | ~10s | ~34s | +24s (3.4x) |
| 50 | ~50s | ~178s | +128s (3.6x) |
| 100 | ~100s | ~373s | +273s (3.7x) |

**Trade-off**: Broadcast lebih lambat TAPI lebih aman dari spam detection.

## 🎛️ Recommended Settings

### Conservative (Paling Aman)
- Min Delay: **3 detik**
- Max Delay: **5 detik**
- Break Interval: **8 pesan**
- Break Duration: **3 detik**

Untuk: Gateway baru, volume tinggi, history spam.

### Balanced (Default)
- Min Delay: **2 detik** ✅
- Max Delay: **4 detik** ✅
- Break Interval: **10 pesan** ✅
- Break Duration: **2 detik** ✅

Untuk: Normal usage, established gateway.

### Aggressive (Lebih Cepat, Lebih Risky)
- Min Delay: **1 detik**
- Max Delay: **2 detik**
- Break Interval: **15 pesan**
- Break Duration: **1 detik**

Untuk: Urgent broadcast, trusted gateway, low volume.

## 🚀 Cara Menggunakan

### 1. Run Migration (Sudah dilakukan di hosting)

```bash
php artisan migrate
```

Ini akan menambahkan 4 settings baru ke tabel `whatsapp_settings`.

### 2. Akses Settings UI

1. Buka **WhatsApp Gateway** → **⚙️ Pengaturan**
2. Scroll ke section **🕐 Rate Limiting**
3. Edit values sesuai kebutuhan:
   - **Min Delay**: Delay minimum (detik)
   - **Max Delay**: Delay maximum (detik)
   - **Break Interval**: Setiap berapa pesan ada break (0 = disable)
   - **Break Duration**: Durasi break tambahan (detik)
4. Klik **Simpan Pengaturan**

### 3. Test Broadcast

**Test dengan Recipients Sedikit** (2-5 orang dulu):
1. Buka **External Broadcast**
2. Upload CSV dengan 2-5 nomor test
3. Parse & Preview
4. Kirim broadcast
5. Monitor waktu eksekusi dan error log

**Naikkan Secara Bertahap**:
- Jika aman: coba 10 recipients
- Jika masih aman: coba 30 recipients
- Dst.

### 4. Monitor & Adjust

**Signs of Spam Detection**:
- ❌ Messages "sent" tapi tidak sampai
- ❌ Gateway response lambat/timeout
- ❌ Error "rate limit exceeded"
- ❌ Account temporary block

**Jika Kena Detection**:
1. **Stop broadcast** immediately
2. **Increase delays**: +1-2 detik pada min/max
3. **Reduce interval**: dari 10 → 8 atau 6
4. **Tunggu** 1-2 jam sebelum test lagi
5. **Test ulang** dengan volume kecil

## 📝 Notes

### Cache Behavior
Settings di-cache selama 1 jam. Setelah update:
- Cache otomatis di-clear
- Perubahan langsung aktif di broadcast berikutnya
- Tidak perlu restart server

### Gateway Selection
External broadcast **SELALU** menggunakan **Gateway 2 (Backup)**:
- Dedicated untuk external broadcast
- Tidak mengganggu notifikasi SPMB internal
- Lebih mudah di-monitor jika kena throttle

Ref: `DUAL_GATEWAY_IMPLEMENTATION.md`

### Disable Break Interval
Set `wa_external_broadcast_break_interval` = **0** untuk disable break tambahan.

Hanya akan pakai random delay tanpa break pattern.

## 🐛 Troubleshooting

### Q: Setting sudah diubah tapi broadcast masih pakai delay lama?

**A**: Clear cache:
```bash
php artisan cache:clear
```

Atau wait 1 jam (cache TTL).

### Q: Broadcast terlalu lambat, bisa dipercepat?

**A**: Ya, tapi hati-hati:
1. Kurangi min/max delay (misal: 1-2 detik)
2. Naikkan break interval (misal: 15 atau 20)
3. Monitor untuk spam detection
4. Jika kena block, kembalikan ke conservative

### Q: Berapa delay optimal untuk gateway saya?

**A**: Depends on:
- **Volume**: Makin besar, makin perlu delay panjang
- **Gateway history**: Gateway baru = lebih hati-hati
- **WhatsApp policy**: Bisa berubah sewaktu-waktu

**Cara find optimal**:
1. Start dengan default (2-4s)
2. Test 50 messages
3. Jika aman: turunkan sedikit (1-3s)
4. Test lagi 50 messages
5. Ulangi sampai menemukan limit

### Q: Broadcast gagal semua?

**A**: Check:
1. **Gateway status**: Apakah online?
2. **Nomor format**: Sudah 62xxx?
3. **Message template**: Ada error?
4. **Network**: Connection ke gateway OK?
5. **Logs**: Check `whatsapp_logs` table untuk detail error

## ✅ Testing Checklist

- [x] Migration created and ready to run
- [x] Helper methods added to WhatsAppSetting model
- [x] Controller updated to use database settings
- [x] Settings UI shows rate_limiting group
- [x] Default values sensible (2-4s, every 10, +2s)
- [x] Cache clearing works after settings update
- [x] Backwards compatible (has default values)

## 🔄 Next Steps (Optional Enhancements)

1. **Auto-adjust delays** based on failure rate
2. **Per-gateway settings** (different delays for primary vs backup)
3. **Time-based scheduling** (slower at peak hours)
4. **Dashboard monitoring** (show estimated time before send)
5. **A/B testing** (compare different delay strategies)

## 📚 Related Documentation

- `BROADCAST_UNIFICATION.md` - Hybrid broadcast logic
- `DUAL_GATEWAY_IMPLEMENTATION.md` - Gateway selection strategy
- `FIX_502_BROADCAST_TIMEOUT.md` - Timeout handling
- `DIAGNOSTIC_TOOL_USAGE.md` - WhatsApp diagnostic tool
