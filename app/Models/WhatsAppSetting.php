<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WhatsAppSetting extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'wa_setting_';

    /**
     * Cache duration (in seconds)
     */
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Scope untuk filter berdasarkan group
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope untuk setting public
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get setting value by key (with cache)
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_DURATION,
            function () use ($key, $default) {
                $setting = self::where('key', $key)->first();
                
                if (!$setting) {
                    return $default;
                }

                return self::castValue($setting->value, $setting->type);
            }
        );
    }

    /**
     * Set setting value by key
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        // Convert value to string for storage
        $stringValue = is_array($value) || is_object($value) 
            ? json_encode($value) 
            : (string) $value;

        $setting->update(['value' => $stringValue]);

        // Clear cache
        Cache::forget(self::CACHE_PREFIX . $key);

        return true;
    }

    /**
     * Cast value based on type
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, $type)
    {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Get all settings by group
     * 
     * @param string $group
     * @return array
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'group_' . $group,
            self::CACHE_DURATION,
            function () use ($group) {
                $settings = self::where('group', $group)->get();
                
                $result = [];
                foreach ($settings as $setting) {
                    $result[$setting->key] = self::castValue($setting->value, $setting->type);
                }
                
                return $result;
            }
        );
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache()
    {
        Cache::flush();
    }

    /**
     * Get WhatsApp server URL
     */
    public static function getServerUrl(): string
    {
        return self::get('wa_server_url', 'http://localhost:3000');
    }

    /**
     * Check if auto send is enabled
     */
    public static function isAutoSendEnabled(): bool
    {
        return self::get('wa_auto_send_enabled', false);
    }

    /**
     * Get connection timeout
     */
    public static function getTimeout(): int
    {
        return self::get('wa_timeout', 10);
    }

    /**
     * Get retry attempts
     */
    public static function getRetryAttempts(): int
    {
        return self::get('wa_retry_attempts', 3);
    }

    /**
     * Get rate limit
     */
    public static function getRateLimit(): int
    {
        return self::get('wa_rate_limit', 20);
    }

    /**
     * Get log retention days
     */
    public static function getLogRetentionDays(): int
    {
        return self::get('wa_log_retention_days', 90);
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValueAttribute()
    {
        $value = self::castValue($this->value, $this->type);

        return match($this->type) {
            'boolean' => $value ? 'Yes' : 'No',
            'json', 'array' => json_encode($value, JSON_PRETTY_PRINT),
            default => $value,
        };
    }

    /**
     * Get group badge color
     */
    public function getGroupColorAttribute()
    {
        return match($this->group) {
            'general' => 'primary',
            'connection' => 'info',
            'notification' => 'warning',
            'advanced' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get group label
     */
    public function getGroupLabelAttribute()
    {
        return match($this->group) {
            'general' => 'Umum',
            'connection' => 'Koneksi',
            'notification' => 'Notifikasi',
            'advanced' => 'Lanjutan',
            'rate_limiting' => 'Rate Limiting',
            default => ucfirst($this->group),
        };
    }

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
}
