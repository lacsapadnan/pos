<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Warehouse;
use App\Http\Requests\AttendanceStoreRequest;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

class AttendanceController extends Controller
{
    public function index()
    {
        // Check if user has permission to manage attendance
        if (auth()->user()->hasPermissionTo('kelola absensi')) {
            // Show attendance management interface for admins
            return $this->adminIndex();
        }

        // For regular users, just show their attendance status (read-only)
        return $this->userIndex();
    }

    public function admin()
    {
        $today = date('Y-m-d');
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::active()->with('warehouse')->orderBy('name')->get();

        return view('pages.attendance.admin', compact('today', 'warehouses', 'employees'));
    }

    private function adminIndex()
    {
        $warehouses = Warehouse::all();
        $employees = Employee::active()->with('warehouse')->orderBy('name')->get();
        $today = now()->format('Y-m-d');

        return view('pages.attendance.admin', compact('warehouses', 'employees', 'today'));
    }

    private function userIndex()
    {
        // Get today's attendance for current user (read-only)
        $todayAttendance = Attendance::where('employee_id', auth()->id())
            ->whereDate('check_in', today())
            ->first();

        return view('pages.attendance.user', compact('todayAttendance'));
    }

    public function create(AttendanceStoreRequest $request)
    {
        $employee = Employee::findOrFail($request->employee_id);

        // Combine date and time for timestamps
        $checkIn = Carbon::parse($request->check_in_date . ' ' . $request->check_in_time);
        $checkOut = null;

        if ($request->check_out_date && $request->check_out_time) {
            $checkOut = Carbon::parse($request->check_out_date . ' ' . $request->check_out_time);
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'warehouse_id' => $employee->warehouse_id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil ditambahkan',
            'attendance' => $attendance->load('employee', 'warehouse')
        ]);
    }

    // Legacy methods - deprecated but kept for compatibility
    public function checkIn(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur absen mandiri telah dinonaktifkan. Silakan hubungi admin untuk mengelola absensi.'
        ], 403);
    }

    public function checkOut(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur absen mandiri telah dinonaktifkan. Silakan hubungi admin untuk mengelola absensi.'
        ], 403);
    }

    public function recap()
    {
        if (!auth()->user()->hasPermissionTo('baca absensi')) {
            abort(403, 'Anda tidak memiliki izin untuk melihat rekap absensi');
        }

        $warehouses = Warehouse::all();
        $users = User::all();
        return view('pages.attendance.recap', compact('warehouses', 'users'));
    }

    public function data(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('baca absensi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melihat data absensi'
            ], 403);
        }

        $user = auth()->user();
        $employee_id = $request->input('employee_id');
        $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
        $toDate = $request->input('to_date') ?? now()->format('Y-m-d');
        $warehouse = $request->input('warehouse');

        $query = Attendance::with(['employee', 'warehouse'])
            ->orderBy('check_in', 'desc');

        // Role-based filtering
        if (!$user->hasRole('master') && !$user->hasPermissionTo('kelola absensi')) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        // Apply filters
        if ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        }

        if ($employee_id) {
            $query->where('employee_id', $employee_id);
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
        });

        return response()->json($attendances);
    }

    public function edit($id)
    {
        if (!auth()->user()->hasPermissionTo('update absensi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit absensi'
            ], 403);
        }

        $attendance = Attendance::with(['employee', 'warehouse'])->findOrFail($id);
        return response()->json($attendance);
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Combine date and time for timestamps
        $checkIn = Carbon::parse($request->check_in_date . ' ' . $request->check_in_time);
        $checkOut = null;

        if ($request->check_out_date && $request->check_out_time) {
            $checkOut = Carbon::parse($request->check_out_date . ' ' . $request->check_out_time);
        }

        $attendance->update([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil diperbarui',
            'attendance' => $attendance->load('employee', 'warehouse')
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasPermissionTo('hapus absensi')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus absensi'
            ], 403);
        }

        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus'
        ]);
    }

    public function getTodayStatus()
    {
        $user = auth()->user();

        $todayAttendance = Attendance::where('employee_id', $user->id)
            ->whereDate('check_in', today())
            ->first();

        return response()->json([
            'attendance' => $todayAttendance ? $todayAttendance->load('employee', 'warehouse') : null,
            'can_check_out' => $todayAttendance ? $todayAttendance->canCheckOut() : false,
        ]);
    }
}
