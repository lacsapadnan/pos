<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettlementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'mutation_id' => 'required|integer|exists:treasury_mutations,id',
            'amount' => 'required|numeric|min:0',
            'output_cashier' => 'required|integer|exists:users,id',
            'from_warehouse' => 'required|integer|exists:warehouses,id',
            'from_treasury' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'mutation_id.required' => 'Mutation ID harus diisi',
            'mutation_id.integer' => 'Mutation ID harus berupa angka',
            'mutation_id.exists' => 'Mutation tidak ditemukan',
            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah tidak boleh kurang dari 0',
            'output_cashier.required' => 'Kasir harus dipilih',
            'output_cashier.integer' => 'Kasir harus berupa angka',
            'output_cashier.exists' => 'Kasir tidak ditemukan',
            'from_warehouse.required' => 'Cabang harus dipilih',
            'from_warehouse.integer' => 'Cabang harus berupa angka',
            'from_warehouse.exists' => 'Cabang tidak ditemukan',
            'from_treasury.required' => 'Treasury tujuan harus diisi',
            'from_treasury.string' => 'Treasury tujuan harus berupa teks',
            'from_treasury.max' => 'Treasury tujuan maksimal 255 karakter',
            'description.string' => 'Deskripsi harus berupa teks',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
        ];
    }
}
