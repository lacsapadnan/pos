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
            'group' => 'required',
            'name' => 'required',
            'unit_dus' => 'required|exists:units,id',
            'unit_pak' => 'required|exists:units,id',
            'unit_eceran' => 'required|exists:units,id',
            'barcode_dus' => 'nullable',
            'barcode_pak' => 'nullable',
            'barcode_eceran' => 'nullable',
            'dus_to_eceran' => 'required',
            'pak_to_eceran' => 'required',
            'price_dus' => 'required',
            'price_pak' => 'required',
            'price_eceran' => 'required',
            'sales_price' => 'required',
            'lastest_price_eceran' => 'nullable',
            'hadiah' => 'nullable',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'group.required' => 'Group harus diisi',
            'name.required' => 'Nama harus diisi',
            'unit_dus.required' => 'Unit dus harus diisi',
            'unit_pak.required' => 'Unit pak harus diisi',
            'unit_eceran.required' => 'Unit eceran harus diisi',
            'dus_to_eceran.required' => 'Dus to eceran harus diisi',
            'pak_to_eceran.required' => 'Pak to eceran harus diisi',
            'price_dus.required' => 'Harga dus harus diisi',
            'price_pak.required' => 'Harga pak harus diisi',
            'price_eceran.required' => 'Harga eceran harus diisi',
            'sales_price.required' => 'Harga jual harus diisi',
            'lastest_price_eceran.required' => 'Harga terakhir eceran harus diisi',
        ];
    }
}
