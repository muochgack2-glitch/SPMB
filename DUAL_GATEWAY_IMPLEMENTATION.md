# 🔄 Dual Function Backup Gateway Implementation

## 📋 Overview

Backup Gateway sekarang memiliki **2 fungsi sekaligus**:

1. **DEDICATED untuk Broadcast Eksternal** - Selalu menggunakan backup gateway
2. **FAILOVER untuk Broadcast SPMB** - Otomatis pakai backup kalau primary down

---

## 🎯 Routing Logic

### Broadcast SPMB (Reguler)
```
┌─────────────────────────────────────┐
│ Primary Gateway Healthy?            │
├─────────────────────────────────────┤
│ ✅ YES → Use Primary Gateway        │
│ ❌ NO  → FAILOVER to Backup Gateway │
└─────────────────────────────────────┘
```

### Broadcast Eksternal
```
┌─────────────────────────────────────┐
│ ALWAYS → Use Backup Gateway         │
│ (Dedicated untuk traffic eksternal) │
└─────────────────────────────────────┘
```

---

## 💻 Implementation Details

### Modified File: `app/Services/WhatsAppService.php`

#### 1. Method `getActiveServerUrl(?string $context = null)`

**FUNCTION 1: Dedicated External Broadcast**
```php
if ($context === 'external_broadcast' && !empty($backup)) {
    Log::info('Using backup gateway for external broadcast (dedicated)');
    return $backup;  // ALWAYS use backup for external
}
```

**FUNCTION 2: SPMB Failover**
```php
// Check primary health
if ($this->checkServerHealth($primary)) {
    return $primary;  // Primary healthy → use primary
}

// Primary down → failover to backup
Log::warning('Primary gateway unhealthy, failover to backup (SPMB)');
return $backup;
```

#### 2. Method `send()`

Sekarang menerima context dari `$options['type']`:
```php
public function send(string $phone, string $message, array $options = []): array
{
    // Determine context from options
    $context = $options['type'] ?? null;
    
    // Get appropriate gateway based on context
    $serverUrl = $this->getActiveServerUrl($context);
    
    // ... send to selected gateway
}
```

---

## 🔧 Configuration

Di **WhatsApp Settings** (`whatsapp_settings` table):

| Key | Value | Description |
|-----|-------|-------------|
| `wa_server_url` | `http://localhost:3000` | Primary Gateway (untuk SPMB) |
| `wa_server_url_backup` | `http://backup-server:3000` | Backup Gateway (untuk Eksternal + Failover) |
| `wa_failover_enabled` | `true` | Aktifkan failover mechanism |
| `wa_failover_timeout` | `5` | Health check timeout (detik) |

---

## 📊 Gateway Usage Matrix

| Broadcast Type | Primary Health | Gateway Used | Reason |
|---------------|----------------|--------------|--------|
| **SPMB** | ✅ Healthy | Primary | Normal operation |
| **SPMB** | ❌ Down | Backup | Failover for high availability |
| **External** | ✅ Healthy | **Backup** | Dedicated routing (separasi traffic) |
| **External** | ❌ Down | **Backup** | Always use backup regardless |

---

## ✅ Benefits

### 1. **Traffic Separation**
- SPMB students (sensitive data) → Primary Gateway
- External contacts (alumni, marketing) → Backup Gateway
- Tidak saling ganggu/compete untuk resources

### 2. **Load Balancing**
- Backup gateway tidak idle, actively used untuk eksternal
- Primary fokus untuk SPMB core business

### 3. **High Availability**
- Failover tetap aktif untuk SPMB
- External broadcast tidak terpengaruh primary status

### 4. **Easy Monitoring**
- Log backup gateway = eksternal + SPMB failover
- Mudah tracking usage per type

---

## 🔍 Logging

### External Broadcast
```
[INFO] Using backup gateway for external broadcast (dedicated)
Gateway: http://backup-server:3000
Context: external_broadcast
```

### SPMB Failover
```
[WARNING] Primary gateway unhealthy, failover to backup (SPMB)
Primary: http://localhost:3000
Backup: http://backup-server:3000
Context: broadcast
```

### Normal SPMB (Primary Healthy)
```
[INFO] Attempting to send WhatsApp message
Server URL: http://localhost:3000
Context: broadcast
```

---

## 🧪 Testing Scenarios

### Scenario 1: External Broadcast (Primary Up)
```bash
Expected: Uses backup gateway
Log: "Using backup gateway for external broadcast (dedicated)"
```

### Scenario 2: External Broadcast (Primary Down)
```bash
Expected: Uses backup gateway (same result)
Log: "Using backup gateway for external broadcast (dedicated)"
```

### Scenario 3: SPMB Broadcast (Primary Up)
```bash
Expected: Uses primary gateway
Log: "Attempting to send WhatsApp message" (server_url = primary)
```

### Scenario 4: SPMB Broadcast (Primary Down)
```bash
Expected: Failover to backup gateway
Log: "Primary gateway unhealthy, failover to backup (SPMB)"
```

---

## 🚀 Deployment Checklist

- [ ] Setup backup gateway di server berbeda (recommended)
- [ ] Update `wa_server_url_backup` di WhatsApp Settings
- [ ] Set `wa_failover_enabled` = `true`
- [ ] Test external broadcast → verify using backup
- [ ] Test SPMB broadcast → verify using primary
- [ ] Stop primary → test SPMB failover → verify using backup
- [ ] Monitor logs di `storage/logs/laravel.log`

---

## 📝 Notes

- Context detection: automatic dari `$options['type']`
- Type `'external_broadcast'` → trigger dedicated routing
- Type lain (`'broadcast'`, `'manual'`, etc.) → use failover logic
- Failover timeout configurable via `wa_failover_timeout` setting
- No code changes needed in controllers - transparent routing

---

**Commit**: Implement dual function backup gateway for external broadcast + SPMB failover
**Date**: June 17, 2026
**Author**: Kiro AI Assistant
