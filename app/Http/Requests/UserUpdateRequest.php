<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $userId = $this->route('user'); // Get the user ID from the route parameter

        return [
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
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
            'name.required' => 'Nama harus diisi!',
            'name.string' => 'Nama harus berupa string!',
            'name.max' => 'Nama maksimal 255 karakter!',
            'role.required' => 'Role harus diisi!',
            'role.string' => 'Role harus berupa string!',
            'role.max' => 'Role maksimal 255 karakter!',
            'email.required' => 'Email harus diisi!',
            'email.string' => 'Email harus berupa string!',
            'email.email' => 'Email harus berupa email!',
            'email.max' => 'Email maksimal 255 karakter!',
            'email.unique' => 'Email sudah terdaftar!',
            'warehouse_id.exists' => 'Cabang tidak ditemukan!',
            'permissions.array' => 'Permissions harus berupa array!',
            'permissions.*.exists' => 'Permission tidak ditemukan!',
        ];
    }
}
