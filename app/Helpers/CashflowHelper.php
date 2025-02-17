<?php

use App\Models\Cashflow;
use Illuminate\Support\Facades\Auth;

if (!function_exists('create_cashflow')) {
    function create_cashflow(
        int $warehouse_id,
        string $description,
        float $paymentIn = 0,
        float $paymentOut = 0,
        string $paymentMethod = 'transfer',
        string $for = 'Bayar piutang'
    ) {
        return Cashflow::create([
            'warehouse_id'   => $warehouse_id,
            'user_id'        => Auth::id(),
            'for'            => $for,
            'description'    => $description,
            'in'             => $paymentIn,
            'out'            => $paymentOut,
            'payment_method' => in_array($paymentMethod, ['transfer', 'cash']) ? $paymentMethod : 'cash',
        ]);
    }
}
