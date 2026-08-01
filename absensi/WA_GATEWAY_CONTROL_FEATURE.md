# WhatsApp Gateway - Start/Stop Control via UI

## 📋 Overview

Fitur untuk mengendalikan WhatsApp Gateway server (start/stop) langsung dari UI dashboard menggunakan PM2 process manager.

**Status:** ✅ **COMPLETED** (with error handling improvement)

**Commit:** `f12911e - fix: improve gateway control error handling - PM2 not installed detection`

---

## 🎯 Features Implemented

### 1. **PM2 Process Control**
- Start gateway server dengan PM2
- Stop gateway server dengan PM2
- Auto-detect PM2 installation status
- Smart error handling untuk PM2 not installed

### 2. **Process Status Detection**
- Real-time check apakah PM2 installed
- Real-time check apakah gateway process running
- Show/hide Start/Stop button berdasarkan status
- Display process metrics (uptime, memory, CPU)

### 3. **User-Friendly UI**
- **PM2 Available:** Show Start/Stop button
- **PM2 Not Available:** Show warning dengan install instructions
- **Manual Alternative:** Provide manual start command jika PM2 tidak tersedia
- Confirmation dialogs sebelum start/stop

---

## 🔧 Technical Implementation

### Backend (Controller)

**File:** `app/Http/Controllers/WhatsAppController.php`

#### 3 New Methods:

1. **`startGateway()`**
   ```php
   // Check if gateway already running
   // Start with: pm2 start server.js --name whatsapp-gateway-absensi
   // Return success/error JSON
   ```

2. **`stopGateway()`**
   ```php
   // Stop with: pm2 stop whatsapp-gateway-absensi
   // Return success/error JSON
   ```

3. **`getGatewayProcessStatus()`** ⭐ **Error Handling Improved**
   ```php
   // Check PM2 installation first
   // Return JSON with PM2 availability status
   // If PM2 installed: return process status (running/stopped)
   // Always return JSON (never throw 500 error)
   ```

**Error Handling Improvements:**
- ✅ Check PM2 installation before checking process
- ✅ Return JSON with helpful error messages
- ✅ Never throw HTTP 500 (catch all exceptions)
- ✅ Provide install instructions in response

### Frontend (UI)

**File:** `resources/views/attendance/whatsapp/index.blade.php`

#### Gateway Control Section:

```html
<div x-data="{ processRunning: false, checking: true, pm2Available: true }">
  <!-- Auto-check PM2 status on page load -->
  
  <!-- Show loading while checking -->
  <template x-if="checking">
    <div>Checking PM2 status...</div>
  </template>
  
  <!-- Show warning if PM2 not installed -->
  <template x-if="!pm2Available">
    <div class="warning-box">
      PM2 not installed
      Install: npm install -g pm2
      Or manual: cd ../whatsapp-server-absensi && node server.js
    </div>
  </template>
  
  <!-- Show Start/Stop buttons if PM2 available -->
  <template x-if="pm2Available">
    <button x-show="!processRunning" @click="startGateway()">
      Start Gateway Server (PM2)
    </button>
    <button x-show="processRunning" @click="stopGateway()">
      Stop Gateway Server (PM2)
    </button>
  </template>
  
  <!-- Logout & Restart (always visible) -->
  <button @click="logout()">Logout & Reset QR</button>
  <button @click="restart()">Restart Gateway</button>
</div>
```

#### JavaScript Logic:

```javascript
// On page load
fetch('/whatsapp/gateway/process-status')
  .then(r => r.json())
  .then(data => {
    if (data.status === 'pm2_not_installed') {
      pm2Available = false;
      errorMsg = data.message;
    } else {
      processRunning = data.running || false;
    }
  });

// Start gateway
fetch('/whatsapp/gateway/start', { method: 'POST' })
  .then(r => r.json())
  .then(data => {
    alert(data.message);
    if (data.success) {
      processRunning = true;
      setTimeout(() => refreshStatus(), 5000);
    }
  });

// Stop gateway
fetch('/whatsapp/gateway/stop', { method: 'POST' })
  .then(r => r.json())
  .then(data => {
    alert(data.message);
    if (data.success) {
      processRunning = false;
      refreshStatus();
    }
  });
```

### Routes

**File:** `routes/web.php`

```php
Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    // ... existing routes ...
    
    // Gateway Control
    Route::post('/gateway/start', [WhatsAppController::class, 'startGateway'])
        ->name('gateway.start');
    Route::post('/gateway/stop', [WhatsAppController::class, 'stopGateway'])
        ->name('gateway.stop');
    Route::get('/gateway/process-status', [WhatsAppController::class, 'getGatewayProcessStatus'])
        ->name('gateway.process-status');
});
```

---

## 🐛 Bug Fixed

### Issue Encountered
User tested the feature and got **Error 500** on `/whatsapp/gateway/process-status`:
- Response was HTML (`<!DOCTYPE`) instead of JSON
- Root cause: **PM2 not installed** on user's system
- Controller was throwing exception, Laravel returning error page

### Fix Applied
1. **Better PM2 Detection:**
   - Check PM2 installation with `pm2 -v 2>&1`
   - Detect "command not found" in output
   - Return helpful error message instead of throwing exception

2. **Always Return JSON:**
   - Wrap all logic in try-catch
   - Never throw HTTP 500
   - Return JSON with status codes:
     - `pm2_not_installed` - PM2 not found
     - `no_processes` - PM2 installed but no processes
     - `not_found` - Gateway process not found
     - `online/stopped` - Gateway status
     - `error` - Unknown error

3. **UI Improvements:**
   - Show yellow warning box if PM2 not available
   - Display install command: `npm install -g pm2`
   - Show manual alternative: `cd ../whatsapp-server-absensi && node server.js`
   - Better loading states

---

## 📦 Installation Requirements

### For UI Start/Stop to Work:

**Option 1: Install PM2 (Recommended)**
```bash
npm install -g pm2
```

**Option 2: Manual Start (Without PM2)**
```bash
cd whatsapp-server-absensi
node server.js
```

---

## 🎨 UI Components

### Gateway Control Section

#### PM2 Available:
```
┌─────────────────────────────────────────┐
│ 🔧 Gateway Control                      │
├─────────────────────────────────────────┤
│ [▶️ Start Gateway Server (PM2)]         │  ← Green button (if stopped)
│                  OR                      │
│ [⏹️ Stop Gateway Server (PM2)]          │  ← Orange button (if running)
│                                          │
│ ─────────────────────────────────────── │
│                                          │
│ [🚪 Logout & Reset QR]                  │  ← Yellow button
│ [🔄 Restart Gateway]                    │  ← Red button
└─────────────────────────────────────────┘
```

#### PM2 Not Available:
```
┌─────────────────────────────────────────┐
│ 🔧 Gateway Control                      │
├─────────────────────────────────────────┤
│ ⚠️ PM2 not installed                   │
│                                          │
│ Install PM2:                             │
│ npm install -g pm2                       │
│                                          │
│ Or start manually:                       │
│ cd ../whatsapp-server-absensi &&         │
│ node server.js                           │
│                                          │
│ ─────────────────────────────────────── │
│                                          │
│ [🚪 Logout & Reset QR]                  │
│ [🔄 Restart Gateway]                    │
└─────────────────────────────────────────┘
```

---

## 🧪 Testing Guide

### Test Scenario 1: PM2 Not Installed
1. Navigate to WhatsApp Gateway dashboard
2. **Expected:** Yellow warning box appears
3. **Expected:** Start/Stop buttons hidden
4. **Expected:** Shows install instructions

### Test Scenario 2: PM2 Installed, Gateway Stopped
1. Install PM2: `npm install -g pm2`
2. Navigate to dashboard
3. **Expected:** Green "Start Gateway Server" button visible
4. Click Start button
5. **Expected:** Confirmation dialog
6. **Expected:** Success message
7. **Expected:** Button changes to orange "Stop"

### Test Scenario 3: PM2 Installed, Gateway Running
1. Start gateway manually: `cd whatsapp-server-absensi && pm2 start server.js --name whatsapp-gateway-absensi`
2. Navigate to dashboard
3. **Expected:** Orange "Stop Gateway Server" button visible
4. Click Stop button
5. **Expected:** Confirmation dialog
6. **Expected:** Success message
7. **Expected:** Button changes to green "Start"

### Test Scenario 4: Process Status Check
1. Open browser console (F12)
2. Refresh dashboard
3. **Expected:** AJAX call to `/whatsapp/gateway/process-status`
4. **Expected:** JSON response (never HTML error page)
5. **Expected:** Button state matches actual process status

---

## 🚀 How It Works

### Flow Diagram:

```
User Opens Dashboard
        ↓
    Fetch /gateway/process-status
        ↓
    ┌───────────────────┐
    │ PM2 Installed?    │
    └─────┬─────────┬───┘
         Yes       No
          ↓         ↓
    Check Process  Show Warning
          ↓         ↓
    ┌─────────┐   [Manual Instructions]
    │ Running?│
    └────┬────┘
      Yes│No
        ↓  ↓
    [Stop][Start]
        ↓
    Execute PM2 Command
        ↓
    Update UI State
```

### Commands Executed:

**Check PM2:**
```bash
pm2 -v 2>&1
```

**Check Process:**
```bash
pm2 jlist
```

**Start Gateway:**
```bash
cd /path/to/whatsapp-server-absensi
pm2 start server.js --name whatsapp-gateway-absensi
```

**Stop Gateway:**
```bash
pm2 stop whatsapp-gateway-absensi
```

---

## 📁 Files Modified

1. ✅ `app/Http/Controllers/WhatsAppController.php`
   - Added: `startGateway()`
   - Added: `stopGateway()`
   - Added: `getGatewayProcessStatus()` (with improved error handling)

2. ✅ `resources/views/attendance/whatsapp/index.blade.php`
   - Added: Gateway Control section with PM2 detection
   - Added: Start/Stop buttons with conditional rendering
   - Added: Warning box for PM2 not installed
   - Added: JavaScript logic for process control

3. ✅ `routes/web.php`
   - Added: `/whatsapp/gateway/start` (POST)
   - Added: `/whatsapp/gateway/stop` (POST)
   - Added: `/whatsapp/gateway/process-status` (GET)

---

## ✅ Checklist

- [x] Backend: `startGateway()` method
- [x] Backend: `stopGateway()` method
- [x] Backend: `getGatewayProcessStatus()` method
- [x] Backend: PM2 installation check
- [x] Backend: Error handling (always return JSON)
- [x] Frontend: Gateway Control UI section
- [x] Frontend: Start/Stop buttons
- [x] Frontend: PM2 not installed warning
- [x] Frontend: Process status detection on load
- [x] Frontend: Confirmation dialogs
- [x] Routes: Gateway control endpoints
- [x] **BUG FIX:** Handle PM2 not installed error
- [x] **BUG FIX:** Always return JSON response
- [x] **BUG FIX:** Show helpful instructions
- [x] Git commit & push to both repositories
- [x] Documentation created

---

## 🔗 Related Documentation

- `WA_GATEWAY_UPGRADE_SUMMARY.md` - Overall project summary
- `WA_GATEWAY_TASK1_COMPLETED.md` - Message Logs feature
- `WA_GATEWAY_TASK2_COMPLETED.md` - Settings Management feature
- `WA_GATEWAY_USER_GUIDE.md` - End-user guide (Indonesian)
- `TESTING_CHECKLIST.md` - Manual testing guide

---

## 👨‍💻 Developer Notes

### Why PM2?
- PM2 adalah standard process manager untuk Node.js
- Auto-restart jika crash
- Monitoring built-in (memory, CPU, uptime)
- Easy to manage multiple processes
- Production-ready

### Why Not Use Shell exec('node server.js')?
- No background process management
- Can't stop easily (need to find PID manually)
- No auto-restart
- No monitoring
- Not suitable for production

### Alternative Without PM2:
User dapat start gateway secara manual:
```bash
cd whatsapp-server-absensi
node server.js
```

Tapi tidak bisa start/stop dari UI, harus manual kill process.

---

## 📝 Changelog

### v1.1 (Latest - f12911e)
- **FIXED:** Error 500 saat PM2 not installed
- **ADDED:** PM2 installation check in `getGatewayProcessStatus()`
- **IMPROVED:** Always return JSON (never throw exception)
- **IMPROVED:** Show warning with install instructions if PM2 not available
- **IMPROVED:** Better error messages

### v1.0 (Initial)
- **ADDED:** Start/Stop gateway via UI
- **ADDED:** Process status detection
- **ADDED:** Confirmation dialogs
- **ADDED:** Gateway Control section in dashboard

---

**Last Updated:** August 1, 2026  
**Author:** Kiro AI Assistant  
**Project:** SPMB - Absensi System (SMK PGRI BLORA)
