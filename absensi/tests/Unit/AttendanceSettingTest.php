<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AttendanceSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AttendanceSettingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that get method retrieves existing setting value.
     */
    public function test_get_returns_existing_setting_value(): void
    {
        // Arrange
        AttendanceSetting::create([
            'key' => 'test_key',
            'value' => 'test_value',
            'group_name' => 'test',
            'description' => 'Test setting',
        ]);

        // Act
        $value = AttendanceSetting::get('test_key');

        // Assert
        $this->assertEquals('test_value', $value);
    }

    /**
     * Test that get method returns default value when key not found.
     */
    public function test_get_returns_default_when_key_not_found(): void
    {
        // Act
        $value = AttendanceSetting::get('nonexistent_key', 'default_value');

        // Assert
        $this->assertEquals('default_value', $value);
    }

    /**
     * Test that get method returns null when key not found and no default.
     */
    public function test_get_returns_null_when_key_not_found_and_no_default(): void
    {
        // Act
        $value = AttendanceSetting::get('nonexistent_key');

        // Assert
        $this->assertNull($value);
    }

    /**
     * Test that set method creates new setting.
     */
    public function test_set_creates_new_setting(): void
    {
        // Act
        $result = AttendanceSetting::set('new_key', 'new_value');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('attendance_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
    }

    /**
     * Test that set method updates existing setting.
     */
    public function test_set_updates_existing_setting(): void
    {
        // Arrange
        AttendanceSetting::create([
            'key' => 'update_key',
            'value' => 'old_value',
            'group_name' => 'test',
            'description' => 'Test setting',
        ]);

        // Act
        $result = AttendanceSetting::set('update_key', 'new_value');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('attendance_settings', [
            'key' => 'update_key',
            'value' => 'new_value',
        ]);
    }

    /**
     * Test that get method uses cache.
     */
    public function test_get_uses_cache(): void
    {
        // Arrange
        AttendanceSetting::create([
            'key' => 'cache_key',
            'value' => 'cache_value',
            'group_name' => 'test',
            'description' => 'Test setting',
        ]);

        // Act - First call (should cache)
        $value1 = AttendanceSetting::get('cache_key');

        // Verify cache exists
        $cachedValue = Cache::get('attendance_setting_cache_key');

        // Assert
        $this->assertEquals('cache_value', $value1);
        $this->assertEquals('cache_value', $cachedValue);
    }

    /**
     * Test that set method clears cache.
     */
    public function test_set_clears_cache(): void
    {
        // Arrange
        AttendanceSetting::create([
            'key' => 'clear_key',
            'value' => 'old_value',
            'group_name' => 'test',
            'description' => 'Test setting',
        ]);

        // Get value to populate cache
        AttendanceSetting::get('clear_key');

        // Act - Update setting
        AttendanceSetting::set('clear_key', 'new_value');

        // Get cache directly
        $cachedValue = Cache::get('attendance_setting_clear_key');

        // Assert - Cache should be cleared
        $this->assertNull($cachedValue);
    }

    /**
     * Test seeded default settings exist.
     */
    public function test_default_settings_exist(): void
    {
        // Assert that key default settings are present
        $checkInTime = AttendanceSetting::get('check_in_time');
        $checkOutTime = AttendanceSetting::get('check_out_time');
        $toleranceMinutes = AttendanceSetting::get('tolerance_minutes');
        $cutoffTime = AttendanceSetting::get('cutoff_time');

        $this->assertNotNull($checkInTime);
        $this->assertNotNull($checkOutTime);
        $this->assertNotNull($toleranceMinutes);
        $this->assertNotNull($cutoffTime);
    }
}
