<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalarySetting;
use App\Models\Warehouse;
use App\Http\Requests\SalarySettingStoreRequest;
use App\Http\Requests\SalarySettingUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalarySettingController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('pages.salary.settings.index', compact('warehouses'));
    }

    public function data(Request $request)
    {
        $query = SalarySetting::with(['employee', 'warehouse'])
            ->when($request->warehouse_id, function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            });

        return DataTables::of($query)
            ->addColumn('employee_name', function ($row) {
                return $row->employee->name;
            })
            ->addColumn('warehouse_name', function ($row) {
                return $row->warehouse->name;
            })
            ->addColumn('actions', function ($row) {
                return view('pages.salary.settings.actions', compact('row'));
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        // Get employees who don't have salary settings yet
        $employees = Employee::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('salary_settings')
                ->whereColumn('salary_settings.employee_id', 'employees.id');
        })
            ->active()
            ->get();

        $warehouses = Warehouse::all();
        return view('pages.salary.settings.form', compact('employees', 'warehouses'));
    }

    public function store(SalarySettingStoreRequest $request)
    {
        SalarySetting::create($request->validated());

        return redirect()->route('salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil dibuat.');
    }

    public function edit(SalarySetting $salarySetting)
    {
        $employees = Employee::where('id', $salarySetting->employee_id)
            ->orWhereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('salary_settings')
                    ->whereColumn('salary_settings.employee_id', 'employees.id');
            })->get();

        $warehouses = Warehouse::all();
        return view('pages.salary.settings.form', compact('salarySetting', 'employees', 'warehouses'));
    }

    public function update(SalarySettingUpdateRequest $request, SalarySetting $salarySetting)
    {
        $salarySetting->update($request->validated());

        return redirect()->route('salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil diubah.');
    }

    public function destroy(SalarySetting $salarySetting)
    {
        $salarySetting->delete();
        return response()->json(['success' => true]);
    }
}
