<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceClassRequest;
use App\Http\Requests\UpdateAttendanceClassRequest;
use App\Models\AttendanceClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceClassController extends Controller
{
    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        $query = AttendanceClass::with('waliKelas')->withCount('students');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%")
                    ->orWhere('tingkat', 'like', "%{$search}%");
            });
        }

        // Filter by tingkat
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->input('tingkat'));
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $classes = $query->orderBy('tingkat', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->paginate(20);

        return view('attendance.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        // Get all users as potential wali kelas (since role column doesn't exist)
        $teachers = User::orderBy('name', 'asc')->get();

        return view('attendance.classes.create', compact('teachers'));
    }

    /**
     * Store a newly created class
     */
    public function store(StoreAttendanceClassRequest $request)
    {
        $validated = $request->validated();

        // Check for duplicate class
        $exists = AttendanceClass::where('nama_kelas', $validated['nama_kelas'])
            ->where('tingkat', $validated['tingkat'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'nama_kelas' => 'Kelas dengan nama dan tingkat yang sama sudah ada.'
            ])->withInput();
        }

        $class = AttendanceClass::create($validated);

        return redirect()
            ->route('attendance.classes.index')
            ->with('success', "Kelas {$class->nama_kelas} berhasil ditambahkan.");
    }

    /**
     * Display the specified class
     */
    public function show(AttendanceClass $class)
    {
        $class->load(['students' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('nama', 'asc');
        }]);

        // Get attendance statistics for this class
        $stats = [
            'total_students' => $class->students->count(),
            'checked_in_today' => $class->students->filter(function ($student) {
                return $student->hasCheckedInToday();
            })->count(),
            'checked_out_today' => $class->students->filter(function ($student) {
                return $student->hasCheckedOutToday();
            })->count(),
        ];

        return view('attendance.classes.show', compact('class', 'stats'));
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit(AttendanceClass $class)
    {
        // Get all users as potential wali kelas (since role column doesn't exist)
        $teachers = User::orderBy('name', 'asc')->get();

        return view('attendance.classes.edit', compact('class', 'teachers'));
    }

    /**
     * Update the specified class
     */
    public function update(UpdateAttendanceClassRequest $request, AttendanceClass $class)
    {
        $validated = $request->validated();

        // Check for duplicate class (excluding current)
        $exists = AttendanceClass::where('nama_kelas', $validated['nama_kelas'])
            ->where('tingkat', $validated['tingkat'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'nama_kelas' => 'Kelas dengan nama dan tingkat yang sama sudah ada.'
            ])->withInput();
        }

        $class->update($validated);

        return redirect()
            ->route('attendance.classes.index')
            ->with('success', "Kelas {$class->nama_kelas} berhasil diperbarui.");
    }

    /**
     * Remove the specified class
     */
    public function destroy(AttendanceClass $class)
    {
        // Check if class has students
        if ($class->students()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Tidak dapat menghapus kelas yang masih memiliki siswa. Pindahkan atau hapus siswa terlebih dahulu.'
            ]);
        }

        $className = $class->nama_kelas;
        $class->delete();

        return redirect()
            ->route('attendance.classes.index')
            ->with('success', "Kelas {$className} berhasil dihapus.");
    }

    /**
     * Toggle active status
     */
    public function toggleActive(AttendanceClass $class)
    {
        $class->update(['is_active' => !$class->is_active]);

        $status = $class->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success' => true,
            'message' => "Kelas {$class->nama_kelas} berhasil {$status}.",
            'is_active' => $class->is_active
        ]);
    }
}
