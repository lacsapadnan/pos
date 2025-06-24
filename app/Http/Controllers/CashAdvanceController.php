<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use App\Models\Employee;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:baca kasbon')->only(['index', 'data', 'show']);
        $this->middleware('can:simpan kasbon')->only(['create', 'store']);
        $this->middleware('can:update kasbon')->only(['edit', 'update']);
        $this->middleware('can:hapus kasbon')->only(['destroy']);
        $this->middleware('can:approve kasbon')->only(['approve', 'reject']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::with('user')->orderBy('name')->get();
        return view('pages.cash-advance.index', compact('warehouses', 'employees'));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $userRoles = auth()->user()->getRoleNames();
        $query = CashAdvance::with(['employee.user', 'warehouse', 'approvedBy'])
            ->orderBy('created_at', 'desc');

        // Role-based filtering
        if ($userRoles->first() !== 'master') {
            $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        // Apply filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('advance_date', [$request->from_date, $request->to_date]);
        }

        $cashAdvances = $query->get();

        return response()->json([
            'data' => $cashAdvances
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userRoles = auth()->user()->getRoleNames();
        $warehouses = Warehouse::orderBy('name')->get();

        if ($userRoles->first() === 'master') {
            $employees = Employee::with('user')->orderBy('name')->get();
        } else {
            $employees = Employee::with('user')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->orderBy('name')->get();
        }

        return view('pages.cash-advance.create', compact('warehouses', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'amount' => 'required|numeric|min:1|max:999999999.99',
            'advance_date' => 'required|date',
            'type' => 'required|in:direct,installment',
            'installment_count' => 'required_if:type,installment|nullable|integer|min:2|max:36',
            'description' => 'nullable|string|max:1000',
        ], [
            'employee_id.required' => 'Karyawan harus dipilih',
            'employee_id.exists' => 'Karyawan tidak valid',
            'warehouse_id.required' => 'Cabang harus dipilih',
            'warehouse_id.exists' => 'Cabang tidak valid',
            'amount.required' => 'Jumlah kasbon harus diisi',
            'amount.numeric' => 'Jumlah kasbon harus berupa angka',
            'amount.min' => 'Jumlah kasbon minimal Rp 1',
            'advance_date.required' => 'Tanggal kasbon harus diisi',
            'advance_date.date' => 'Format tanggal tidak valid',
            'type.required' => 'Tipe pembayaran harus dipilih',
            'type.in' => 'Tipe pembayaran tidak valid',
            'installment_count.required_if' => 'Jumlah cicilan harus diisi untuk tipe cicilan',
            'installment_count.integer' => 'Jumlah cicilan harus berupa angka',
            'installment_count.min' => 'Jumlah cicilan minimal 2',
            'installment_count.max' => 'Jumlah cicilan maksimal 36',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $cashAdvance = CashAdvance::create([
                'employee_id' => $request->employee_id,
                'warehouse_id' => $request->warehouse_id,
                'amount' => $request->amount,
                'advance_date' => $request->advance_date,
                'type' => $request->type,
                'installment_count' => $request->type === 'installment' ? $request->installment_count : null,
                'description' => $request->description,
            ]);

            // Create installment payment records if installment type
            if ($request->type === 'installment') {
                $this->createInstallmentPayments($cashAdvance);
            }

            DB::commit();
            return redirect()->route('kasbon.index')->with('success', 'Kasbon berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cashAdvance = CashAdvance::with(['employee.user', 'warehouse', 'approvedBy', 'payments.processedBy'])->findOrFail($id);
        return view('pages.cash-advance.show', compact('cashAdvance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cashAdvance = CashAdvance::with(['employee.user', 'warehouse', 'approvedBy', 'payments.processedBy'])->findOrFail($id);
        // Only allow editing if status is pending
        if ($cashAdvance->status !== 'pending') {
            return redirect()->route('kasbon.index')->with('error', 'Kasbon yang sudah diproses tidak dapat diedit');
        }

        $userRoles = auth()->user()->getRoleNames();
        $warehouses = Warehouse::orderBy('name')->get();

        if ($userRoles->first() === 'master') {
            $employees = Employee::with('user')->orderBy('name')->get();
        } else {
            $employees = Employee::with('user')
                ->where('warehouse_id', auth()->user()->warehouse_id)
                ->orderBy('name')->get();
        }

        return view('pages.cash-advance.edit', compact('cashAdvance', 'warehouses', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashAdvance $cashAdvance)
    {
        // Only allow updating if status is pending
        if ($cashAdvance->status !== 'pending') {
            return redirect()->route('kasbon.index')->with('error', 'Kasbon yang sudah diproses tidak dapat diedit');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'amount' => 'required|numeric|min:1|max:999999999.99',
            'advance_date' => 'required|date',
            'type' => 'required|in:direct,installment',
            'installment_count' => 'required_if:type,installment|nullable|integer|min:2|max:36',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Delete existing payment records if changing type or installment count
            if (
                $cashAdvance->type !== $request->type ||
                $cashAdvance->installment_count != $request->installment_count
            ) {
                $cashAdvance->payments()->delete();
            }

            $cashAdvance->update([
                'employee_id' => $request->employee_id,
                'warehouse_id' => $request->warehouse_id,
                'amount' => $request->amount,
                'advance_date' => $request->advance_date,
                'type' => $request->type,
                'installment_count' => $request->type === 'installment' ? $request->installment_count : null,
                'description' => $request->description,
            ]);

            // Recreate installment payments if needed
            if ($request->type === 'installment') {
                $this->createInstallmentPayments($cashAdvance);
            }

            DB::commit();
            return redirect()->route('kasbon.index')->with('success', 'Kasbon berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashAdvance $cashAdvance)
    {
        // Only allow deletion if status is pending
        if ($cashAdvance->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Kasbon yang sudah diproses tidak dapat dihapus']);
        }

        try {
            $cashAdvance->delete();
            // Set session flash message for SweetAlert
            session()->flash('success', 'Kasbon berhasil dihapus');
            return response()->json(['success' => true, 'message' => 'Kasbon berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve cash advance
     */
    public function approve(Request $request, CashAdvance $cashAdvance)
    {
        if ($cashAdvance->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Kasbon ini sudah diproses']);
        }

        try {
            $cashAdvance->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Set session flash message for SweetAlert
            session()->flash('success', 'Kasbon berhasil disetujui');
            return response()->json(['success' => true, 'message' => 'Kasbon berhasil disetujui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject cash advance
     */
    public function reject(Request $request, CashAdvance $cashAdvance)
    {
        if ($cashAdvance->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Kasbon ini sudah diproses']);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $cashAdvance->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Set session flash message for SweetAlert
            session()->flash('success', 'Kasbon berhasil ditolak');
            return response()->json(['success' => true, 'message' => 'Kasbon berhasil ditolak']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Create installment payment records
     */
    private function createInstallmentPayments(CashAdvance $cashAdvance)
    {
        if ($cashAdvance->type !== 'installment' || !$cashAdvance->installment_count) {
            return;
        }

        $installmentAmount = $cashAdvance->amount / $cashAdvance->installment_count;
        $dueDate = Carbon::parse($cashAdvance->advance_date)->addMonth();

        for ($i = 1; $i <= $cashAdvance->installment_count; $i++) {
            CashAdvancePayment::create([
                'cash_advance_id' => $cashAdvance->id,
                'processed_by' => auth()->id(),
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'due_date' => $dueDate->copy(),
                'status' => 'pending',
            ]);

            $dueDate->addMonth();
        }
    }
}
