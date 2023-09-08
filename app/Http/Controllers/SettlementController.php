<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Models\TreasuryMutation;
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
        return view('pages.settlement.index');
    }

    public function data()
    {
        $settlements = Settlement::with('mutation', 'cashier')->get();
        return response()->json($settlements);
    }

    public function combinedData()
    {
        // Query mutations with necessary relationships
        $mutations = TreasuryMutation::with(['fromWarehouse', 'toWarehouse', 'inputCashier', 'outputCashier'])
            ->orderBy('input_date', 'desc')
            ->get();
        // Query settlements with related data
        $settlements = Settlement::all();

        // Combine mutations with settlements using mutation_id as the key
        $combinedData = [];

        foreach ($mutations as $mutation) {
            $mutationId = $mutation->id;

            // Find the corresponding settlement, if it exists
            $settlement = $settlements->firstWhere('mutation_id', $mutationId);

            // If no settlement is found, default to 0 for total_received and outstanding
            $totalReceived = $settlement ? $settlement->total_recieved : 0;
            $outstanding = $settlement ? $settlement->outstanding : 0;

            // Build the combined data
            $combinedData[] = [
                'id' => $mutation->id,
                'input_date' => $mutation->input_date,
                'from_warehouse' => $mutation->fromWarehouse->name,
                'to_warehouse' => $mutation->toWarehouse->name,
                'input_cashier' => $mutation->inputCashier->name,
                'output_cashier' => $mutation->outputCashier->name,
                'amount' => $mutation->amount,
                'description' => $mutation->description,
                'from_treasury' => $mutation->from_treasury,
                'to_treasury' => $mutation->to_treasury,
                'total_received' => $totalReceived,
                'outstanding' => $outstanding,
            ];
        }

        return response()->json($combinedData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.settlement.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            DB::beginTransaction();
            Settlement::insert($settlements);
            DB::commit();

            return response()->json(['message' => 'Settlements created successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in store method: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the request'], 500);
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

        return redirect()->back()->with('success', 'Settlement deleted successfully');
    }
}
