# Testing Checklist - WhatsApp Gateway Upgrade

**Tanggal:** 01 Agustus 2026  
**Versi:** 1.0  
**Status:** Ready for Testing

---

## ✅ Pre-Test Verification (DONE)

- [x] Database migrations run successfully
- [x] Tables created: `whatsapp_messages`, `whatsapp_settings`
- [x] Default settings inserted (13 rows)
- [x] Routes registered (14 routes)
- [x] Models work correctly (type casting verified)
- [x] Assets built successfully
- [x] Cache cleared

---

## 🧪 Manual Testing Steps

### 1. Test Dashboard Access

**URL:** `http://localhost:8000/whatsapp` (atau port Anda)

**Expected:**
- ✅ Dashboard page loads without errors
- ✅ "WhatsApp Gateway" title visible
- ✅ Gateway status card shows "Disconnected" or "Connected"
- ✅ 3 buttons visible: Settings (gray), Message Logs (purple), Refresh (blue outline)
- ✅ Gateway status info cards visible (URL, Uptime, Memory)
- ✅ QR code section visible
- ✅ Quick send message form visible
- ✅ Dark mode toggle works

**Test Steps:**
1. Login ke sistem absensi
2. Klik menu "WA Gateway" di sidebar
3. Verify semua elemen muncul
4. Toggle dark mode (moon icon) - check appearance
5. Klik "Refresh" button - should update status

---

### 2. Test Message Logs Page

**URL:** `http://localhost:8000/whatsapp/logs`

**Expected:**
- ✅ Message Logs page loads
- ✅ 5 statistics cards visible (Total, Terkirim, Gagal, Pending, Hari Ini)
- ✅ All show "0" initially (no messages sent yet)
- ✅ Filter card with 5 fields visible
- ✅ Empty state message visible: "Tidak ada data"
- ✅ "Kembali ke Dashboard" button works

**Test Steps:**
1. From dashboard, click "Message Logs" button (purple)
2. Verify page layout
3. Try filters (should show empty results)
4. Click "Kembali ke Dashboard" - should return to dashboard
5. Test dark mode toggle

---

### 3. Test Settings Page

**URL:** `http://localhost:8000/whatsapp/settings`

**Expected:**
- ✅ Settings page loads
- ✅ 4 colored cards visible:
  - Blue: Koneksi Gateway
  - Yellow: Rate Limiting  
  - Green: Fitur Notifikasi
  - Purple: Template Pesan
- ✅ All fields populated with default values
- ✅ Toggle switches work (click to ON/OFF)
- ✅ "Simpan Pengaturan" and "Reset ke Default" buttons visible

**Test Steps:**
1. From dashboard, click "Settings" button (gray)
2. Verify all 4 sections visible
3. Check default values:
   - Gateway URL: `http://localhost:3001`
   - Timeout: 10
   - Retry Attempts: 3
   - Messages per Minute: 20
   - Delay: 3
   - All toggles: ON (blue/green)
4. Try toggling switches - should animate smoothly
5. Test dark mode

---

### 4. Test Settings Save

**Test Steps:**
1. Go to Settings page
2. Change "Timeout" from 10 to 15
3. Toggle OFF "Send on Check-Out"
4. Click "Simpan Pengaturan"
5. Expected: Green success message "Pengaturan berhasil disimpan!"
6. Refresh page - values should persist
7. Change back and verify

---

### 5. Test Settings Reset

**Test Steps:**
1. Go to Settings page
2. Change multiple values (e.g., timeout to 20, toggle some features)
3. Click "Simpan Pengaturan"
4. Click "Reset ke Default"
5. Expected: Confirmation dialog appears
6. Click OK
7. Expected: Success message
8. Check values - should be back to defaults

---

### 6. Test Message Sending (if gateway running)

**Prerequisites:** 
- WhatsApp Gateway server running on port 3001
- Gateway connected (scan QR code first)

**Test Steps:**
1. Go to Dashboard
2. Scroll to "Kirim Pesan Manual" section
3. Enter test phone number: `628123456789` (use your real number)
4. Enter message: "Test dari sistem absensi"
5. Click "Kirim Pesan"
6. Expected: Success toast notification
7. Check your phone - should receive WhatsApp message
8. Go to Message Logs - should see 1 new message with status "Terkirim"

---

### 7. Test Message Logs After Send

**Test Steps:**
1. After sending test message, go to Message Logs
2. Expected:
   - Total: 1
   - Terkirim: 1 (green)
   - Gagal: 0 (red)
   - Pending: 0 (yellow)
   - Hari Ini: 1 (purple)
3. Table should show 1 row with your message
4. Click "Detail" button
5. Expected: Modal opens with full message details
6. Close modal - should work with X button or backdrop click

---

### 8. Test Message Logs Filters

**Test Steps:**
1. Send a few more test messages (at least 3)
2. Go to Message Logs
3. Test Status filter:
   - Select "Terkirim" - should show only sent messages
   - Select "Gagal" - should show empty (if all succeeded)
4. Test Type filter:
   - Select "Manual" - should show your test messages
5. Test Date filter:
   - Set today's date - should show all
   - Set yesterday - should show empty
6. Test Search:
   - Enter your phone number - should find messages
   - Enter random text - should show empty
7. Click "Reset" button (circular arrow) - should clear all filters

---

### 9. Test Dark Mode Consistency

**Test Steps:**
1. Toggle dark mode ON (moon icon in sidebar)
2. Visit all 3 pages:
   - Dashboard
   - Message Logs
   - Settings
3. Expected: All pages should have dark theme
4. Check:
   - Background is dark
   - Text is light/white
   - Cards have dark background
   - Buttons maintain colors
   - Forms have dark inputs
5. Toggle back to light mode
6. Verify all pages return to light theme

---

### 10. Test Responsive Design

**Test Steps:**
1. Open browser DevTools (F12)
2. Toggle device toolbar (mobile view)
3. Test on different sizes:
   - Mobile: 375px width
   - Tablet: 768px width
   - Desktop: 1920px width
4. Check each page:
   - Dashboard: Cards stack on mobile
   - Message Logs: Table scrolls horizontally on mobile
   - Settings: Cards stack, toggle switches work
5. Verify no overlapping elements
6. Test sidebar hamburger menu on mobile

---

## 🚨 Error Scenarios to Test

### Scenario 1: Gateway Not Running

**Test Steps:**
1. Stop WhatsApp Gateway server (if running)
2. Go to Dashboard
3. Expected: Status shows "Disconnected" (red)
4. Try to send message
5. Expected: Error message or timeout
6. Check Message Logs - message should be marked as "Gagal"

---

### Scenario 2: Invalid Phone Number

**Test Steps:**
1. Try to send message to invalid number: `081234567890` (starts with 08)
2. Expected: Form validation or error message
3. Try without country code: `81234567890`
4. Expected: Error or auto-correction to 628xxx

---

### Scenario 3: Empty Message

**Test Steps:**
1. Enter phone number but leave message empty
2. Click "Kirim Pesan"
3. Expected: Validation error "Message is required"

---

### Scenario 4: Database Error Simulation

**Test Steps:**
1. Go to Settings
2. Try to save with invalid values:
   - Timeout: 0 (below minimum 5)
   - Messages per Minute: 100 (above maximum 60)
3. Expected: Validation errors shown below fields
4. Fix values and try again - should save successfully

---

## 🔍 Browser Console Checks

**Open Console (F12 → Console tab)**

### Expected NO Errors:
- ✅ No JavaScript errors
- ✅ No 404 errors for CSS/JS files
- ✅ No CSRF token errors
- ✅ No XHR/fetch errors

### Check Network Tab:
- ✅ CSS files load (app.css)
- ✅ JS files load (app.js)
- ✅ API calls return 200 OK
- ✅ No 500 server errors

---

## 📊 Database Verification

**After Testing, Check Database:**

```sql
-- Check messages table
SELECT COUNT(*) FROM whatsapp_messages;
-- Should have test messages

SELECT status, COUNT(*) 
FROM whatsapp_messages 
GROUP BY status;
-- Should show counts per status

-- Check settings table
SELECT COUNT(*) FROM whatsapp_settings;
-- Should be 13

SELECT `key`, `value` 
FROM whatsapp_settings 
WHERE `key` LIKE 'send_on%';
-- Should show feature toggles
```

---

## ✅ Success Criteria

All tests PASS if:
- [x] All pages load without errors
- [x] Database tables exist and populated
- [x] Settings can be saved and retrieved
- [x] Messages can be sent (if gateway running)
- [x] Message logs display correctly
- [x] Filters and search work
- [x] Dark mode works on all pages
- [x] Responsive on mobile/tablet/desktop
- [x] No console errors
- [x] Forms validate correctly
- [x] Success/error messages display

---

## 🐛 If You Find Bugs

**Report Format:**
1. **Page/Feature:** Where the bug occurred
2. **Steps to Reproduce:** What you did
3. **Expected:** What should happen
4. **Actual:** What actually happened
5. **Screenshot:** If visual bug
6. **Console Errors:** Copy from browser console

---

## 📝 Testing Log Template

```
Date: _____________
Tester: ___________
Browser: __________
OS: _______________

Test Results:
[ ] Dashboard Access
[ ] Message Logs Page
[ ] Settings Page
[ ] Settings Save
[ ] Settings Reset
[ ] Message Sending
[ ] Message Logs After Send
[ ] Filters
[ ] Dark Mode
[ ] Responsive Design

Issues Found: ________________________________________
__________________________________________________
__________________________________________________

Overall Status: [ ] PASS  [ ] FAIL  [ ] NEEDS FIX
```

---

## 🎯 Quick Test Commands

```bash
# Check database
php artisan tinker --execute="
    echo 'Messages: ' . \App\Models\WhatsAppMessage::count() . PHP_EOL;
    echo 'Settings: ' . \App\Models\WhatsAppSetting::count() . PHP_EOL;
"

# Check routes
php artisan route:list --path=whatsapp

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Test model
php artisan tinker --execute="
    var_dump(\App\Models\WhatsAppSetting::get('gateway_url'));
    var_dump(\App\Models\WhatsAppSetting::get('auto_send_enabled'));
"
```

---

**Ready to Test!** 🚀

Start with the manual testing steps above and report any issues found.
