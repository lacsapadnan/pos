<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:warehouses,name,' . $this->id],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */

    public function messages(): array
    {
        return [
            'name.required' => 'Nama gudang harus diisi.',
            'name.string' => 'Nama gudang harus berupa string.',
            'name.max' => 'Nama gudang maksimal 255 karakter.',
            'name.unique' => 'Nama gudang sudah terdaftar.',
            'address.required' => 'Alamat gudang harus diisi.',
            'address.string' => 'Alamat gudang harus berupa string.',
            'address.max' => 'Alamat gudang maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon gudang harus diisi.',
            'phone.string' => 'Nomor telepon gudang harus berupa string.',
            'phone.max' => 'Nomor telepon gudang maksimal 20 karakter.',
        ];
    }
}
