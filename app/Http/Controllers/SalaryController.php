<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Employee;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SalaryPayment;
use App\Models\SalarySetting;
use Yajra\DataTables\Facades\DataTables;
use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;

class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:baca gaji')->only(['index', 'data', 'show']);
        $this->middleware('can:simpan gaji')->only(['create', 'store']);
        $this->middleware('can:update gaji')->only(['edit', 'update']);
        $this->middleware('can:hapus gaji')->only(['destroy']);
        $this->middleware('can:approve gaji')->only(['approve', 'calculate']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('pages.salary.index', compact('warehouses'));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $query = SalaryPayment::with(['employee', 'warehouse', 'salarySetting', 'calculatedBy', 'approvedBy'])
            ->when($request->warehouse_id, function ($query) use ($request) {
                return $query->where('warehouse_id', $request->warehouse_id);
            })
            ->when($request->employee_id, function ($query) use ($request) {
                return $query->where('employee_id', $request->employee_id);
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->period_month && $request->period_year, function ($query) use ($request) {
                return $query->whereMonth('period_start', $request->period_month)
                    ->whereYear('period_start', $request->period_year);
            });

        return DataTables::of($query)
            ->addColumn('employee_name', function ($row) {
                return $row->employee->name;
            })
            ->addColumn('warehouse_name', function ($row) {
                return $row->warehouse->name;
            })
            ->addColumn('period', function ($row) {
                return $row->getPeriodString();
            })
            ->addColumn('attendance_percentage', function ($row) {
                return number_format($row->getAttendancePercentage(), 1) . '%';
            })
            ->addColumn('status_label', function ($row) {
                return view('pages.salary.status', compact('row'));
            })
            ->addColumn('actions', function ($row) {
                return view('pages.salary.actions', compact('row'));
            })
            ->rawColumns(['status_label', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::active()->with('user')->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        return view('pages.salary.create', compact('employees', 'warehouses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'notes' => 'nullable|string',
            'cash_advance_ids' => 'nullable|array',
        ]);

        // Custom validation for cash advance IDs
        if ($request->has('cash_advance_ids') && is_array($request->cash_advance_ids)) {
            foreach ($request->cash_advance_ids as $id) {
                $validDirectAdvance = CashAdvance::where('id', $id)
                    ->where('employee_id', $request->employee_id)
                    ->where('status', 'approved')
                    ->exists();

                $validInstallmentPayment = CashAdvancePayment::whereHas('cashAdvance', function ($query) use ($request) {
                    $query->where('employee_id', $request->employee_id)
                        ->where('status', 'approved');
                })
                    ->where('id', $id)
                    ->where('status', 'pending')
                    ->exists();

                if (!$validDirectAdvance && !$validInstallmentPayment) {
                    return redirect()->back()
                        ->withErrors(['cash_advance_ids' => 'Salah satu kasbon yang dipilih tidak valid.'])
                        ->withInput();
                }
            }
        }

        // Check if salary setting exists
        $salarySetting = SalarySetting::where('employee_id', $request->employee_id)->first();
        if (!$salarySetting) {
            return redirect()->back()->with('error', 'Karyawan belum memiliki pengaturan gaji.');
        }

        // Check for duplicate period
        $exists = SalaryPayment::where('employee_id', $request->employee_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('period_start', [$request->period_start, $request->period_end])
                    ->orWhereBetween('period_end', [$request->period_start, $request->period_end]);
            })->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Periode gaji sudah ada untuk karyawan ini.');
        }

        DB::beginTransaction();
        try {
            // Create salary payment
            $salaryPayment = new SalaryPayment($request->all());
            $salaryPayment->salary_setting_id = $salarySetting->id;
            $salaryPayment->daily_salary = $salarySetting->daily_salary;
            $salaryPayment->monthly_salary = $salarySetting->monthly_salary;
            $salaryPayment->status = 'draft';

            // Store cash advance IDs if provided
            if ($request->has('cash_advance_ids')) {
                $salaryPayment->cash_advance_ids = $request->cash_advance_ids;
            }

            $salaryPayment->save();

            // Automatically calculate the salary
            $salaryPayment->calculateSalary();

            DB::commit();
            return redirect()->route('gaji.index')
                ->with('success', 'Data gaji berhasil dibuat dan dihitung.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $salary = SalaryPayment::with(['employee', 'warehouse', 'calculatedBy', 'approvedBy'])->findOrFail($id);
        return view('pages.salary.show', compact('salary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalaryPayment $gaji)
    {
        if (!$gaji->isDraft()) {
            return redirect()->route('gaji.index')
                ->with('error', 'Hanya data gaji dengan status draft yang dapat diedit.');
        }

        $employees = Employee::whereHas('salarySetting')
            ->orWhere('id', $gaji->employee_id)
            ->get();
        $warehouses = Warehouse::all();
        return view('pages.salary.payment.form', compact('gaji', 'employees', 'warehouses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryPayment $gaji)
    {
        if (!$gaji->isDraft()) {
            return redirect()->route('gaji.index')
                ->with('error', 'Hanya data gaji dengan status draft yang dapat diedit.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'notes' => 'nullable|string',
            'cash_advance_ids' => 'nullable|array',
        ]);

        // Custom validation for cash advance IDs
        if ($request->has('cash_advance_ids') && is_array($request->cash_advance_ids)) {
            foreach ($request->cash_advance_ids as $id) {
                $validDirectAdvance = CashAdvance::where('id', $id)
                    ->where('employee_id', $request->employee_id)
                    ->where('status', 'approved')
                    ->exists();

                $validInstallmentPayment = CashAdvancePayment::whereHas('cashAdvance', function ($query) use ($request) {
                    $query->where('employee_id', $request->employee_id)
                        ->where('status', 'approved');
                })
                    ->where('id', $id)
                    ->where('status', 'pending')
                    ->exists();

                if (!$validDirectAdvance && !$validInstallmentPayment) {
                    return redirect()->back()
                        ->withErrors(['cash_advance_ids' => 'Salah satu kasbon yang dipilih tidak valid.'])
                        ->withInput();
                }
            }
        }

        // Check if salary setting exists
        $salarySetting = SalarySetting::where('employee_id', $request->employee_id)->first();
        if (!$salarySetting) {
            return redirect()->back()->with('error', 'Karyawan belum memiliki pengaturan gaji.');
        }

        // Check for duplicate period
        $exists = SalaryPayment::where('employee_id', $request->employee_id)
            ->where('id', '!=', $gaji->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('period_start', [$request->period_start, $request->period_end])
                    ->orWhereBetween('period_end', [$request->period_start, $request->period_end]);
            })->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Periode gaji sudah ada untuk karyawan ini.');
        }

        DB::beginTransaction();
        try {
            // Update salary payment
            $gaji->fill($request->all());
            $gaji->salary_setting_id = $salarySetting->id;
            $gaji->daily_salary = $salarySetting->daily_salary;
            $gaji->monthly_salary = $salarySetting->monthly_salary;

            // Store cash advance IDs if provided
            if ($request->has('cash_advance_ids')) {
                $gaji->cash_advance_ids = $request->cash_advance_ids;
            }

            $gaji->save();

            // Automatically recalculate the salary
            $gaji->calculateSalary();

            DB::commit();
            return redirect()->route('gaji.index')
                ->with('success', 'Data gaji berhasil diupdate dan dihitung ulang.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryPayment $gaji)
    {
        if (!$gaji->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya data gaji dengan status draft yang dapat dihapus.'
            ]);
        }

        $gaji->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Calculate salary based on attendance and cash advance
     */
    public function calculate(SalaryPayment $salary)
    {
        if (!$salary->isDraft()) {
            return redirect()->back()->with('error', 'Hanya data gaji dengan status draft yang dapat dihitung.');
        }

        try {
            $salary->calculateSalary();
            return redirect()->back()->with('success', 'Perhitungan gaji berhasil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghitung gaji: ' . $e->getMessage());
        }
    }

    /**
     * Approve calculated salary
     */
    public function approve(SalaryPayment $salary)
    {
        if (!$salary->isCalculated()) {
            return redirect()->back()->with('error', 'Hanya data gaji yang sudah dihitung yang dapat disetujui.');
        }

        $salary->status = 'approved';
        $salary->approved_by = auth()->id();
        $salary->approved_at = now();
        $salary->save();

        return redirect()->back()->with('success', 'Data gaji berhasil disetujui.');
    }

    /**
     * Mark salary as paid
     */
    public function markPaid(SalaryPayment $salary)
    {
        if (!$salary->isApproved()) {
            return redirect()->back()->with('error', 'Hanya data gaji yang sudah disetujui yang dapat ditandai sudah dibayar.');
        }

        DB::beginTransaction();
        try {
            // Mark salary as paid
            $salary->status = 'paid';
            $salary->paid_at = now();
            $salary->save();

            // Update cash advance payments if any were deducted
            if (!empty($salary->cash_advance_ids)) {
                $selectedDeductions = array_map('intval', $salary->cash_advance_ids);

                // Handle direct cash advances
                $directAdvances = CashAdvance::where('employee_id', $salary->employee_id)
                    ->where('status', 'approved')
                    ->where('type', 'direct')
                    ->whereIn('id', $selectedDeductions)
                    ->get();

                foreach ($directAdvances as $cashAdvance) {
                    $remainingAmount = $cashAdvance->amount - $cashAdvance->paid_amount;
                    if ($remainingAmount > 0) {
                        // Update paid amount and status
                        $cashAdvance->paid_amount = $cashAdvance->amount;
                        $cashAdvance->status = 'completed';
                        $cashAdvance->save();
                    }
                }

                // Handle installment payments
                $installmentPayments = CashAdvancePayment::whereHas('cashAdvance', function ($query) use ($salary) {
                    $query->where('employee_id', $salary->employee_id)
                        ->where('status', 'approved')
                        ->where('type', 'installment');
                })
                    ->where('status', 'pending')
                    ->whereIn('id', $selectedDeductions)
                    ->get();

                foreach ($installmentPayments as $payment) {
                    // Mark payment as paid
                    $payment->status = 'paid';
                    $payment->payment_date = now();
                    $payment->processed_by = auth()->id();
                    $payment->save();

                    // Update cash advance paid amount
                    $cashAdvance = $payment->cashAdvance;
                    $cashAdvance->paid_amount += $payment->amount;

                    // Check if all payments are completed
                    $remainingPayments = $cashAdvance->payments()->where('status', 'pending')->count();
                    if ($remainingPayments == 0) {
                        $cashAdvance->status = 'completed';
                    }

                    $cashAdvance->save();
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data gaji berhasil ditandai sudah dibayar dan kasbon terkait telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
