# 🎨 Landing Page Enhancement Tasks

Daftar enhancement untuk meningkatkan user experience pada Public Landing Page (QR Scanner).

---

## ✅ COMPLETED

### 1. ✨ Toast Notifications dengan Sound
**Status:** ✅ DONE (Commit: 08d9d1b)

**Fitur:**
- Toast notification container dengan gradient design
- Web Audio API untuk notification sound (beep)
- Auto-dismiss setelah 5 detik
- Manual dismiss dengan tombol close
- 3 varian: success (hijau), warning (kuning), info (biru)
- Slide-in animation dari kanan
- Bounce animation untuk icon

**Files Modified:**
- `resources/views/welcome.blade.php`
- `public/sounds/notification.txt`

**Functions:**
- `playNotificationSound()` - Generate beep sound
- `showToast(title, message, type, duration)` - Show toast
- `dismissToast(button)` - Close toast
- `showNotification(scanData)` - Enhanced untuk SSE events

**Testing:**
```javascript
// Test toast manually di browser console
showToast('Test Notification', 'This is a test message', 'success', 5000);
```

---

## 📋 TODO - PENDING TASKS

### 2. 🔔 Badge Notification Count
**Status:** ⏳ TODO
**Priority:** HIGH
**Estimated Time:** 2 hours

**Deskripsi:**
Tampilkan counter notifikasi yang belum dibaca di:
- Tab browser title (Absensi (3))
- Recent Scans header badge
- Blink animation untuk attention

**Fitur yang Dibutuhkan:**
- Counter untuk new scans sejak user terakhir fokus ke tab
- Update tab title dengan count
- Visual badge di Recent Scans section
- Blinking effect untuk new notifications
- Reset count ketika user scroll Recent Scans

**Implementation Plan:**
```javascript
// Variables
let unreadScanCount = 0;
let originalTitle = document.title;

// Update badge
function updateBadgeCount() {
    unreadScanCount++;
    
    // Update tab title
    document.title = `(${unreadScanCount}) ${originalTitle}`;
    
    // Blink effect
    blinkTabTitle();
    
    // Update visual badge
    updateVisualBadge(unreadScanCount);
}

// Reset on focus
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        resetBadgeCount();
    }
});
```

**Files to Modify:**
- `resources/views/welcome.blade.php` (JavaScript section)
- `resources/views/welcome.blade.php` (Add badge HTML)

**Acceptance Criteria:**
- [ ] Tab title shows (N) when new scans arrive
- [ ] Badge appears on Recent Scans header
- [ ] Blinking animation for 5 seconds
- [ ] Auto-reset when tab gains focus
- [ ] Counter persists during session

---

### 3. 🎨 Animasi Saat New Scan Masuk
**Status:** ⏳ TODO
**Priority:** MEDIUM
**Estimated Time:** 3 hours

**Deskripsi:**
Tambahkan animasi smooth ketika scan baru ditambahkan ke Recent Scans timeline.

**Animasi yang Dibutuhkan:**
1. **Slide In dari Atas** - Item baru slide masuk dari atas
2. **Highlight Flash** - Background flash kuning lalu fade
3. **Scale Animation** - Zoom from 0.9 to 1.0
4. **Fade Out Old Items** - Item ke-11 fade out saat dihapus
5. **Confetti Effect** (optional) - Untuk status "hadir"

**Implementation Plan:**
```css
/* CSS Animations */
@keyframes slideInTop {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes highlightFlash {
    0% { background-color: #fef3c7; }
    100% { background-color: transparent; }
}

@keyframes scaleIn {
    from { transform: scale(0.9); }
    to { transform: scale(1); }
}

.scan-item-new {
    animation: 
        slideInTop 0.5s ease-out,
        highlightFlash 2s ease-out,
        scaleIn 0.3s ease-out;
}
```

```javascript
// Add animation class to new items
function addToRecentScans(scanData) {
    const newItem = createScanElement(scanData);
    newItem.classList.add('scan-item-new');
    
    // Insert at top
    timeline.insertBefore(newItem, timeline.firstChild);
    
    // Remove animation class after completion
    setTimeout(() => {
        newItem.classList.remove('scan-item-new');
    }, 2500);
}
```

**Files to Modify:**
- `resources/views/welcome.blade.php` (CSS section)
- `resources/views/welcome.blade.php` (JavaScript - addToRecentScans)

**Optional Libraries:**
- [Canvas Confetti](https://www.npmjs.com/package/canvas-confetti) untuk confetti effect

**Acceptance Criteria:**
- [ ] New item slides in smoothly from top
- [ ] Highlight flash untuk 2 detik
- [ ] Scale animation memberikan feedback visual
- [ ] Old items fade out gracefully
- [ ] No janky animation or layout shift
- [ ] Works on mobile devices

---

### 4. 📱 Mobile Push Notifications
**Status:** ⏳ TODO
**Priority:** LOW
**Estimated Time:** 8 hours

**Deskripsi:**
Implementasi Web Push Notifications menggunakan Service Worker dan Push API, sehingga admin/guru bisa terima notifikasi di HP bahkan saat browser tertutup.

**Requirements:**
1. Service Worker untuk handle push events
2. Push subscription management
3. Backend untuk send push notifications
4. Permission request UI
5. Notification payload dengan action buttons

**Technology Stack:**
- Web Push API (native browser)
- OR Firebase Cloud Messaging (FCM) (recommended)
- Laravel WebPush package untuk backend
- Service Worker untuk offline support

**Implementation Plan (FCM Approach):**

**A. Frontend Setup:**
```javascript
// Request permission
async function requestNotificationPermission() {
    const permission = await Notification.requestPermission();
    
    if (permission === 'granted') {
        // Get FCM token
        const token = await messaging.getToken();
        
        // Send token to backend
        await saveTokenToServer(token);
    }
}

// Handle foreground messages
messaging.onMessage((payload) => {
    showToast(payload.notification.title, payload.notification.body);
});
```

**B. Service Worker (`public/firebase-messaging-sw.js`):**
```javascript
importScripts('https://www.gstatic.com/firebasejs/10.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "...",
    projectId: "...",
    messagingSenderId: "...",
    appId: "..."
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    self.registration.showNotification(payload.notification.title, {
        body: payload.notification.body,
        icon: '/images/logo.png',
        badge: '/images/badge.png'
    });
});
```

**C. Backend Controller:**
```php
// app/Http/Controllers/PushNotificationController.php
public function sendNotification($userId, $title, $message) {
    $fcmTokens = User::find($userId)->fcm_tokens;
    
    foreach ($fcmTokens as $token) {
        FCM::sendTo($token, [
            'notification' => [
                'title' => $title,
                'body' => $message,
                'icon' => asset('images/logo.png')
            ]
        ]);
    }
}
```

**Files to Create:**
- `public/firebase-messaging-sw.js`
- `app/Http/Controllers/PushNotificationController.php`
- `app/Models/FcmToken.php`
- `database/migrations/xxxx_create_fcm_tokens_table.php`

**Files to Modify:**
- `resources/views/welcome.blade.php` (Add FCM initialization)
- `package.json` (Add Firebase SDK)
- `.env` (Add Firebase config)

**Acceptance Criteria:**
- [ ] User dapat opt-in untuk notifications
- [ ] Token disimpan ke database
- [ ] Notifikasi muncul di HP saat scan baru
- [ ] Notifikasi muncul meskipun browser tertutup
- [ ] Click notification membuka landing page
- [ ] Handle token refresh otomatis
- [ ] Unsubscribe functionality

**Note:** Fitur ini paling kompleks dan membutuhkan:
- Firebase project setup
- Database migration untuk token storage
- Testing di real mobile device
- SSL certificate (HTTPS required)

---

## 🎯 Priority Recommendations

**Untuk Development:**
1. ✅ Toast Notifications - **DONE**
2. 🔔 Badge Count - **Do This Next** (Quick win, high impact)
3. 🎨 Animations - **After Badge** (Polish UX)
4. 📱 Push Notifications - **Optional** (Complex, low priority untuk MVP)

**Rationale:**
- Badge Count mudah diimplementasikan dan memberikan value langsung
- Animations meningkatkan perceived performance
- Push Notifications butuh infrastructure tambahan (Firebase) dan testing kompleks

---

## 📝 Notes

- Semua fitur bersifat **progressive enhancement** - sistem tetap berfungsi tanpa fitur ini
- Testing harus dilakukan di real device untuk push notifications
- Pertimbangkan browser compatibility (terutama iOS Safari)
- Monitor performance impact dari animations

---

## 🚀 Future Enhancements (Beyond Scope)

- Sound selection (multiple notification sounds)
- Customizable toast duration per user
- Toast history/log
- Desktop notifications (Electron wrapper)
- WhatsApp notification integration
- Email digest untuk daily summary
- Dark mode toggle
- Accessibility improvements (screen reader support)

---

**Last Updated:** 2026-08-02
**Maintainer:** Development Team
