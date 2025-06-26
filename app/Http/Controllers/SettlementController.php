<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Settlement;
use App\Models\TreasuryMutation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CashflowService;
use App\Http\Requests\SettlementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementController extends Controller
{
    private CashflowService $cashflowService;

    public function __construct(CashflowService $cashflowService)
    {
        $this->cashflowService = $cashflowService;
    }

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

                // Handle both old and new column names
                $totalReceived = 0;
                $outstanding = 0;

                if ($settlement) {
                    $totalReceived = $settlement->total_received ?? 0;
                    $outstanding = $settlement->outstanding ?? 0;

                    // If outstanding is 0 or not calculated properly, recalculate
                    if ($outstanding == 0 && $totalReceived > 0) {
                        $outstanding = (float)$mutation->amount - (float)$totalReceived;
                    }
                }

                return [
                    'id' => $mutation->id,
                    'input_date' => $mutation->input_date,
                    'from_warehouse' => $mutation->fromWarehouse->name,
                    'output_cashier' => $mutation->outputCashier->name,
                    'from_treasury' => $mutation->from_treasury,
                    'amount' => (float)$mutation->amount,
                    'total_received' => (float)$totalReceived,
                    'outstanding' => (float)$outstanding,
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
                if (!isset($inputRequest['mutation_id']) || !isset($inputRequest['total_received'])) {
                    return response()->json(['error' => 'Invalid input format'], 400);
                }

                $mutationId = $inputRequest['mutation_id'];
                $mutation = TreasuryMutation::find($mutationId);

                if (!$mutation) {
                    return response()->json(['error' => 'Mutation not found for mutation_id ' . $mutationId], 404);
                }

                // Clean and convert total_received to proper numeric format
                $totalReceived = $inputRequest['total_received'];

                // Remove currency formatting if present (Rp, commas, etc.)
                if (is_string($totalReceived)) {
                    $totalReceived = preg_replace('/[^0-9.]/', '', $totalReceived);
                }

                $totalReceived = (float) $totalReceived;
                $mutationAmount = (float) $mutation->amount;

                // Calculate outstanding value
                $outstanding = $mutationAmount - $totalReceived;

                // Create a new Settlement record and add it to the $settlements array
                $settlements[] = [
                    'mutation_id' => $mutationId,
                    'cashier_id' => auth()->id(),
                    'total_received' => $totalReceived,
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

    public function actionStore(SettlementRequest $request)
    {

        DB::beginTransaction();

        try {
            // Find the mutation record with proper error handling
            $mutation = TreasuryMutation::find($request->mutation_id);

            if (!$mutation) {
                return redirect()->back()->withErrors(['error' => 'Treasury mutation not found.']);
            }

            // Validate that the mutation amount is available
            if (!$mutation->amount || !is_numeric($mutation->amount)) {
                return redirect()->back()->withErrors(['error' => 'Invalid mutation amount.']);
            }

            // Use the amount from request as the total received
            $totalReceived = (float) $request->amount;
            $mutationAmount = (float) $mutation->amount;
            $outstanding = $mutationAmount - $totalReceived;

            // Validate that total received doesn't exceed mutation amount
            if ($totalReceived > $mutationAmount) {
                return redirect()->back()->withErrors(['error' => 'Total received cannot exceed the mutation amount.']);
            }

            // Check if settlement already exists for this mutation
            $existingSettlement = Settlement::where('mutation_id', $request->mutation_id)->first();
            if ($existingSettlement) {
                return redirect()->back()->withErrors(['error' => 'Settlement already exists for this mutation.']);
            }

            $settlement = new Settlement();
            $settlement->mutation_id = $request->mutation_id;
            $settlement->cashier_id = $request->output_cashier;
            $settlement->total_received = $totalReceived;
            $settlement->outstanding = $outstanding;
            $settlement->to_treasury = $request->from_treasury;
            $settlement->save();

            // Use injected service with error handling
            if ($totalReceived > 0) {
                $this->cashflowService->handleSettlement(
                    warehouseId: (int) $request->from_warehouse,
                    description: $request->description ?? 'Settlement - ' . $mutation->from_treasury,
                    totalReceived: $totalReceived,
                    outputCashier: (int) $request->output_cashier
                );
            }

            DB::commit();

            return redirect()->route('settlement.index')->with('success', 'Settlement created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in actionStore method: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'An error occurred while processing the settlement. Please try again.']);
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

    public function serverSide(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $searchValue = $request->input('search.value');

        // Base query
        $query = TreasuryMutation::with(['fromWarehouse', 'toWarehouse', 'inputCashier', 'outputCashier'])
            ->orderBy('input_date', 'desc');

        // Apply search if provided
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('input_date', 'like', "%{$searchValue}%")
                    ->orWhere('from_treasury', 'like', "%{$searchValue}%")
                    ->orWhere('amount', 'like', "%{$searchValue}%")
                    ->orWhereHas('fromWarehouse', function ($warehouse) use ($searchValue) {
                        $warehouse->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('outputCashier', function ($cashier) use ($searchValue) {
                        $cashier->where('name', 'like', "%{$searchValue}%");
                    });
            });
        }

        // Get total records
        $totalRecords = TreasuryMutation::count();
        $filteredRecords = $query->count();

        // Apply pagination
        $mutations = $query->skip($start)->take($length)->get();

        // Transform data
        $data = $mutations->map(function ($mutation) {
            $settlement = Settlement::where('mutation_id', $mutation->id)->first();

            // Handle both old and new column names
            $totalReceived = 0;
            $outstanding = 0;

            if ($settlement) {
                $totalReceived = $settlement->total_received ?? 0;
                $outstanding = $settlement->outstanding ?? 0;

                // If outstanding is 0 or not calculated properly, recalculate
                if ($outstanding == 0 && $totalReceived > 0) {
                    $outstanding = (float)$mutation->amount - (float)$totalReceived;
                }
            }

            return [
                'id' => $mutation->id,
                'input_date' => $mutation->input_date,
                'from_warehouse' => $mutation->fromWarehouse->name,
                'output_cashier' => $mutation->outputCashier->name,
                'from_treasury' => $mutation->from_treasury,
                'amount' => (float)$mutation->amount,
                'total_received' => (float)$totalReceived,
                'outstanding' => (float)$outstanding,
            ];
        });

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }
}
