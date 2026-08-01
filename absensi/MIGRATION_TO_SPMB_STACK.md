# Migration Plan: Absensi → SPMB Tech Stack

## 🎯 Tujuan
Migrasi teknologi Absensi ke tech stack SPMB yang lebih stable dan mudah debug.

## 📊 Scope Pekerjaan

### Files yang Perlu Diubah:
- **Livewire Components:** 2 files
  - `app/Livewire/AttendanceDashboard.php`
  - `app/Livewire/QRScannerInterface.php`
- **Blade Views:** 6 files
  - `resources/views/attendance/dashboard/index.blade.php`
  - `resources/views/attendance/scanner.blade.php`
  - `resources/views/livewire/attendance-dashboard.blade.php`
  - `resources/views/livewire/qr-scanner-interface.blade.php`
  - `resources/views/layouts/app.blade.php`
  - `resources/views/components/⚡*.blade.php`

---

## 📝 Phase 1: Backup & Preparation (15 menit)

### 1.1 Backup Current State
```bash
cd c:\Users\DMCenter\Music\SPMB2\SPMB\absensi

# Buat branch baru untuk migrasi
git checkout -b migrate-to-spmb-stack

# Backup Livewire files
mkdir -p backup/livewire-backup
cp -r app/Livewire backup/livewire-backup/
cp -r resources/views/livewire backup/livewire-backup/views/
```

### 1.2 Document Current Features
- ✅ Auto-refresh dashboard setiap 30 detik
- ✅ Filter by date dan class
- ✅ QR Scanner dengan camera
- ✅ Real-time attendance tracking
- ✅ Photo preview modal

---

## 🔧 Phase 2: Update Dependencies (30 menit)

### 2.1 Update composer.json
```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1",
        "google/apiclient": "2.15.0",
        "maatwebsite/excel": "^3.1",
        "simplesoftwareio/simple-qrcode": "^4.2",
        "twilio/sdk": "^7.0"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/pail": "^1.2.2",
        "laravel/pint": "^1.13",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^11.5.3"
    }
}
```

**Perubahan:**
- ❌ Remove: `livewire/livewire`
- ⬇️ Downgrade: Laravel 13 → 12
- ⬇️ Downgrade: PHP 8.3 → 8.2
- ➕ Add: `twilio/sdk` untuk WhatsApp

### 2.2 Update package.json
```json
{
    "devDependencies": {
        "@tailwindcss/vite": "^4.0.0",
        "axios": "^1.7.4",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^1.2.0",
        "tailwindcss": "^4.0.0",
        "vite": "^6.0.11"
    },
    "dependencies": {
        "html5-qrcode": "^2.3.8"
    }
}
```

**Perubahan:**
- ⬇️ Downgrade: Vite 8 → 6
- ⬇️ Downgrade: Tailwind 3 → 4
- ➕ Keep: `html5-qrcode` (masih perlu untuk scanner)
- ❌ Remove: `apexcharts`, `flatpickr` (pakai vanilla JS)

### 2.3 Run Update
```bash
# Remove Livewire
composer remove livewire/livewire

# Update dependencies
composer update

# Update frontend
npm install
```

---

## 🎨 Phase 3: Convert Livewire → Traditional (2-3 jam)

### 3.1 AttendanceDashboard Migration

**Before (Livewire):**
```php
// app/Livewire/AttendanceDashboard.php
class AttendanceDashboard extends Component
{
    public $selectedDate;
    public $selectedClass;
    
    public function refresh() { ... }
}
```

**After (Controller):**
```php
// app/Http/Controllers/AttendanceDashboardController.php
class AttendanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->toDateString());
        $selectedClass = $request->get('class', 'all');
        
        // Logic tetap sama
        
        return view('attendance.dashboard.index', compact(
            'selectedDate',
            'selectedClass',
            'records',
            'stats'
        ));
    }
}
```

**View Migration:**
```blade
{{-- Before: wire:model.live --}}
<input wire:model.live="selectedDate" />

{{-- After: Traditional form --}}
<form method="GET" action="{{ route('attendance.dashboard') }}" id="filterForm">
    <input name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" />
</form>
```

### 3.2 QR Scanner Migration

**Before (Livewire):**
```php
// app/Livewire/QRScannerInterface.php
class QRScannerInterface extends Component
{
    public function scanQR($qrData) { ... }
}
```

**After (AJAX Controller):**
```php
// app/Http/Controllers/AttendanceQRController.php
class AttendanceQRController extends Controller
{
    public function scan(Request $request)
    {
        $qrData = $request->input('qr_data');
        
        // Validate dan process
        
        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'data' => $attendance
        ]);
    }
}
```

**View Migration:**
```javascript
// Before: wire:click
<button wire:click="scanQR('{{ $qr }}')">Scan</button>

// After: Axios/Fetch
<button onclick="scanQR('{{ $qr }}')">Scan</button>

<script>
async function scanQR(qrData) {
    const response = await fetch('/attendance/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ qr_data: qrData })
    });
    
    const result = await response.json();
    if (result.success) {
        alert(result.message);
        location.reload();
    }
}
</script>
```

---

## 🔄 Phase 4: Replace Auto-refresh (1 jam)

### 4.1 Dashboard Auto-refresh

**Before (Livewire):**
```blade
<div wire:poll.30s="refresh">
    {{-- Content --}}
</div>
```

**After (JavaScript):**
```javascript
// Simple auto-refresh
setInterval(function() {
    location.reload();
}, 30000); // 30 seconds

// Atau pakai AJAX untuk lebih smooth
setInterval(async function() {
    const response = await fetch(window.location.href);
    const html = await response.text();
    document.querySelector('#dashboard-content').innerHTML = 
        new DOMParser().parseFromString(html, 'text/html')
            .querySelector('#dashboard-content').innerHTML;
}, 30000);
```

---

## 📱 Phase 5: WhatsApp Gateway Migration (2 jam)

### 5.1 Install Twilio
```bash
composer require twilio/sdk
```

### 5.2 Update WhatsAppService
```php
// app/Services/AttendanceWhatsAppService.php
use Twilio\Rest\Client;

class AttendanceWhatsAppService
{
    private function getTwilioClient()
    {
        return new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }
    
    public function sendParentNotification(string $phone, string $message)
    {
        $client = $this->getTwilioClient();
        
        $message = $client->messages->create(
            "whatsapp:$phone",
            [
                'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
                'body' => $message
            ]
        );
        
        return [
            'success' => true,
            'sid' => $message->sid
        ];
    }
}
```

### 5.3 Config Twilio
```php
// config/services.php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_AUTH_TOKEN'),
    'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
],
```

### 5.4 Update .env
```env
TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=+14155238886
```

**Benefits:**
- ✅ No PM2 process management
- ✅ No port conflicts
- ✅ Reliable message delivery
- ✅ Official API support
- ❌ Trade-off: Biaya per pesan (~$0.005)

---

## 🧪 Phase 6: Testing (1 jam)

### 6.1 Test Checklist
- [ ] Dashboard bisa diakses tanpa error
- [ ] Filter date & class berfungsi
- [ ] Auto-refresh 30 detik berjalan
- [ ] QR Scanner bisa scan kode
- [ ] Check-in/Check-out tercatat
- [ ] WhatsApp notification terkirim
- [ ] No console errors
- [ ] No cache issues

### 6.2 Load Testing
```bash
# Test dengan ab (Apache Bench)
ab -n 100 -c 10 http://localhost/attendance/dashboard
```

---

## 🚀 Phase 7: Deployment (30 menit)

### 7.1 Production Deployment
```bash
# Di server production
cd /www/wwwroot/absensi/Absensi/absensi

# Pull changes
git pull origin migrate-to-spmb-stack

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Clear all caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
systemctl restart php-fpm-84
systemctl restart nginx

# Stop PM2 gateway (tidak perlu lagi)
pm2 delete whatsapp-gateway-absensi
pm2 save
```

---

## 📊 Expected Results

### Before (Absensi Current):
- ❌ Laravel 13 + Livewire 4 + Vite 8
- ❌ Complex caching layers
- ❌ Custom WhatsApp Gateway (PM2)
- ❌ Hard to debug
- ❌ Banyak bug

### After (SPMB Stack):
- ✅ Laravel 12 + Traditional MVC
- ✅ Simple caching
- ✅ Twilio WhatsApp (stable)
- ✅ Easy to debug
- ✅ Production-ready

---

## 💰 Cost Estimation

### Twilio WhatsApp Pricing:
- **Per message:** $0.005 (Rp 80)
- **100 messages/day:** $0.50/day = $15/month
- **500 messages/day:** $2.50/day = $75/month

### Alternative (Tetap Gratis):
Jika budget terbatas, bisa tetap pakai Baileys Gateway tapi:
- ✅ Simplify: Remove PM2, run as systemd service
- ✅ Better error handling
- ✅ Separate server process

---

## ⚠️ Risks & Mitigation

### Risks:
1. **Breaking changes** during migration
2. **Feature loss** (real-time updates)
3. **User adjustment** (page reload vs smooth update)

### Mitigation:
1. ✅ Keep backup branch
2. ✅ Test thoroughly di local
3. ✅ Deploy di staging dulu
4. ✅ Gradual rollout
5. ✅ Rollback plan ready

---

## 🎯 Timeline

| Phase | Duration | Status |
|-------|----------|--------|
| 1. Backup & Prep | 15 min | ⏳ Pending |
| 2. Update Dependencies | 30 min | ⏳ Pending |
| 3. Convert Livewire | 2-3 hours | ⏳ Pending |
| 4. Replace Auto-refresh | 1 hour | ⏳ Pending |
| 5. WhatsApp Migration | 2 hours | ⏳ Pending |
| 6. Testing | 1 hour | ⏳ Pending |
| 7. Deployment | 30 min | ⏳ Pending |
| **TOTAL** | **7-8 hours** | |

---

## 📝 Next Steps

Mau saya mulai dari Phase mana dulu?

1. **Phase 1-2** (Backup + Dependencies) - Aman, reversible
2. **Phase 3** (Convert Livewire) - Butuh testing intensif
3. **Phase 5** (Twilio Migration) - Alternative: Keep Baileys

Pilih opsi atau mau saya jalankan semua bertahap?
