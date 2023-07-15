<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KasRequest extends FormRequest
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
            'date' => 'required|date',
            'type' => 'required',
            'amount' => 'required',
            'description' => 'nullable',
            'kas_income_item_id' => 'nullable',
            'kas_expense_item_id' => 'nullable',
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
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Tanggal harus berupa tanggal',
            'type.required' => 'Tipe harus diisi',
            'amount.required' => 'Jumlah harus diisi',
        ];
    }
}
