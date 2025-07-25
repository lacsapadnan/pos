<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required',
            'unit_dus' => 'required|exists:units,id',
            'unit_pak' => 'required|exists:units,id',
            'unit_eceran' => 'required|exists:units,id',
            'barcode_dus' => 'nullable',
            'barcode_pak' => 'nullable',
            'barcode_eceran' => 'nullable',
            'dus_to_eceran' => 'required',
            'pak_to_eceran' => 'required',
            'hadiah' => 'nullable',
            'hadiah_out_of_town' => 'nullable',
            'promo' => 'nullable',
            'promo_out_of_town' => 'nullable',
            'capital_dus' => 'nullable|integer',
            'capital_pak' => 'nullable|integer',
            'capital_eceran' => 'nullable|integer',
            'price_sell_dus' => 'nullable|integer',
            'price_sell_pak' => 'nullable|integer',
            'price_sell_eceran' => 'nullable|integer',
            'price_sell_dus_out_of_town' => 'nullable|integer',
            'price_sell_pak_out_of_town' => 'nullable|integer',
            'price_sell_eceran_out_of_town' => 'nullable|integer',
            'isShow' => 'nullable|boolean',
            'lastest_price_eceran_out_of_town' => 'nullable|integer',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'unit_dus.required' => 'Unit dus harus diisi',
            'unit_pak.required' => 'Unit pak harus diisi',
            'unit_eceran.required' => 'Unit eceran harus diisi',
            'dus_to_eceran.required' => 'Dus to eceran harus diisi',
            'pak_to_eceran.required' => 'Pak to eceran harus diisi',
        ];
    }
}
