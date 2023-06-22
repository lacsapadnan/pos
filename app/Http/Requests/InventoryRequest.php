<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk harus diisi',
            'product_id.exists' => 'Produk tidak ditemukan',
            'warehouse_id.required' => 'Cabang harus diisi',
            'warehouse_id.exists' => 'Cabang tidak ditemukan',
            'quantity.required' => 'Jumlah harus diisi',
            'quantity.numeric' => 'Jumlah harus berupa angka',
            'quantity.min' => 'Jumlah minimal 1',
        ];
    }
}
