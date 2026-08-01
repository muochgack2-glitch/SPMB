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
        'description',
    ];

    /**
     * Get setting value by key with automatic type casting
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Try to get from cache first
        $cacheKey = 'whatsapp_setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set setting value
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        // Convert value to string for storage
        $stringValue = self::valueToString($value, $setting->type);
        
        $setting->update(['value' => $stringValue]);
        
        // Clear cache
        Cache::forget('whatsapp_setting_' . $key);
        
        return true;
    }

    /**
     * Get all settings grouped by group
     *
     * @return array
     */
    public static function getAllGrouped(): array
    {
        $settings = self::orderBy('group')->orderBy('key')->get();
        
        $grouped = [];
        foreach ($settings as $setting) {
            $grouped[$setting->group][] = [
                'key' => $setting->key,
                'value' => self::castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        }
        
        return $grouped;
    }

    /**
     * Update multiple settings at once
     *
     * @param array $settings ['key' => 'value', ...]
     * @return void
     */
    public static function updateMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * Reset all settings to default
     *
     * @return void
     */
    public static function resetToDefaults(): void
    {
        // Clear all cache
        $allSettings = self::all();
        foreach ($allSettings as $setting) {
            Cache::forget('whatsapp_setting_' . $setting->key);
        }
        
        // This would require re-running the migration
        // For now, we'll just clear cache
    }

    /**
     * Cast value based on type
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function castValue(string $value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Convert value to string for storage
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private static function valueToString($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Scope to filter by group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Boot method to clear cache on update
     */
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($setting) {
            Cache::forget('whatsapp_setting_' . $setting->key);
        });
    }
}
