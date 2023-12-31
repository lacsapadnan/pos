<?php

namespace App\Http\Controllers;

use App\Http\Requests\TreasuryMutationRequest;
use App\Models\Cashflow;
use App\Models\TreasuryMutation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreasuryMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $roles = auth()->user()->roles->pluck('name')->implode(',');
        $cashiers = User::orderBy('id', 'asc')->get();
        return view('pages.mutation.index', compact('warehouses', 'roles', 'cashiers'));
    }

    public function data()
    {
        $mutation = TreasuryMutation::with(['fromWarehouse', 'toWarehouse', 'inputCashier', 'outputCashier'])->orderBy('input_date', 'desc')->get();
        return response()->json($mutation);
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
    public function store(TreasuryMutationRequest $request)
    {
        $data = $request->validated();
        $data['input_date'] = date('Y-m-d H:i:s', strtotime($data['input_date']));
        TreasuryMutation::create($data);

        Cashflow::create([
            'user_id' => $data['output_cashier'],
            'warehouse_id' => $data['from_warehouse'],
            'for' => 'Mutasi Kas',
            'description' => $data['description'],
            'out' => $data['amount'],
            'in' => 0,
        ]);

        return redirect()->back()->with('success', 'Mutasi kas berhasil ditambahkan');
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
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $treasuryMutation = TreasuryMutation::findOrFail($id);
        $treasuryMutation->delete();

        return redirect()->back()->with('success', 'Mutasi kas berhasil dihapus');
    }
}
