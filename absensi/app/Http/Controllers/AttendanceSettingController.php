<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AttendanceSettingController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        // Get settings grouped by group_name
        $settings = AttendanceSetting::getGrouped();

        return view('attendance.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Validate all settings
        $rules = [
            'settings.check_in_time' => 'required|date_format:H:i',
            'settings.check_out_time' => 'required|date_format:H:i|after:settings.check_in_time',
            'settings.tolerance_minutes' => 'required|integer|min:0|max:60',
            'settings.cutoff_time' => 'required|date_format:H:i|after:settings.check_in_time',
            'settings.enable_parent_notification' => 'required|boolean',
            'settings.include_photo_in_notification' => 'required|boolean',
            'settings.school_name' => 'required|string|max:100',
            'settings.announcement' => 'nullable|string|max:255',
        ];

        $messages = [
            'settings.check_in_time.required' => 'Jam masuk wajib diisi.',
            'settings.check_in_time.date_format' => 'Format jam masuk tidak valid (HH:MM).',
            'settings.check_out_time.required' => 'Jam pulang wajib diisi.',
            'settings.check_out_time.date_format' => 'Format jam pulang tidak valid (HH:MM).',
            'settings.check_out_time.after' => 'Jam pulang harus setelah jam masuk.',
            'settings.tolerance_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
            'settings.tolerance_minutes.integer' => 'Toleransi keterlambatan harus berupa angka.',
            'settings.tolerance_minutes.min' => 'Toleransi minimal 0 menit.',
            'settings.tolerance_minutes.max' => 'Toleransi maksimal 60 menit.',
            'settings.cutoff_time.required' => 'Batas waktu alpha wajib diisi.',
            'settings.cutoff_time.date_format' => 'Format batas waktu tidak valid (HH:MM).',
            'settings.cutoff_time.after' => 'Batas waktu harus setelah jam masuk.',
            'settings.school_name.required' => 'Nama sekolah wajib diisi.',
            'settings.school_name.max' => 'Nama sekolah maksimal 100 karakter.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update each setting
        $validated = $validator->validated();
        $settingsData = $validated['settings'];

        foreach ($settingsData as $key => $value) {
            AttendanceSetting::set($key, $value);
        }

        // Clear settings cache
        AttendanceSetting::clearCache();

        return redirect()
            ->route('attendance.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        // Default settings
        $defaults = [
            'check_in_time' => '07:00',
            'check_out_time' => '15:00',
            'tolerance_minutes' => '15',
            'cutoff_time' => '09:00',
            'enable_parent_notification' => '1',
            'include_photo_in_notification' => '0',
            'school_name' => 'SMK Negeri 1',
        ];

        foreach ($defaults as $key => $value) {
            AttendanceSetting::set($key, $value);
        }

        // Clear settings cache
        AttendanceSetting::clearCache();

        return redirect()
            ->route('attendance.settings.index')
            ->with('success', 'Pengaturan berhasil direset ke default.');
    }

    /**
     * Get settings as JSON (for API)
     */
    public function getSettings(Request $request)
    {
        $group = $request->input('group');

        if ($group) {
            $settings = AttendanceSetting::getByGroup($group);
        } else {
            $settings = AttendanceSetting::getGrouped();
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update single setting (for API)
     */
    public function updateSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $setting = AttendanceSetting::where('key', $validated['key'])->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan tidak ditemukan.'
            ], 404);
        }

        AttendanceSetting::set($validated['key'], $validated['value']);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil diperbarui.',
            'data' => [
                'key' => $validated['key'],
                'value' => $validated['value']
            ]
        ]);
    }

    /**
     * Test notification settings
     */
    public function testNotification(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|regex:/^628[0-9]{9,12}$/',
        ]);

        // Send test notification
        $whatsappService = app(\App\Services\AttendanceWhatsAppService::class);

        $message = "🔔 *Test Notifikasi*\n\n";
        $message .= "Ini adalah pesan test dari Sistem Absensi.\n";
        $message .= "Jika Anda menerima pesan ini, artinya konfigurasi WhatsApp Gateway sudah benar.\n\n";
        $message .= "_" . AttendanceSetting::get('school_name', 'SMK Negeri 1') . "_";

        $result = $whatsappService->sendParentNotification(
            $validated['phone'],
            $message
        );

        if ($result['success']) {
            return back()->with('success', 'Notifikasi test berhasil dikirim ke ' . $validated['phone']);
        } else {
            return back()->withErrors(['notification' => 'Gagal mengirim notifikasi: ' . $result['message']]);
        }
    }
}
