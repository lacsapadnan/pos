<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashAdvanceController extends Controller
{
    public function getAvailableDeductions(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $deductions = [];

        // Get all approved direct cash advances for this employee that haven't been fully paid
        $directAdvances = CashAdvance::where('employee_id', $request->employee_id)
            ->where('status', 'approved')
            ->where('type', 'direct')
            ->whereRaw('amount > paid_amount')
            ->get();

        foreach ($directAdvances as $cashAdvance) {
            $remainingAmount = $cashAdvance->amount - $cashAdvance->paid_amount;
            if ($remainingAmount > 0) {
                $deductions[] = [
                    'id' => $cashAdvance->id,
                    'type' => 'direct',
                    'amount' => $remainingAmount,
                    'advance_date' => $cashAdvance->advance_date->format('Y-m-d'),
                    'selected' => false // Changed from true to false - unchecked by default
                ];
            }
        }

        // Get all pending installment payments
        $installmentPayments = CashAdvancePayment::whereHas('cashAdvance', function ($query) use ($request) {
            $query->where('employee_id', $request->employee_id)
                ->where('status', 'approved')
                ->where('type', 'installment');
        })
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        foreach ($installmentPayments as $payment) {
            $deductions[] = [
                'id' => $payment->id,
                'type' => 'installment',
                'amount' => $payment->amount,
                'due_date' => $payment->due_date->format('Y-m-d'),
                'installment_number' => $payment->installment_number,
                'selected' => false, // Changed from true to false - unchecked by default
                'is_overdue' => $payment->due_date < Carbon::today()
            ];
        }

        return response()->json([
            'data' => $deductions
        ]);
    }
}
