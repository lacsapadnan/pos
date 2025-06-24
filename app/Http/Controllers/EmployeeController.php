<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get();
        return view('pages.employee.index', compact('warehouses', 'users'));
    }

    public function data()
    {
        $employees = Employee::with('warehouse', 'user')->orderBy('id', 'asc')->get();
        return response()->json($employees);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nickname' => 'nullable|string|max:255',
            'ktp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'warehouse_id' => 'required|exists:warehouses,id',
            'user_id' => 'required|exists:users,id|unique:employees,user_id',
        ]);

        try {
            DB::beginTransaction();

            $ktpPath = null;
            if ($request->hasFile('ktp')) {
                $ktpPath = $request->file('ktp')->store('ktp-images', 'public');
            }

            Employee::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'nickname' => $request->nickname,
                'ktp' => $ktpPath,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => $request->user_id,
            ]);

            DB::commit();
            return redirect()->back()->withSuccess('Karyawan berhasil ditambahkan');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors($th->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get();

        return view('pages.employee.edit', compact('warehouses', 'employee', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nickname' => 'nullable|string|max:255',
            'ktp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'warehouse_id' => 'required|exists:warehouses,id',
            'user_id' => 'required|exists:users,id|unique:employees,user_id,' . $id,
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($id);

            $ktpPath = $employee->ktp; // Keep existing path if no new file
            if ($request->hasFile('ktp')) {
                // Delete old KTP image if exists
                if ($employee->ktp && Storage::disk('public')->exists($employee->ktp)) {
                    Storage::disk('public')->delete($employee->ktp);
                }

                $ktpPath = $request->file('ktp')->store('ktp-images', 'public');
            }

            $employee->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'nickname' => $request->nickname,
                'ktp' => $ktpPath,
                'warehouse_id' => $request->warehouse_id,
                'user_id' => $request->user_id,
            ]);

            DB::commit();
            return redirect()->route('karyawan.index')->withSuccess('Karyawan berhasil diperbarui');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors($th->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($id);

            // Delete KTP image if exists
            if ($employee->ktp && Storage::disk('public')->exists($employee->ktp)) {
                Storage::disk('public')->delete($employee->ktp);
            }

            $employee->delete();

            DB::commit();
            return redirect()->back()->withSuccess('Karyawan berhasil dihapus');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->withErrors($th->getMessage());
        }
    }
}
