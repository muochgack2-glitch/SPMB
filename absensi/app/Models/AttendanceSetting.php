<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'group_name', 'description'])]
class AttendanceSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_settings';

    /**
     * Cache duration in seconds (1 hour).
     *
     * @var int
     */
    private const CACHE_DURATION = 3600;

    /**
     * Retrieve setting value by key.
     * Returns default value if setting not found.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "attendance_setting_{$key}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Update or create setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $groupName
     * @param string|null $description
     * @return bool
     */
    public static function set(string $key, $value, ?string $groupName = null, ?string $description = null): bool
    {
        try {
            $data = ['value' => $value];
            
            // Only update group_name and description if provided
            if ($groupName !== null) {
                $data['group_name'] = $groupName;
            }
            if ($description !== null) {
                $data['description'] = $description;
            }

            // Get existing setting to preserve group_name if not provided
            $existing = self::where('key', $key)->first();
            
            if (!$existing && $groupName === null) {
                // New setting without group_name, set default
                $data['group_name'] = 'general';
            }

            self::updateOrCreate(
                ['key' => $key],
                $data
            );

            // Clear cache for this setting
            Cache::forget("attendance_setting_{$key}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all settings grouped by group_name.
     *
     * @return array
     */
    public static function getGrouped(): array
    {
        return self::all()
            ->groupBy('group_name')
            ->map(function ($group) {
                return $group->pluck('value', 'key')->toArray();
            })
            ->toArray();
    }

    /**
     * Get all settings in a specific group.
     *
     * @param string $groupName
     * @return array
     */
    public static function getByGroup(string $groupName): array
    {
        return self::where('group_name', $groupName)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Clear all settings cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');

        foreach ($keys as $key) {
            Cache::forget("attendance_setting_{$key}");
        }
    }
}
