<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('id', 'asc')->get();
        return view('pages.employee.index', compact('warehouses'));
    }

    public function data()
    {
        $employees = Employee::with('warehouse',)->orderBy('id', 'asc')->get();
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
        try {
            DB::beginTransaction();

            Employee::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'warehouse_id' => $request->warehouse_id,
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

        return view('pages.employee.edit', compact('warehouses', 'employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($id);
            $employee->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'warehouse_id' => $request->warehouse_id,
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
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->back()->withSuccess('Karyawan berhasil dihapus');
    }
}
