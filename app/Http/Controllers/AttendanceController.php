<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        // Get today's attendance for current user
        $todayAttendance = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in', today())
            ->first();

        return view('pages.attendance.index', compact('todayAttendance'));
    }

    public function checkIn(Request $request)
    {
        $user = auth()->user();

        // Check if user already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', today())
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini'
            ]);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'warehouse_id' => $user->warehouse_id,
            'check_in' => now(),
            'status' => 'checked_in'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil',
            'attendance' => $attendance->load('user', 'warehouse')
        ]);
    }

    public function checkOut(Request $request)
    {
        $user = auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini'
            ]);
        }

        if (!$attendance->canCheckOut()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum bisa absen keluar. Minimal durasi kerja adalah 1 jam.'
            ]);
        }

        // If on break, end the break first
        if ($attendance->isOnBreak()) {
            $attendance->break_end = now();
        }

        $attendance->update([
            'check_out' => now(),
            'status' => 'checked_out'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen keluar berhasil',
            'attendance' => $attendance->load('user', 'warehouse')
        ]);
    }

    public function startBreak(Request $request)
    {
        $user = auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum absen masuk hari ini'
            ]);
        }

        if ($attendance->hasUsedBreak()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah menggunakan istirahat hari ini'
            ]);
        }

        $attendance->update([
            'break_start' => now(),
            'status' => 'on_break'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Istirahat dimulai',
            'attendance' => $attendance->load('user', 'warehouse')
        ]);
    }

    public function endBreak(Request $request)
    {
        $user = auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance || !$attendance->isOnBreak()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak sedang istirahat'
            ]);
        }

        $attendance->update([
            'break_end' => now(),
            'status' => 'checked_in'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Istirahat selesai',
            'attendance' => $attendance->load('user', 'warehouse')
        ]);
    }

    public function recap()
    {
        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.attendance.recap', compact('warehouses', 'users'));
    }

    public function data(Request $request)
    {
        $userRoles = auth()->user()->getRoleNames();
        $user_id = $request->input('user_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');
        $warehouse = $request->input('warehouse');

        $query = Attendance::with(['user', 'warehouse'])
            ->orderBy('check_in', 'desc');

        // Role-based filtering
        if ($userRoles->first() !== 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        // Apply filters
        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($fromDate && $toDate) {
            $endDate = Carbon::parse($toDate)->endOfDay();
            $query->whereBetween('check_in', [$fromDate, $endDate]);
        }

        $attendances = $query->get();

        // Add calculated fields
        $attendances->each(function ($attendance) {
            $attendance->total_hours = $attendance->getTotalWorkHours();
            $attendance->can_check_out = $attendance->canCheckOut();
            $attendance->is_on_break = $attendance->isOnBreak();
        });

        return response()->json($attendances);
    }

    public function edit($id)
    {
        $attendance = Attendance::with(['user', 'warehouse'])->findOrFail($id);
        return response()->json($attendance);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_in_time' => 'required',
            'check_out_date' => 'nullable|date',
            'check_out_time' => 'nullable',
            'break_start_date' => 'nullable|date',
            'break_start_time' => 'nullable',
            'break_end_date' => 'nullable|date',
            'break_end_time' => 'nullable',
            'status' => 'required|in:checked_in,checked_out,on_break',
            'notes' => 'nullable|string'
        ]);

        $attendance = Attendance::findOrFail($id);

        // Combine date and time for timestamps
        $checkIn = Carbon::parse($request->check_in_date . ' ' . $request->check_in_time);
        $checkOut = null;
        $breakStart = null;
        $breakEnd = null;

        if ($request->check_out_date && $request->check_out_time) {
            $checkOut = Carbon::parse($request->check_out_date . ' ' . $request->check_out_time);
        }

        if ($request->break_start_date && $request->break_start_time) {
            $breakStart = Carbon::parse($request->break_start_date . ' ' . $request->break_start_time);
        }

        if ($request->break_end_date && $request->break_end_time) {
            $breakEnd = Carbon::parse($request->break_end_date . ' ' . $request->break_end_time);
        }

        // Validation: check_out should be after check_in
        if ($checkOut && $checkOut->lt($checkIn)) {
            return response()->json([
                'success' => false,
                'message' => 'Jam keluar harus setelah jam masuk'
            ], 422);
        }

        // Validation: break times should be within work hours
        if ($breakStart && ($breakStart->lt($checkIn) || ($checkOut && $breakStart->gt($checkOut)))) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu istirahat harus dalam jam kerja'
            ], 422);
        }

        if ($breakEnd && ($breakEnd->lt($checkIn) || ($checkOut && $breakEnd->gt($checkOut)))) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu selesai istirahat harus dalam jam kerja'
            ], 422);
        }

        if ($breakStart && $breakEnd && $breakEnd->lt($breakStart)) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu selesai istirahat harus setelah waktu mulai istirahat'
            ], 422);
        }

        $attendance->update([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui',
            'attendance' => $attendance->load('user', 'warehouse')
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        // Check if user has permission to delete this attendance
        $userRoles = auth()->user()->getRoleNames();
        if ($userRoles->first() !== 'master' && $attendance->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus data ini'
            ], 403);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus'
        ]);
    }

    public function getTodayStatus()
    {
        $user = auth()->user();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', today())
            ->first();

        return response()->json([
            'attendance' => $todayAttendance ? $todayAttendance->load('user', 'warehouse') : null,
            'can_check_out' => $todayAttendance ? $todayAttendance->canCheckOut() : false,
            'is_on_break' => $todayAttendance ? $todayAttendance->isOnBreak() : false,
            'has_used_break' => $todayAttendance ? $todayAttendance->hasUsedBreak() : false,
        ]);
    }
}
