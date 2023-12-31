<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TreasuryMutationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'from_warehouse' => 'required|exists:warehouses,id',
            'from_treasury' => 'nullable',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'input_date' => 'required|date',
            'output_cashier' => 'nullable',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'from_warehouse.required' => 'Cabang asal harus diisi',
            'from_warehouse.exists' => 'Cabang asal tidak ditemukan',
            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'description.string' => 'Deskripsi harus berupa teks',
            'input_date.required' => 'Tanggal harus diisi',
            'input_date.date' => 'Tanggal harus berupa tanggal',
        ];
    }
}
