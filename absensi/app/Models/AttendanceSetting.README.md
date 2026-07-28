# AttendanceSetting Model

## Overview
The `AttendanceSetting` model provides a key-value configuration system for the attendance application. It includes automatic caching for improved performance.

## Features
- Key-value storage for application settings
- Automatic caching with 1-hour duration
- Static methods for easy access: `get()` and `set()`
- Settings grouped by category (time, tolerance, notification, general)
- Cache invalidation on updates

## Usage

### Retrieve a Setting
```php
// Get a setting value by key
$checkInTime = AttendanceSetting::get('check_in_time');

// Get with default value if not found
$maxRetries = AttendanceSetting::get('max_retries', 3);
```

### Update or Create a Setting
```php
// Simple update (preserves existing group_name)
AttendanceSetting::set('check_in_time', '07:30');

// Create new setting with group
AttendanceSetting::set('new_key', 'new_value', 'general', 'Description here');

// Update with new group and description
AttendanceSetting::set('existing_key', 'updated_value', 'notification', 'Updated description');
```

### Get Settings by Group
```php
// Get all time-related settings
$timeSettings = AttendanceSetting::getByGroup('time');
// Returns: ['check_in_time' => '07:00', 'check_out_time' => '15:00', ...]

// Get all settings grouped
$allGrouped = AttendanceSetting::getGrouped();
// Returns: ['time' => [...], 'notification' => [...], ...]
```

### Clear Cache
```php
// Clear all settings cache (useful after bulk updates)
AttendanceSetting::clearCache();
```

## Database Schema

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar(100) | Unique setting key |
| value | text | Setting value |
| group_name | varchar(50) | Setting category/group |
| description | text | Optional description |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## Default Settings

The following settings are seeded by default:

| Key | Value | Group | Description |
|-----|-------|-------|-------------|
| check_in_time | 07:00 | time | Official check-in time |
| check_out_time | 15:00 | time | Official check-out time |
| tolerance_minutes | 15 | tolerance | Late tolerance (minutes) |
| cutoff_time | 09:00 | time | Check-in deadline (after = alpha) |
| enable_parent_notification | true | notification | Enable parent notifications |
| include_photo_in_notification | false | notification | Include photo in WhatsApp |
| school_name | SMK PGRI BLORA | general | School name |

## Caching Behavior

- Settings are cached for 1 hour (3600 seconds)
- Cache key format: `attendance_setting_{key}`
- Cache is automatically cleared when a setting is updated via `set()`
- Manual cache clearing available via `clearCache()`

## Examples in Service Classes

```php
// AttendanceStatusService
$checkInTime = AttendanceSetting::get('check_in_time', '07:00');
$tolerance = AttendanceSetting::get('tolerance_minutes', 15);

// AttendanceNotificationService
$schoolName = AttendanceSetting::get('school_name', 'Sekolah');
$includePhoto = AttendanceSetting::get('include_photo_in_notification', 'false') === 'true';

// AttendanceSettingController
AttendanceSetting::set('check_in_time', $request->check_in_time);
AttendanceSetting::set('tolerance_minutes', $request->tolerance_minutes);
```

## Testing

Comprehensive unit tests are available in `tests/Unit/AttendanceSettingTest.php` covering:
- Getting existing settings
- Getting with default values
- Creating new settings
- Updating existing settings
- Cache behavior
- Default seeded settings
