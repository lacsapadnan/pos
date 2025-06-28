<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashAdvanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('simpan kasbon');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'amount' => 'required|numeric|min:1|max:999999999.99',
            'advance_date' => 'required|date',
            'type' => 'required|in:direct,installment',
            'installment_count' => 'required_if:type,installment|nullable|integer|min:2|max:36',
            'description' => 'nullable|string|max:1000',
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
        ];
    }
}
