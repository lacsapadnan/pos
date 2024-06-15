<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Settlement;
use App\Models\TreasuryMutation;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        $roles = auth()->user()->roles->pluck('name')->implode(',');
        $cashiers = User::orderBy('id', 'asc')->get();
        return view('pages.settlement.index', compact('warehouses', 'roles', 'cashiers'));
    }

    public function data()
    {
        $settlements = Settlement::with('mutation', 'cashier')->get();
        return response()->json($settlements);
    }

    public function combinedData()
    {
        $combinedData = TreasuryMutation::with(['fromWarehouse', 'toWarehouse', 'inputCashier', 'outputCashier'])
            ->orderBy('input_date', 'desc')
            ->get()
            ->map(function ($mutation) {
                $settlement = Settlement::where('mutation_id', $mutation->id)->first();

                $totalReceived = $settlement ? $settlement->total_received : 0;
                $outstanding = $settlement ? $settlement->outstanding : 0;

                return [
                    'id' => $mutation->id,
                    'input_date' => $mutation->input_date,
                    'from_warehouse' => $mutation->fromWarehouse->name,
                    'output_cashier' => $mutation->outputCashier->name,
                    'from_treasury' => $mutation->from_treasury,
                    'amount' => $mutation->amount,
                    'total_received' => $totalReceived,
                    'outstanding' => $outstanding,
                ];
            });

        return response()->json($combinedData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.settlement.all-data');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $inputRequests = $request->input('requests');

            if (empty($inputRequests)) {
                return response()->json(['error' => 'No requests to process'], 400);
            }

            $settlements = [];

            foreach ($inputRequests as $inputRequest) {
                if (!isset($inputRequest['mutation_id']) || !isset($inputRequest['total_recieved'])) {
                    return response()->json(['error' => 'Invalid input format'], 400);
                }

                $mutationId = $inputRequest['mutation_id'];
                $mutation = TreasuryMutation::find($mutationId);

                if (!$mutation) {
                    return response()->json(['error' => 'Mutation not found for mutation_id ' . $mutationId], 404);
                }

                // Calculate outstanding value based on your logic
                $totalReceived = $inputRequest['total_recieved'];
                $outstanding = $mutation->amount - $totalReceived;

                // Create a new Settlement record and add it to the $settlements array
                $settlements[] = [
                    'mutation_id' => $mutationId,
                    'cashier_id' => auth()->id(), // Assuming you have authentication set up
                    'total_recieved' => $totalReceived,
                    'outstanding' => $outstanding,
                    'to_treasury' => "Kas Bank 2", // Adjust as needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert all settlements in a single database transaction
            Settlement::insert($settlements);
            DB::commit();

            return response()->json(['message' => 'Settlements created successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in store method: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
        }
    }

    public function actionStore(Request $request)
    {
        $mutation = TreasuryMutation::find($request->mutation_id);
        $totalRecieved = $request->amount;
        $outstanding = $mutation->amount - $totalRecieved;

        $settlement = new Settlement();
        $settlement->mutation_id = $request->mutation_id;
        $settlement->cashier_id = $request->output_cashier;
        $settlement->total_recieved = $totalRecieved;
        $settlement->outstanding = $outstanding;
        $settlement->to_treasury = $request->from_warehouse;
        $settlement->save();

        Cashflow::create([
            'user_id' => $request->output_cashier,
            'warehouse_id' => $request->from_warehouse,
            'for' => 'Settlement',
            'description' => $request->description,
            'out' => 0,
            'in' => $totalRecieved,
        ]);

        return redirect()->route('laporan')->with('success', 'Settlement created successfully');
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
        $settlement = Settlement::find($id);
        $settlement->delete();

        $cashflow = Cashflow::where('for', 'Settlement')->where('user_id', $settlement->cashier_id)->where('warehouse_id', $settlement->to_treasury)->first();
        if ($cashflow) {
            $cashflow->delete();
        }

        return redirect()->back()->with('success', 'Settlement deleted successfully');
    }
}
