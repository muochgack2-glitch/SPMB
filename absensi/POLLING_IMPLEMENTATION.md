# Implementasi Polling untuk Real-time Update

## Tujuan
Mengganti SSE (Server-Sent Events) dengan Polling yang lebih ringan dan stabil untuk update real-time di 3 halaman:
1. Landing Page (`/` - welcome.blade.php)
2. Scanner Page (`/scanner` - scanner.blade.php) 
3. Dashboard Admin (`/attendance/dashboard` - dashboard/index.blade.php)

## Status
✅ **Backend API sudah siap**
- Endpoint: `GET /api/attendance/live-data`
- Return: stats, records, absent_students
- Controller: `AttendanceStatsController@liveData`

⏳ **Frontend Polling - Belum implement**

---

## Implementasi Frontend

### 1. Landing Page (`resources/views/welcome.blade.php`)

**Fungsi yang sudah ada:**
- `loadTodayStats()` - Load statistik dari API

**Yang perlu ditambahkan:**
```javascript
// Polling interval ID untuk kontrol start/stop
let pollingInterval = null;

// Start polling saat page load
function startPolling() {
    // Initial load
    loadTodayStats();
    
    // Poll every 5 seconds
    pollingInterval = setInterval(() => {
        loadTodayStats();
        loadRecentScans(); // Update recent scans juga
    }, 5000); // 5 detik
    
    console.log('✅ Polling started (every 5s)');
}

// Stop polling (untuk cleanup)
function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
        console.log('⏹️ Polling stopped');
    }
}

// Pause polling saat tab tidak aktif (hemat resource)
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopPolling();
        console.log('👁️ Tab hidden - polling paused');
    } else {
        startPolling();
        console.log('👁️ Tab visible - polling resumed');
    }
});

// Start polling on page load
document.addEventListener('DOMContentLoaded', function() {
    startPolling();
});
```

**Lokasi penambahan:** 
Setelah fungsi `loadRecentScans()`, sebelum `</script>`

---

### 2. Scanner Page (`resources/views/attendance/scanner.blade.php`)

**Yang perlu ditambahkan:**
- Sama seperti landing page
- Poll `loadTodayStats()` setiap 5 detik
- Pause saat tab tidak aktif

---

### 3. Dashboard Admin (`resources/views/attendance/dashboard/index.blade.php`)

**Fungsi yang sudah ada:**
- `refreshDashboard()` - Reload halaman untuk update

**Yang perlu diubah:**
```javascript
// Replace current auto-refresh (30 detik reload halaman)
// Dengan polling yang update data tanpa reload

let pollingInterval = null;

function refreshDashboard() {
    fetch('{{ route("attendance.dashboard.refresh") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats
                updateStats(data.stats);
                // Update table
                updateAttendanceTable(data.records);
            }
        })
        .catch(error => console.error('Refresh error:', error));
}

function startPolling() {
    refreshDashboard(); // Initial load
    pollingInterval = setInterval(refreshDashboard, 5000); // 5 detik
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Pause polling saat tab tidak aktif
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopPolling();
    } else {
        startPolling();
    }
});

// Start on page load
startPolling();
```

---

## Perbandingan SSE vs Polling

| Aspek | SSE (Lama) | Polling (Baru) |
|-------|------------|----------------|
| **Koneksi** | Persistent (terus buka) | Short-lived (buka-tutup) |
| **Update** | Instant (0 detik) | Delay 5 detik |
| **Beban Server** | Tinggi (hold koneksi lama) | Rendah (cepat selesai) |
| **Stabilitas** | Tidak stabil banyak user | Stabil untuk banyak user |
| **Resource** | Boros (koneksi terus) | Hemat (polling saat tab aktif) |

---

## Keuntungan Polling

1. ✅ **Ringan** - Tidak ada koneksi persistent
2. ✅ **Stabil** - Cocok untuk banyak user
3. ✅ **Hemat** - Pause saat tab tidak aktif (Visibility API)
4. ✅ **Fleksibel** - Interval bisa disesuaikan
5. ✅ **Simple** - Tidak butuh server khusus

---

## Next Steps

1. ✅ Backend API ready
2. ⏳ Implement polling di `welcome.blade.php`
3. ⏳ Implement polling di `scanner.blade.php`
4. ⏳ Implement polling di `dashboard/index.blade.php`
5. ⏳ Test dengan multiple users/tabs
6. ⏳ Commit & push

---

## Testing Checklist

- [ ] Landing page: Stats update otomatis setiap 5 detik
- [ ] Landing page: Polling pause saat tab hidden
- [ ] Scanner page: Stats update otomatis
- [ ] Dashboard: Table update tanpa reload
- [ ] Multiple tabs: Semua polling independent
- [ ] 10+ tabs open: Server tetap cepat dan stabil
